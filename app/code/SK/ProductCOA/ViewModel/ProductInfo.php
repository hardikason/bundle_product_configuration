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
use Magento\Sales\Api\Data\OrderInterface;

/**
 * ViewModel for Getting Product Info
 */
class ProductInfo implements ArgumentInterface
{
    /**
     * module enable config
     *
     */
    private const MODULE_ENABLED = "catalog/productcoa/enable";

    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param RequestInterface $request
     * @param Image $imageHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
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

    /**
     * Get view authentication photo url function
     *
     * @param string $orderItemId
     * @return string
     */
    public function getViewProductAuthenticationPhotoUrl($orderItemId): string
    {
        return $this->urlBuilder->getUrl("productcoa/customer/authphoto/", ['id'=>(int)$orderItemId]);
    }

    /**
     * Get Product Image Url
     *
     * @param ProductInterface $product
     * @return string
     */
    public function getProductImage(ProductInterface $product): string
    {
        return $this->imageHelper->init($product, 'product_page_image_medium')->getUrl();
    }

    /**
     * Get Authentication Photo Url
     *
     * @param string $authPhotoPath
     * @return string
     */
    public function getAuthenticationPhotoUrl($authPhotoPath): string
    {
        return $this->urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).$authPhotoPath;
    }

    /**
     * Get Order info
     *
     * @return OrderInterface|null
     */
    public function getOrder(): OrderInterface|null
    {
        $orderId = (int) $this->request->getParam('id');

        try {
            return $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check order has authentication photo available
     *
     * @param object $order
     * @return boolean
     */
    public function hasAuthenticationPhoto($order):bool
    {
        $orderItems = $order->getAllItems();

        $hasAuthenticationPhoto = false;
        foreach ($orderItems as $item) {
            if ($item->getAuthenticationPhoto() !== null) {
                $hasAuthenticationPhoto = true;
            }
        }

        return $hasAuthenticationPhoto;
    }
}
