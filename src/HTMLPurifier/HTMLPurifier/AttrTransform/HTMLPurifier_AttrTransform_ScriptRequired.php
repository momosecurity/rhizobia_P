<?php

namespace Security\HTMLPurifier\HTMLPurifier\AttrTransform;
/**
 * Implements required attribute stipulation for <script>
 */

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_AttrTransform;

class HTMLPurifier_AttrTransform_ScriptRequired extends HTMLPurifier_AttrTransform
{
    /**
     * @param array $attr
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return array
     */
    public function transform($attr, $config, $context)
    {
        if (!isset($attr['type'])) {
            $attr['type'] = 'text/javascript';
        }
        return $attr;
    }
}


