<?php 

declare(strict_types=1);

namespace SK\ConvertToOrder\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Type;

class BundleProducts extends AbstractSource
{
    protected $productCollectionFactory;

    public function __construct(CollectionFactory $productCollectionFactory)
    {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Retrieve all grouped product options
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $collection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('name')
                ->addAttributeToFilter('type_id', Type::TYPE_BUNDLE); 

            foreach ($collection as $product) {
                $this->_options[] = [
                    'label' => $product->getName(),
                    'value' => $product->getSku(),
                ];
            }
        }
        return $this->_options;
    }
}
