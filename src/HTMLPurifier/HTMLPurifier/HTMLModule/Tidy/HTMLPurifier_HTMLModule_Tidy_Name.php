<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule\Tidy;
/**
 * Name is deprecated, but allowed in strict doctypes, so onl
 */

use Security\HTMLPurifier\HTMLPurifier\AttrTransform\HTMLPurifier_AttrTransform_Name;
use Security\HTMLPurifier\HTMLPurifier\HTMLModule\HTMLPurifier_HTMLModule_Tidy;

class HTMLPurifier_HTMLModule_Tidy_Name extends HTMLPurifier_HTMLModule_Tidy
{
    /**
     * @type string
     */
    public $name = 'Tidy_Name';

    /**
     * @type string
     */
    public $defaultLevel = 'heavy';

    /**
     * @return array
     */
    public function makeFixes()
    {
        $r = array();
        // @name for img, a -----------------------------------------------
        // Technically, it's allowed even on strict, so we allow authors to use
        // it. However, it's deprecated in future versions of XHTML.
        $r['img@name'] =
        $r['a@name'] = new HTMLPurifier_AttrTransform_Name();
        return $r;
    }
}


