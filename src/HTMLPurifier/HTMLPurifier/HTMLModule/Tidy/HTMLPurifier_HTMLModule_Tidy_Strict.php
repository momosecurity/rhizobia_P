<?php

namespace Security\HTMLPurifier\HTMLPurifier\HTMLModule\Tidy;


use Security\HTMLPurifier\HTMLPurifier\ChildDef\HTMLPurifier_ChildDef_StrictBlockquote;

class HTMLPurifier_HTMLModule_Tidy_Strict extends HTMLPurifier_HTMLModule_Tidy_XHTMLAndHTML4
{
    /**
     * @type string
     */
    public $name = 'Tidy_Strict';

    /**
     * @type string
     */
    public $defaultLevel = 'light';
    /**
     * @type bool
     */
    public $defines_child_def = true;

    /**
     * @return array
     */
    public function makeFixes()
    {
        $r = parent::makeFixes();
        $r['blockquote#content_model_type'] = 'strictblockquote';
        return $r;
    }

    /**
     * @param HTMLPurifier_ElementDef $def
     * @return HTMLPurifier_ChildDef_StrictBlockquote
     */
    public function getChildDef($def)
    {
        if ($def->content_model_type != 'strictblockquote') {
            return parent::getChildDef($def);
        }
        return new HTMLPurifier_ChildDef_StrictBlockquote($def->content_model);
    }
}


