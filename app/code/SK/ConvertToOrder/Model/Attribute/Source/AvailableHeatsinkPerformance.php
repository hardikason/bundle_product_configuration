<?php

declare(strict_types=1);

namespace SK\ConvertToOrder\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class AvailableHeatsinkPerformance extends AbstractSource
{
    /**
     * Get All Heatsink Perfomace Option
     *
     * @return array
     */
    public function getAllOptions(): array
    {
        return [
            ['value' => '', 'label' => __(' -- Select --')],
            ['value' => 'regular', 'label' => __('Regular')],
            ['value' => 'medium', 'label' => __('Medium')],
            ['value' => 'high', 'label' => __('High')]
        ];
    }
}
