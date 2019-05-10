<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule;
/**
 * Module adds the target-based noopener attribute transformation to a tags.  It
 * is enabled by HTML.TargetNoopener
 */

use Security\HTMLPurifier\HTMLPurifier\AttrTransform\HTMLPurifier_AttrTransform_TargetNoopener;
use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_HTMLModule;

class HTMLPurifier_HTMLModule_TargetNoopener extends HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'TargetNoopener';

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $a = $this->addBlankElement('a');
        $a->attr_transform_post[] = new HTMLPurifier_AttrTransform_TargetNoopener();
    }
}
