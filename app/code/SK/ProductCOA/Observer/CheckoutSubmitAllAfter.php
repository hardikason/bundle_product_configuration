<?php
namespace SK\ProductCOA\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Api\ProductRepositoryInterface;

class CheckoutSubmitAllAfter implements ObserverInterface
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {
    }

    public function execute(Observer $observer)
    {
        try{
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
         file_put_contents(BP . '/var/log/event.log', "Order ID: " . $order->getId() . "\n", FILE_APPEND);
       // echo 'ssdfsdf';die;
               

        foreach ($order->getAllItems() as $orderItem) {
            $quoteItem = $quote->getItemById($orderItem->getQuoteItemId());
             file_put_contents(BP . '/var/log/event.log', "OrderItem ID: " . $orderItem->getId() . "\n", FILE_APPEND);
            if ($quoteItem && $quoteItem->getProduct()) {
                $productId = $orderItem->getProductId();

                $product = $this->productRepository->getById($productId);
                $authPhoto = $quoteItem->getData('authentication_photo');
                file_put_contents(BP . '/var/log/event.log', "Product ID: " . $productId . "\n", FILE_APPEND);
                file_put_contents(BP . '/var/log/event.log', "Authentication Photo: " . $authPhoto . "\n", FILE_APPEND);
                
                // Optional: if it's a file path, prepend base media URL later
               // $orderItem->setData('authentication_photo', 'sdfsfsd');
            }
        }
        }catch(\Exception $e){

            //echo ''. $e->getMessage();die;
        }
    }

}
