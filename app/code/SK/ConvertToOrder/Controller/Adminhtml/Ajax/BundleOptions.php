<?php
namespace SK\ConvertToOrder\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class BundleOptions extends Action
{
    protected $resultJsonFactory;
    protected $productRepository;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductRepository $productRepository
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productRepository = $productRepository;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }

    public function execute()
    {
        
        $resultJson = $this->resultJsonFactory->create();
        $sku = $this->getRequest()->getParam('sku');

        if (!$sku) {
            return $resultJson->setData(['error' => true, 'message' => 'No SKU provided']);
        }

        try {
            $product = $this->productRepository->get($sku);

            if ($product->getTypeId() !== 'bundle') {
                return $resultJson->setData(['error' => true, 'message' => 'Not a bundle product']);
            }

            foreach ($product->getExtensionAttributes()->getBundleProductOptions() as $option) {
                $options[] = ['value' => $option->getOptionId(), 'label' => $option->getTitle()];
            }

            return $resultJson->setData(['success' => true, 'options' => $options]);

        } catch (NoSuchEntityException $e) {
            return $resultJson->setData(['error' => true, 'message' => 'Product not found']);
        }
    }
}
