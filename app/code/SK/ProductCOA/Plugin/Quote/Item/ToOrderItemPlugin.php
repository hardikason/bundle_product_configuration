<?php
namespace SK\ProductCOA\Plugin\Quote\Item;

use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Catalog\Api\ProductRepositoryInterface;

class ToOrderItemPlugin
{
    /**
     * Constructor function
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Transfer authentication_photo from quote item to order item
     *
     * @param ToOrderItem $subject
     * @param OrderItem $result
     * @param AbstractItem $item
     * @param array $additional
     * @return OrderItem
     */
    public function afterConvert(
        ToOrderItem $subject,
        OrderItem $result,
        AbstractItem $item,
        array $additional = []
    ): OrderItem {
        $product = $this->productRepository->getById($item->getProductId());
        $authPhoto = $product->getData('authentication_photo');

        if ($authPhoto) {
            $result->setData('authentication_photo', $authPhoto);
        }

        return $result;
    }
}
