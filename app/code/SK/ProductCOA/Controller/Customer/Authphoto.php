<?php
namespace SK\ProductCOA\Controller\Customer;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Controller\ResultFactory;
use SK\ProductCOA\ViewModel\ProductInfo;

class Authphoto implements HttpGetActionInterface
{
    /**
     * Constructor function
     *
     * @param CustomerSession $customerSession
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        protected CustomerSession $customerSession,
        protected ResultFactory $resultFactory,
        protected RequestInterface $request,
        protected ProductInfo $productInfo,
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
        if (!$this->customerSession->isLoggedIn()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        $customer = $this->customerSession->getCustomer();

        $data = $this->productInfo->getOrderItemInfo();
        if($data['customer_id'] != $customer->getId()) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Product Details'));
        
        return $resultPage;
    }
}
