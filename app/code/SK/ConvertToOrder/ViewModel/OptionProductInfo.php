<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace SK\ConvertToOrder\ViewModel;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * ViewModel for Bundle Option Block
 */
class OptionProductInfo implements ArgumentInterface
{
    /**
     * module enable path
     * 
     */
    const MODULE_ENABLE = 'bundle_config/general/enable';

    /**
     * Constructor function
     *
     * @param SerializerInterface $serializer
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        protected SerializerInterface $serializer,
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(
                self::MODULE_ENABLE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
    }

    /**
     * Returns quantity validator.
     *
     * @return string
     */
    public function getProductInfo($_selection, $_option): string
    {
        
        $data['option_id'] = $_option->getData('option_id');
        $data['tdp'] = $_selection->getData('tdp');
        $data['heatsink_performance'] = $_selection->getData('heatsink_performance');

        return $this->serializer->serialize($data);
    }

    /**
     * Returns Unserialized Data.
     *
     * @return string
     */
    public function getUnserializedHeatsinkConditionData($data, $optionId): string
    {
        $conditionalOptions = [];
        
        if($data) :
            $data = $this->serializer->unserialize($data);
            foreach($data['dynamic_row'] as $rows):
                if($optionId && $optionId == $rows['cpu_option_id']):
                    $conditionalOptions = $rows;
                    break;
                endif;
            endforeach;
        endif;

        return !empty($conditionalOptions)? $this->serializer->serialize($conditionalOptions) : '';
    }
    
}
