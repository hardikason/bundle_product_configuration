<?php

namespace SK\ProductCOA\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ProductSaveBefore implements ObserverInterface
{

    /**
     * Authentication photo upload directory config
     */
    private const UPLOAD_DIR = 'catalog/productcoa/auth_images_dir';
    
    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
    ) {
    }

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

            if (is_array($authenticationPhoto)) {
                $product->setData(
                    'authentication_photo',
                    $this->getAuthDirName(). '/' .$authenticationPhoto[0]['name']
                );
            }
        } catch (\Exception $e) {
            $observer->getEvent()->addError($e);
        }
    }

    /**
     * Get authentication upload photo directory
     *
     * @return string|null
     */
    public function getAuthDirName(): string|null
    {
        return $this->scopeConfig->getValue(self::UPLOAD_DIR, ScopeInterface::SCOPE_STORE);
    }
}
