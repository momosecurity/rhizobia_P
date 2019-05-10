<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule;
/**
 * XHTML 1.1 Target Module, defines target attribute in link elements.
 */

use Security\HTMLPurifier\HTMLPurifier\AttrDef\HTML\HTMLPurifier_AttrDef_HTML_FrameTarget;
use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_HTMLModule;

class HTMLPurifier_HTMLModule_Target extends HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'Target';

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $elements = array('a');
        foreach ($elements as $name) {
            $e = $this->addBlankElement($name);
            $e->attr = array(
                'target' => new HTMLPurifier_AttrDef_HTML_FrameTarget()
            );
        }
    }
}


