<?php
namespace SK\ConvertToOrder\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class BundleOptions extends Action
{
    /**
     * Constructor Initialize
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProductRepository $productRepository
     */
    public function __construct(
        protected Context $context,
        protected JsonFactory $resultJsonFactory,
        protected ProductRepository $productRepository
    ) {
        parent::__construct($context);
    }

    /**
     * Check if controller access allowed
     *
     * @return void
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::products');
    }

    /**
     * Get Bundle Options List
     *
     * @return string
     */
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

            $options[] = ['value' => '', 'label' => '-- Select Option --'];
            foreach ($product->getExtensionAttributes()->getBundleProductOptions() as $option) {
                $options[] = ['value' => $option->getOptionId(), 'label' => $option->getTitle()];
            }

            return $resultJson->setData(['success' => true, 'options' => $options]);

        } catch (NoSuchEntityException $e) {
            return $resultJson->setData(['error' => true, 'message' => 'Product not found']);
        }
    }
}
