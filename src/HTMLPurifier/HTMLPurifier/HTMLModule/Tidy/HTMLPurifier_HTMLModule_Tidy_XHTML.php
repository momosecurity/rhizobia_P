<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule\Tidy;


use Security\HTMLPurifier\HTMLPurifier\AttrTransform\HTMLPurifier_AttrTransform_Lang;
use Security\HTMLPurifier\HTMLPurifier\HTMLModule\HTMLPurifier_HTMLModule_Tidy;

class HTMLPurifier_HTMLModule_Tidy_XHTML extends HTMLPurifier_HTMLModule_Tidy
{
    /**
     * @type string
     */
    public $name = 'Tidy_XHTML';

    /**
     * @type string
     */
    public $defaultLevel = 'medium';

    /**
     * @return array
     */
    public function makeFixes()
    {
        $r = array();
        $r['@lang'] = new HTMLPurifier_AttrTransform_Lang();
        return $r;
    }
}


