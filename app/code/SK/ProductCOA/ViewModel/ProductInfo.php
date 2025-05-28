<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace SK\ProductCOA\ViewModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Helper\Image;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * ViewModel for Getting Product Info
 */
class ProductInfo implements ArgumentInterface
{

    private const MODULE_ENABLED = "catalog/productcoa/enable";

    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected ScopeConfigInterface $scopeConfig,
        protected UrlInterface $urlBuilder,
        protected ProductRepositoryInterface $productRepository,
        protected RequestInterface $request,
        protected Image $imageHelper,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
    ) {
    }

    /**
     * Check if module is enabled
     *
     * @return string|null
     */
    public function isEnabled(): string|null
    {
        
        return $this->scopeConfig->getValue(self::MODULE_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getProductAuthenticationPhotoUrl($orderItemId): string
    {
        return $this->urlBuilder->getUrl("productcoa/customer/authphoto/", ['id'=>(int)$orderItemId]);
    }

    public function getProductData(): \Magento\Sales\Api\Data\OrderItemInterface
    {
        //$productId = (int) $this->request->getParam('id');
        $data = $this->getOrderItemInfo();
        return $data['itemData'];
        //return $this->productRepository->getById((int)$data['product_id']);
    }

    public function getProductImage(ProductInterface $product): string
    {
        return $this->imageHelper->init($product, 'product_page_image_medium')->getUrl();
    }

    public function getOrderItemInfo()//: array
    {
        $itemId = (int) $this->request->getParam('id');
       

        $itemData = $this->orderItemRepository->get($itemId);
        //$orderItems = $this->orderItemRepository->getList($searchCriteria);

        // foreach ($orderItems as $childItem) {
        //     echo 'adasd'. $childItem->getItemId(); // or any other order item data
             $orderData = $this->orderRepository->get((int)$itemData->getOrderId());
        
        return [
                'customer_id' => $orderData->getCustomerId(), 
                'product_id' => $itemData->getProductId(),
                'itemData' => $itemData
            ];
        
    }

    public function getOrderItemInformation($parentItemId = null):array 
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('parent_item_id', $parentItemId)
            ->create();
        $orderItems = $this->orderItemRepository->getList($searchCriteria);
        
        $data = [];
        foreach ($orderItems as $childItem) {
            if($childItem->getAuthenticationPhoto()) {
                $data['item_id'] = $childItem->getId();
                $data['has_auth_photo'] = $childItem->getAuthenticationPhoto()?true:false;
                return $data;
            }    
        }

        return $data;
    }
}
