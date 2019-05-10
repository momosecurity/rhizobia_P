<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule;
/**
 * A "safe" script module. No inline JS is allowed, and pointed to JS
 * files must match whitelist.
 */

use Security\HTMLPurifier\HTMLPurifier\AttrDef\HTMLPurifier_AttrDef_Enum;
use Security\HTMLPurifier\HTMLPurifier\AttrTransform\HTMLPurifier_AttrTransform_ScriptRequired;
use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_HTMLModule;

class HTMLPurifier_HTMLModule_SafeScripting extends HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'SafeScripting';

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        // These definitions are not intrinsically safe: the attribute transforms
        // are a vital part of ensuring safety.

        $allowed = $config->get('HTML.SafeScripting');
        $script = $this->addElement(
            'script',
            'Inline',
            'Empty',
            null,
            array(
                // While technically not required by the spec, we're forcing
                // it to this value.
                'type' => 'Enum#text/javascript',
                'src*' => new HTMLPurifier_AttrDef_Enum(array_keys($allowed))
            )
        );
        $script->attr_transform_pre[] =
        $script->attr_transform_post[] = new HTMLPurifier_AttrTransform_ScriptRequired();
    }
}


