<?php

namespace Security\HTMLPurifier\HTMLPurifier\AttrDef\HTML;
/**
 * Special-case enum attribute definition that lazy loads allowed frame targets
 */

use Security\HTMLPurifier\HTMLPurifier\AttrDef\HTMLPurifier_AttrDef_Enum;

class HTMLPurifier_AttrDef_HTML_FrameTarget extends HTMLPurifier_AttrDef_Enum
{

    /**
     * @type array
     */
    public $valid_values = false; // uninitialized value

    /**
     * @type bool
     */
    protected $case_sensitive = false;

    public function __construct()
    {
    }

    /**
     * @param string $string
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        if ($this->valid_values === false) {
            $this->valid_values = $config->get('Attr.AllowedFrameTargets');
        }
        return parent::validate($string, $config, $context);
    }
}


