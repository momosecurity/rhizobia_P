<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule;
/**
 * Module adds the target-based noreferrer attribute transformation to a tags.  It
 * is enabled by HTML.TargetNoreferrer
 */

use Security\HTMLPurifier\HTMLPurifier\AttrTransform\HTMLPurifier_AttrTransform_TargetNoreferrer;
use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_HTMLModule;

class HTMLPurifier_HTMLModule_TargetNoreferrer extends HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'TargetNoreferrer';

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $a = $this->addBlankElement('a');
        $a->attr_transform_post[] = new HTMLPurifier_AttrTransform_TargetNoreferrer();
    }
}
