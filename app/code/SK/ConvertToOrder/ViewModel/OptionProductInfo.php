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
    public function getProductInfo($_selection): string
    {
        
        $validators['ttttt'] = $_selection->getData('tdp');

        return $this->serializer->serialize($validators);
    }
}
