<?php

namespace SK\ConvertToOrder\Observer;

use SK\ConvertToOrder\Ui\DataProvider\Product\Form\Modifier\DynamicRowAttribute;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;


class SaveDynamicRowValues implements ObserverInterface
{
    /**
     * Dependency Initilization
     *
     * @param RequestInterface $request
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        protected RequestInterface $request,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
    ) {
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return this
     */
    public function execute(Observer $observer)
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getDataObject();
        $wholeRequest = $this->request->getPost();
        $post = $wholeRequest['product'];

        if (empty($post)) {
            $post = !empty($wholeRequest['variables']['product']) ? $wholeRequest['variables']['product'] : [];
        }
        $compatible_with = isset(
            $post['compatible_with']
        ) ? $post['compatible_with'] : '';

        
        if (is_array($compatible_with)) {
            $product->setCompatibleWith($this->serializer->serialize($compatible_with));
        }
        
        $heatsink_condition = isset(
            $post['heatsink_condition']
        ) ? $post['heatsink_condition'] : '';

        if (is_array($heatsink_condition)) {
            $product->setHeatsinkCondition($this->serializer->serialize($heatsink_condition));
        }
    }
}