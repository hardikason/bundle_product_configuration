<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace SK\ConvertToOrder\ViewModel;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for Bundle Option Block
 */
class OptionProductInfo implements ArgumentInterface
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
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
        $data = $this->serializer->unserialize($data);
        foreach($data['dynamic_row'] as $rows):
            if($optionId == $rows['cpu_option_id']):
                $conditionalOptions = $rows;
                break;
            endif;
        endforeach;

        return !empty($conditionalOptions)? $this->serializer->serialize($conditionalOptions) : '';
    }
    
}
