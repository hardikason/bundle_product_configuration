<?php

namespace SK\ConvertToOrder\Block;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class AdminAjaxUrl extends Template
{
    /**
     * Get the secure admin AJAX URL with a valid secret key
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('configure_bundle/ajax/bundleOptions', ['_current' => true, '_secure' => true]);
    }
}
