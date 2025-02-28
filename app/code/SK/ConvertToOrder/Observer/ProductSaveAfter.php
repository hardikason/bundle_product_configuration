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
        protected SaveAction $saveAction
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

        // Get compatible products from request data
        $compatibleProducts = $simpleProduct->getCompatibleWith();

        if (!$simpleProduct->getId() || $simpleProduct->getTypeId() != 'simple') {
            return;
        }

        if (!empty($compatibleProducts)) {
            $compatibleProducts = $this->serializer->unserialize($compatibleProducts);
            
            echo '<pre>';print_r($compatibleProducts);
            
            if (isset($compatibleProducts['dynamic_row'])) {

                $newBundleData = [];

                foreach ($compatibleProducts['dynamic_row'] as $bundle) {
                    $bundleProductId = $bundle['bundle_product'];
                    $bundleOptionId = isset($bundle['bundle_option']) ? $bundle['bundle_option'] : '';

                    $linkData = [];
                    if(!$bundleOptionId){ 
                        echo 'option id not available at '. $bundle['record_id'];
                        $linkData = [
                            'product_id' => $simpleProduct->getId(), 
                            'sku' => $simpleProduct->getSku(), 
                            'selection_qty' => 1, 
                            'selection_can_change_qty' => 1, 
                            'delete' => ''
                        ];
                        $bundleOptionId = $this->createNewBundleOptionAndAssign($bundle, $simpleProductSku, $linkData);

                        $bundle['bundle_option'] = $bundleOptionId;
                        $bundle['new_bundle_option'] = '';
                    }
                    

                    $newBundleData[] = $bundle;

                    //$this->assignProductToBundle($bundleProductId, $simpleProductSku, $bundleOptionId);
                    
                }


                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
                $productModel = $objectManager->create(\Magento\Catalog\Model\Product::class)->load($simpleProduct->getId());
                
                $compatibleProducts['dynamic_row'] = $newBundleData;
                //print_r($compatibleProducts);die;
                $compatible_with = $this->serializer->serialize($compatibleProducts);

                //$simpleProduct->setCompatibleWith($compatible_with);

                $simpleProduct->setData('compatible_with', $compatible_with);

                $productModel->addAttributeUpdate('compatible_with', $compatible_with, $simpleProduct->getStoreId());
            }
        }
    }

    /**
     * Assign product to a bundle option
     *
     * @param int $bundleProductId
     * @param int $simpleProductSku
     * @param string $bundleOptionId
     */
    protected function assignProductToBundle($bundleProductId, $simpleProductSku, $bundleOptionId)
    {
        try {
            $bundleProduct = $this->productRepository->get($bundleProductId);

            if (!$bundleProduct->getId() || $bundleProduct->getTypeId() !== 'bundle') {
                return;
            }

            // Get current selections for the bundle options
            $existingSelections = $bundleProduct->getExtensionAttributes()->getBundleProductOptions() ?? [];

            // Create link data for the simple product
            $link = $this->linkInterfaceFactory->create();
            $link->setSku($simpleProductSku);
            $link->setQty(1);  // Set quantity
            $link->setPosition(1);  // Set position
            $link->setIsDefault(false);  // Default selection or not
            $link->setCanChangeQuantity(true);
            // Add child product to the bundle option
            $this->productLinkManagement->addChildByProductSku($bundleProductId, $bundleOptionId, $link);

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
           
            
            // Get current selections for the bundle options
            //$existingSelections = $bundleProduct->getExtensionAttributes()->getBundleProductOptions() ?? [];
            if($bundleOptionId) {
                echo '<br>bundleOptionId '. $bundleOptionId; 
                //$this->assignProductToBundle($bundleProductId, $simpleProductSku, $bundleOptionId);
                //return $this;
            }
            
            echo '<pre>DDDDDsXXXXXXXXXXXXXXXXXXXXXXXX';
            echo '<br>bundleOptionId '. $bundleOptionId;
            print_r($bundle);
            $newBundleOptionTitle = $bundle['new_bundle_option'];
            echo $newBundleOptionTitle;
            echo $simpleProductSku;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
            //die;
            // Create link data for the simple product
            //$linkData = $this->getLinkData($simpleProductSku);

            print_r($linkData);
            $links = [];
            $link = $this->linkInterfaceFactory->create();
            $link->setData($linkData);
            //$link->setSku($simpleProduct->getSku());
            $link->setQty(1);  // Set quantity
            $link->setPosition(1);  // Set position
            $link->setIsDefault(false);  // Default selection or not
            $link->setCanChangeQuantity(true);
            $links[] = $link;

            $optionRepository = $objectManager->create(\Magento\Bundle\Model\Option\SaveAction::class);

            $bundleOption = $this->optionInterfaceFactory->create();

            $bundleOption->setTitle($newBundleOptionTitle);
            $bundleOption->setType('select');
            $bundleOption->setRequired(true);
            $bundleOption->setPosition(1);
            $bundleOption->setParentId($bundleProductId);
            $bundleOption->setStoreId($bundleProduct->getStoreId());
            $bundleOption->setProductLinks($links);

            print_r( $bundleOption);
            $savedBundleOption = $optionRepository->save($bundleProduct, $bundleOption);

            //print_r($savedBundleOption);
            //$this->productLinkManagement->addChildByProductSku($bundleProductId, $bundleOptionId, $link);
            
            return $savedBundleOption->getId();
            //$option = $this->optionInterfaceFactory->create();

            
            //$optionFactory = $objectManager->create(\Magento\Bundle\Model\OptionFactory::class);
            
            //$option->setOptionId(null);
            //$bundleOption->setProductLinks($links);

            //$bundleOption->save();

            
            //die;
            echo $bundleOptionId = $bundleOption->getId();

            // 🔹 Step 4: Assign Option to Bundle Product
            $resourceModel = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);
            $connection = $resourceModel->getConnection();
            $tableName = $resourceModel->getTableName('catalog_product_bundle_option');
            $connection->update(
                $tableName,
                ['parent_id' => $bundleProduct->getId()], // Assign option to bundle product
                ['option_id = ?' => $bundleOptionId]
            );

            
            die;

            $selectionFactory = $objectManager->create(\Magento\Bundle\Model\SelectionFactory::class);

            // 🔹 Step 3: Assign Simple Products to the Bundle Option
            $simpleProductSkus = [$linkData['sku']]; // Replace with valid SKUs
            $selectionData = [];

            foreach ($simpleProductSkus as $sku) {
                $simpleProduct = $productRepository->get($sku);
                $selection = $selectionFactory->create();
                $selection->setOptionId($optionId);
                $selection->setProductId($simpleProduct->getId());
                $selection->setSelectionPriceValue(0); // Keep 0 for dynamic pricing
                $selection->setSelectionPriceType(0);
                $selection->setIsDefault(0);
                $selection->setSelectionQty(1);
                $selection->setSelectionCanChangeQty(1);
                $selection->save();
                $selectionData[] = $selection->getId();
                echo "Added Simple Product: {$sku} (ID: {$simpleProduct->getId()}) to Bundle Option\n";
            }

            // 🔹 Step 4: Assign Option to Bundle Product
            
            $connection = $resourceModel->getConnection();
            $tableName = $resourceModel->getTableName('catalog_product_bundle_option');
            $connection->update(
                $tableName,
                ['parent_id' => $bundleProduct->getId()], // Assign option to bundle product
                ['option_id = ?' => $optionId]
            );
            

            
            //echo $simpleProductSku.'---';
            $simpleProduct = $this->productRepository->get($linkData['sku']);
            

            
            //$option->setProductLinks($links);
            //$options[] = $option;

            //print_r($options);die;

           
            // Add child product to the bundle option
            //$this->productLinkManagement->addChildByProductSku($bundleProductId, $bundleOptionId, $link);

            // $extension = $bundleProduct->getExtensionAttributes();
            // $extension->setBundleProductOptions($options);
            // $bundleProduct->setExtensionAttributes($extension);
            // $bundleProduct->save();
            //  $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
            
            // $this->productLinkManagement->addChild($bundleProduct, $optionId, $link);

            // $resourceModel = $objectManager->get(\Magento\Framework\App\ResourceConnection::class);

            
            
            die;
            return "Product successfully added!";
           
        } catch (LocalizedException $e) {
            echo $e->getMessage();die;
            $this->logger->debug($e->getMessage());
        }
    }

    public function getLinkData($simpleProductSku) {
        $simpleProduct = $this->productRepository->get($simpleProductSku);
        $linkData = [
            'product_id' => $simpleProduct->getId(), 
            'sku' => $simpleProduct->getSku(), 
            'selection_qty' => 1, 
            'selection_can_change_qty' => 1, 
            'delete' => ''
        ];

        return $linkData;
    }
}
