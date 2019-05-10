<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule;
/**
 * XHTML 1.1 Edit Module, defines editing-related elements. Text Extension
 * Module.
 */

use Security\HTMLPurifier\HTMLPurifier\ChildDef\HTMLPurifier_ChildDef_Chameleon;
use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_HTMLModule;

class HTMLPurifier_HTMLModule_Edit extends HTMLPurifier_HTMLModule
{

    /**
     * @type string
     */
    public $name = 'Edit';
    /**
     * @type bool
     */
    public $defines_child_def = true;

    // HTML 4.01 specifies that ins/del must not contain block
    // elements when used in an inline context, chameleon is
    // a complicated workaround to acheive this effect

    // Inline context ! Block context (exclamation mark is
    // separator, see getChildDef for parsing)

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $contents = 'Chameleon: #PCDATA | Inline ! #PCDATA | Flow';
        $attr = array(
            'cite' => 'URI',
            // 'datetime' => 'Datetime', // not implemented
        );
        $this->addElement('del', 'Inline', $contents, 'Common', $attr);
        $this->addElement('ins', 'Inline', $contents, 'Common', $attr);
    }

    /**
     * @param HTMLPurifier_ElementDef $def
     * @return HTMLPurifier_ChildDef_Chameleon
     */
    public function getChildDef($def)
    {
        if ($def->content_model_type != 'chameleon') {
            return false;
        }
        $value = explode('!', $def->content_model);
        return new HTMLPurifier_ChildDef_Chameleon($value[0], $value[1]);
    }
}


