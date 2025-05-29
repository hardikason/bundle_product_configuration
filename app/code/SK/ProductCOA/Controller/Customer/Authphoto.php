<?php
namespace SK\ProductCOA\Controller\Customer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;
use SK\ProductCOA\ViewModel\ProductInfo;
use Magento\Framework\Message\ManagerInterface;

class Authphoto implements HttpGetActionInterface
{
    /**
     * Constructor function
     *
     * @param CustomerSession $customerSession
     * @param ResultFactory $resultFactory
     * @param RequestInterface $request
     * @param ProductInfo $productInfo
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        protected CustomerSession $customerSession,
        protected ResultFactory $resultFactory,
        protected RequestInterface $request,
        protected ProductInfo $productInfo,
        protected ManagerInterface $messageManager
    ) {
    }

    /**
     * Order info page with product authenticated photo
     *
     * @return void
     */
    public function execute()
    {
        // Redirect to login if customer is not logged in
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        $customer = $this->customerSession->getCustomer();

        $order = $this->productInfo->getOrder();
        if (!$order) {
            $this->messageManager->addErrorMessage(__('Order does not exist.'));
            $resultRedirect->setPath('sales/order/history');
            return $resultRedirect;
        }
        if ($order->getCustomerId() != $customer->getId()) {
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Product Details'));
        
        return $resultPage;
    }
}
