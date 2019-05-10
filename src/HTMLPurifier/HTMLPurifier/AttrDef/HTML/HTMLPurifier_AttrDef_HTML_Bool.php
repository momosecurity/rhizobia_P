<?php

namespace Security\HTMLPurifier\HTMLPurifier\AttrDef\HTML;
/**
 * Validates a boolean attribute
 */

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_AttrDef;

class HTMLPurifier_AttrDef_HTML_Bool extends HTMLPurifier_AttrDef
{

    /**
     * @type bool
     */
    public $minimized = true;
    /**
     * @type bool
     */
    protected $name;

    /**
     * @param bool $name
     */
    public function __construct($name = false)
    {
        $this->name = $name;
    }

    /**
     * @param string $string
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        return $this->name;
    }

    /**
     * @param string $string Name of attribute
     * @return HTMLPurifier_AttrDef_HTML_Bool
     */
    public function make($string)
    {
        return new HTMLPurifier_AttrDef_HTML_Bool($string);
    }
}


