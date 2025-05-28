<?php

namespace SK\ProductCOA\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class ProductSaveBefore implements ObserverInterface
{
    

    /**
     * Execute observer on product save
     *
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        try {
            

            $product = $observer->getEvent()->getProduct();
            $authenticationPhoto = $product->getData('authentication_photo');
           // print_r($authenticationPhoto);die;
            if(is_array($authenticationPhoto)) {
                $product->setData('authentication_photo', $authenticationPhoto[0]['name']);
            }
        }
        catch (\Exception $e) {
            $observer->getEvent()->addError($e);
        }
        
    }
}
