<?php 

declare(strict_types=1);

namespace SK\ConvertToOrder\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;

class BundleProductOptions extends AbstractSource
{
    protected $_bundleProductOptions = [];

    public function __construct(
        protected CollectionFactory $productCollectionFactory,
        protected ProductRepository $productRepository,
        protected RequestInterface $request)
    {
    }

    /**
     * get bundle options
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        try {
            // Get current product ID from request
            $productId = $this->request->getParam('id');

            $this->_bundleProductOptions = [];
            $product = $this->productRepository->getById($productId);
            // Check if it's a bundle product
            if ($product->getTypeId() !== 'bundle') {
                return [['value' => '', 'label' => __('Not a Bundle Product')]];
            }

            if($product->getId()) {
                foreach ($product->getExtensionAttributes()->getBundleProductOptions() as $option) {
                    $this->_bundleProductOptions[] = ['value' => $option->getOptionId(), 'label' => $option->getTitle()];
                }
            }
            
            return $this->_bundleProductOptions;

        } catch(\Magento\Framework\Exception\LocalizedException $e) {
            return [];
        } 

    }
}
