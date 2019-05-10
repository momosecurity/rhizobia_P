<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule\Tidy;

use Security\HTMLPurifier\HTMLPurifier\AttrTransform\HTMLPurifier_AttrTransform_Background;
use Security\HTMLPurifier\HTMLPurifier\AttrTransform\HTMLPurifier_AttrTransform_Length;
use Security\HTMLPurifier\HTMLPurifier\HTMLModule\HTMLPurifier_HTMLModule_Tidy;

class HTMLPurifier_HTMLModule_Tidy_Proprietary extends HTMLPurifier_HTMLModule_Tidy
{

    /**
     * @type string
     */
    public $name = 'Tidy_Proprietary';

    /**
     * @type string
     */
    public $defaultLevel = 'light';

    /**
     * @return array
     */
    public function makeFixes()
    {
        $r = array();
        $r['table@background'] = new HTMLPurifier_AttrTransform_Background();
        $r['td@background'] = new HTMLPurifier_AttrTransform_Background();
        $r['th@background'] = new HTMLPurifier_AttrTransform_Background();
        $r['tr@background'] = new HTMLPurifier_AttrTransform_Background();
        $r['thead@background'] = new HTMLPurifier_AttrTransform_Background();
        $r['tfoot@background'] = new HTMLPurifier_AttrTransform_Background();
        $r['tbody@background'] = new HTMLPurifier_AttrTransform_Background();
        $r['table@height'] = new HTMLPurifier_AttrTransform_Length('height');
        return $r;
    }
}


