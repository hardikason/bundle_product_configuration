<?php

declare(strict_types=1);

namespace SK\ConvertToOrder\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductRepository;

class BundleProducts extends AbstractSource
{
    protected $_bundleOptions = [];

    public function __construct(
        protected CollectionFactory $productCollectionFactory,
        protected ProductRepository $productRepository
    ) {
    }

    /**
     * Get all bundle products
     *
     * @return void
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $collection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('name')
                ->addAttributeToFilter('type_id', Type::TYPE_BUNDLE);
            $this->_options[] = ['value' => '', 'label' => '-- select bundle product --'];
            foreach ($collection as $product) {
                $this->_options[] = [
                    'label' => $product->getName(),
                    'value' => $product->getSku(),
                ];
            }
        }
        return $this->_options;
    }

    /**
     * get bundle options
     *
     * @param string $sku
     * @return void
     */
    public function getBundleOptions($sku)
    {
        try {
            $this->_bundleOptions = [];
            $product = $this->productRepository->get($sku);
            if ($product->getId()) {
                
                foreach ($product->getExtensionAttributes()->getBundleProductOptions() as $option) {
                    $this->_bundleOptions[] = ['value' => $option->getOptionId(), 'label' => $option->getTitle()];
                }
            }
            
            return $this->_bundleOptions;

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return [];
        }
    }
}
