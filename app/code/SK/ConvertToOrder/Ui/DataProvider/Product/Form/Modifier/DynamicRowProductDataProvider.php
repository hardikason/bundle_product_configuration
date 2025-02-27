<?php

namespace SK\ConvertToOrder\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

class DynamicRowProductDataProvider extends ProductDataProvider
{
    /**
     * Constructor Initialize
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param PoolInterface $pool
     * @param LocatorInterface $locator
     * @param RequestInterface $request
     * @param JsonHelper $jsonHelper
     * @param array $data
     * @param array $meta
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        protected CollectionFactory $collectionFactory,
        protected PoolInterface $pool,
        protected LocatorInterface $locator,
        protected RequestInterface $request,
        protected JsonHelper $jsonHelper,
        array $data = [],
        array $meta = [],
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $collectionFactory, $pool, $meta, $data);
    }

    /**
     * Get Product Data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();
        $product = $this->locator->getProduct();
        $productId = $product->getId();

        if ($productId) {
            // Fetch saved custom field data from the product
            $heatsinkConditionData = $product->getCustomAttribute('heatsink_condition');
            
            if ($heatsinkConditionData) {
                
                $decodedData = $this->jsonHelper->jsonDecode($heatsinkConditionData->getValue());
                $data[$productId]['product']['heatsink_condition'] = $decodedData ?? [];
            }

            $compatible_with = $product->getCustomAttribute('compatible_with');
            
            if ($compatible_with) {
                
                $decodedData = $this->jsonHelper->jsonDecode($compatible_with->getValue());
                $data[$productId]['product']['compatible_with'] = $decodedData ?? [];
            }
        }
        
        return $data;
    }
}
