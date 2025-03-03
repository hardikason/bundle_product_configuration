<?php

declare(strict_types=1);

namespace SK\ConvertToOrder\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class ProductTypes extends AbstractSource
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
            ['value' => 'cpu', 'label' => __('CPU')],
            ['value' => 'chasis', 'label' => __('CHASIS')],
            ['value' => 'heatsink', 'label' => __('heatsink')],
            ['value' => 'ZZZZZ', 'label' => __('ZZZZZ')]

            
        ];
    }
}
