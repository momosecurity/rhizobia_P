<?php

namespace Security\HTMLPurifier\HTMLPurifier\AttrDef;
/**
 * Validates arbitrary text according to the HTML spec.
 */

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_AttrDef;

class HTMLPurifier_AttrDef_Text extends HTMLPurifier_AttrDef
{

    /**
     * @param string $string
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        return $this->parseCDATA($string);
    }
}


