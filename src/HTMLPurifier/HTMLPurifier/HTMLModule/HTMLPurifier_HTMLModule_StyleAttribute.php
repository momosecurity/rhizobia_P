<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule;
/**
 * XHTML 1.1 Edit Module, defines editing-related elements. Text Extension
 * Module.
 */

use Security\HTMLPurifier\HTMLPurifier\AttrDef\HTMLPurifier_AttrDef_CSS;
use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_HTMLModule;

class HTMLPurifier_HTMLModule_StyleAttribute extends HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'StyleAttribute';

    /**
     * @type array
     */
    public $attr_collections = array(
        // The inclusion routine differs from the Abstract Modules but
        // is in line with the DTD and XML Schemas.
        'Style' => array('style' => false), // see constructor
        'Core' => array(0 => array('Style'))
    );

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $this->attr_collections['Style']['style'] = new HTMLPurifier_AttrDef_CSS();
    }
}


