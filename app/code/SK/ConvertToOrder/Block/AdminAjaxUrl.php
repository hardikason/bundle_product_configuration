<?php

namespace SK\ConvertToOrder\Block;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class AdminAjaxUrl extends Template
{
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get the secure admin AJAX URL with a valid secret key
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('configure_bundle/ajax/bundleOptions', ['_current' => true, '_secure' => true]);
    }
}
