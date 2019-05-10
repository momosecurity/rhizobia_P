<?php

namespace Security\HTMLPurifier\HTMLPurifier\AttrTransform;
/**
 * Sets height/width defaults for <textarea>
 */

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_AttrTransform;

class HTMLPurifier_AttrTransform_Textarea extends HTMLPurifier_AttrTransform
{
    /**
     * @param array $attr
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return array
     */
    public function transform($attr, $config, $context)
    {
        // Calculated from Firefox
        if (!isset($attr['cols'])) {
            $attr['cols'] = '22';
        }
        if (!isset($attr['rows'])) {
            $attr['rows'] = '3';
        }
        return $attr;
    }
}


