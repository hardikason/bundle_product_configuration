<?php

declare(strict_types=1);

namespace SK\ConvertToOrder\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class BundleCategories extends AbstractSource
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
            ['value' => 'category_1', 'label' => __('Category 1')],
            ['value' => 'category_2', 'label' => __('Category 2')],
            ['value' => 'category_3', 'label' => __('Category 3')]
        ];
    }
}
