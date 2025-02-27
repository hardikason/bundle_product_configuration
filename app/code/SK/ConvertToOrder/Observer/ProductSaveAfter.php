<?php

namespace SK\ConvertToOrder\Observer;

use SK\ConvertToOrder\Ui\DataProvider\Product\Form\Modifier\DynamicRowAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\LocalizedException;
use Magento\Bundle\Api\ProductLinkManagementAddChildrenInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;

class ProductSaveAfter implements ObserverInterface
{
    /**
     * Dependency Initilization
     *
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected ProductRepository $productRepository,
        protected ProductLinkManagementAddChildrenInterface $productLinkManagement,
        protected LinkInterfaceFactory $linkInterfaceFactory 
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
        $product = $observer->getEvent()->getProduct();
        $simpleProductSku = $product->getSku();

        // Get compatible products from request data
        $compatibleProducts = $product->getCompatibleWith();

        if (!empty($compatibleProducts)) {
            $compatibleProducts = $this->serializer->unserialize($compatibleProducts);
            
            foreach ($compatibleProducts['dynamic_row'] as $bundle) {
                $bundleProductId = $bundle['bundle_product'];
                $bundleOptionTitle = $bundle['bundle_option'];

                $this->assignProductToBundle($bundleProductId, $simpleProductSku, $bundleOptionTitle);
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
           
        }               
        catch(LocalizedException $e) {
            echo $e->getMessage();
        }
    }
}