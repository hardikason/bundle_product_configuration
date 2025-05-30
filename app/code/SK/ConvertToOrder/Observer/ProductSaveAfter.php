<?php

namespace SK\ConvertToOrder\Observer;

use SK\ConvertToOrder\Ui\DataProvider\Product\Form\Modifier\DynamicRowAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Bundle\Api\ProductLinkManagementAddChildrenInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Psr\Log\LoggerInterface;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Model\Option\SaveAction;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Type;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * Dependency Initilization
     *
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param ProductRepository $productRepository
     * @param ProductLinkManagementAddChildrenInterface $productLinkManagement
     * @param LinkInterfaceFactory $linkInterfaceFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected ProductRepository $productRepository,
        protected ProductLinkManagementAddChildrenInterface $productLinkManagement,
        protected LinkInterfaceFactory $linkInterfaceFactory,
        protected LoggerInterface $logger,
        protected OptionInterfaceFactory $optionInterfaceFactory,
        protected SaveAction $saveAction,
        protected ProductFactory $productFactory
    ) {
    }

    /**
     * Execute observer on product save
     *
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $simpleProduct = $observer->getEvent()->getProduct();
        $simpleProductSku = $simpleProduct->getSku();

        if ($simpleProduct->getTypeId() == Type::TYPE_BUNDLE) {
            print_r($simpleProduct->getData('bundle_removed_items'));
            die;
        }

        if ($simpleProduct->getTypeId() == Type::TYPE_SIMPLE) {
           // Get compatible products from request data
            $compatibleProducts = $simpleProduct->getCompatibleWith();
            if (!empty($compatibleProducts)) {
                $compatibleProducts = $this->serializer->unserialize($compatibleProducts);
                
                if (isset($compatibleProducts['dynamic_row'])) {

                    $newBundleData = []; $createNewOption = 0; $removeBundleRecordIds = [];

                    foreach ($compatibleProducts['dynamic_row'] as $bundle) {
                        //echo 'fff'.$bundle['delete'];die;

                        $bundleProductSku = $bundle['bundle_product'];
                        $bundleOptionId = isset($bundle['bundle_option']) ? $bundle['bundle_option'] : '';

                        if($bundle['delete'] == 1) {
                            echo 'delete';
                            $this->removeProductFromBundle($bundleProductSku, $bundleOptionId, $simpleProductSku);
                            $removeBundleRecordIds[] = $bundle['record_id'];
                            continue; 
                        }

                        echo 'after continue';
                        

                        $linkData = [];
                        if(!$bundleOptionId){ 
                            $linkData = [
                                'product_id' => $simpleProduct->getId(), 
                                'sku' => $simpleProduct->getSku(), 
                                'selection_qty' => 1, 
                                'selection_can_change_qty' => 1, 
                                'delete' => 0
                            ];
                            $bundleOptionId = $this->createNewBundleOptionAndAssign($bundle, $simpleProductSku, $linkData);

                            $bundle['bundle_option'] = $bundleOptionId;
                            $bundle['new_bundle_option'] = '';
                            $createNewOption++;
                        }else{
                            $this->assignProductToBundle($bundleProductSku, $simpleProductSku, $bundleOptionId);
                        }

                        $newBundleData[] = $bundle;
                    }

                    print_r($newBundleData);

                    print_r($removeBundleRecordIds);

                    //reset the compatible with if new options created in bundle and assigned prodcuts in it.
                    if($createNewOption > 0 || count($removeBundleRecordIds) > 0) {

                        $newBundleData = array_filter($newBundleData, function ($item) use ($removeBundleRecordIds) {
                            return !in_array($item['record_id'], $removeBundleRecordIds);
                        });
        
                        //print_r($newBundleData);die;

                        $productModel = $this->productFactory->create()->load($simpleProduct->getId());
                        $compatibleProducts['dynamic_row'] = $newBundleData;
                        $compatible_with = $this->serializer->serialize($compatibleProducts);
                        $simpleProduct->setData('compatible_with', $compatible_with);
                        $productModel->addAttributeUpdate('compatible_with', $compatible_with, $simpleProduct->getStoreId());
                    }
                    
                }
            }
        }
    }

    /**
     * Assign product to a bundle option
     *
     * @param int $bundleProductSku
     * @param int $simpleProductSku
     * @param string $bundleOptionId
     */
    protected function assignProductToBundle($bundleProductSku, $simpleProductSku, $bundleOptionId)
    {
        try {
            $bundleProduct = $this->productRepository->get($bundleProductSku);

            if (!$bundleProduct->getId() || $bundleProduct->getTypeId() !== 'bundle') {
                return;
            }

            // Get current selections for the bundle options
            //$existingSelections = $bundleProduct->getExtensionAttributes()->getBundleProductOptions() ?? [];

            // Create link data for the simple product
            $link = $this->linkInterfaceFactory->create();
            $link->setSku($simpleProductSku);
            $link->setQty(1);  // Set quantity
            $link->setPosition(1);  // Set position
            $link->setIsDefault(false);  // Default selection or not
            $link->setCanChangeQuantity(true);
            // Add child product to the bundle option
            $this->productLinkManagement->addChildByProductSku($bundleProductSku, $bundleOptionId, $link);

            return "Product successfully added!";
           
        } catch (LocalizedException $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    protected function createNewBundleOptionAndAssign($bundle, $simpleProductSku, $linkData)
    {
        try {
            $bundleProductId = $bundle['bundle_product'];
            $bundleOptionId = isset($bundle['bundle_option']) ? $bundle['bundle_option'] : '';
            $bundleProduct = $this->productRepository->get($bundleProductId);

            if (!$bundleProduct->getId() || $bundleProduct->getTypeId() != 'bundle') {
                return;
            }
           
            $newBundleOptionTitle = $bundle['new_bundle_option'];

            $bundleOption = $this->createNewBundleOption($bundleProduct, $simpleProductSku, $newBundleOptionTitle);
            
            $savedBundleOption = $this->saveAction->save($bundleProduct, $bundleOption);

            return $savedBundleOption->getId();
            
        } catch (LocalizedException $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    public function getLinkData($simpleProductSku) {

        $simpleProduct = $this->productRepository->get($simpleProductSku);

        $link = $this->linkInterfaceFactory->create();
        $link->setProductId($simpleProduct->getId());
        $link->setSku($simpleProduct->getSku());
        $link->setQty(1);  // Set quantity
        $link->setPosition(1);  // Set position
        $link->setIsDefault(true);  // Default selection or not
        $link->setCanChangeQuantity(true);

        return $link;
    }

    public function createNewBundleOption($bundleProduct, $simpleProductSku, $newBundleOptionTitle) {

        // Create link data for the simple product
        $links[] = $this->getLinkData($simpleProductSku);

        $bundleOption = $this->optionInterfaceFactory->create();

        $bundleOption->setTitle($newBundleOptionTitle);
        $bundleOption->setType('select');
        $bundleOption->setRequired(true);
        $bundleOption->setPosition(1);
        $bundleOption->setParentId($bundleProduct->getId());
        $bundleOption->setStoreId($bundleProduct->getStoreId());
        $bundleOption->setProductLinks($links);

        return $bundleOption;
    }

    public function removeProductFromBundle($bundleProductSku, $optionId, $simpleProductSku) {
        $this->productLinkManagement->removeChild($bundleProductSku, $optionId, $simpleProductSku);
    }
}
