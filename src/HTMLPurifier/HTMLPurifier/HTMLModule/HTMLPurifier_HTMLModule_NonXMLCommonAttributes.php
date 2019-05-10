<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule;

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_HTMLModule;

class HTMLPurifier_HTMLModule_NonXMLCommonAttributes extends HTMLPurifier_HTMLModule
{
    /**
     * @type string
     */
    public $name = 'NonXMLCommonAttributes';

    /**
     * @type array
     */
    public $attr_collections = array(
        'Lang' => array(
            'lang' => 'LanguageCode',
        )
    );
}


