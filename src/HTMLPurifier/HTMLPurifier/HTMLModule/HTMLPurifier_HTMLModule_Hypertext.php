<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule;
/**
 * XHTML 1.1 Hypertext Module, defines hypertext links. Core Module.
 */

use Security\HTMLPurifier\HTMLPurifier\AttrDef\HTML\HTMLPurifier_AttrDef_HTML_LinkTypes;
use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_HTMLModule;

class HTMLPurifier_HTMLModule_Hypertext extends HTMLPurifier_HTMLModule
{

    /**
     * @type string
     */
    public $name = 'Hypertext';

    /**
     * @param HTMLPurifier_Config $config
     */
    public function setup($config)
    {
        $a = $this->addElement(
            'a',
            'Inline',
            'Inline',
            'Common',
            array(
                // 'accesskey' => 'Character',
                // 'charset' => 'Charset',
                'href' => 'URI',
                // 'hreflang' => 'LanguageCode',
                'rel' => new HTMLPurifier_AttrDef_HTML_LinkTypes('rel'),
                'rev' => new HTMLPurifier_AttrDef_HTML_LinkTypes('rev'),
                // 'tabindex' => 'Number',
                // 'type' => 'ContentType',
            )
        );
        $a->formatting = true;
        $a->excludes = array('a' => true);
    }
}


