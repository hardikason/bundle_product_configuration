<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace SK\ConvertToOrder\ViewModel;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * ViewModel for Bundle Option Block
 */
class OptionProductInfo implements ArgumentInterface
{

    /**
     * Constructor function
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(
        protected SerializerInterface $serializer
    ) {
    }

    /**
     * Get product info
     *
     * @param array $_selection
     * @param array $_option
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
     * @param string $data
     * @param int $optionId
     * @return string
     */
    public function getHeatsinkConditionData($data, $optionId): string
    {
        $conditionalOptions = [];
        
        if ($data):
            $data = $this->serializer->unserialize($data);
            if (isset($data['dynamic_row'])) {
                foreach ($data['dynamic_row'] as $rows):
                    if ($optionId && $optionId == $rows['cpu_option_id']):
                        $conditionalOptions = $rows;
                        break;
                    endif;
                endforeach;
            }
            
        endif;

        return is_array($conditionalOptions)? $this->serializer->serialize($conditionalOptions) : '';
    }
}
