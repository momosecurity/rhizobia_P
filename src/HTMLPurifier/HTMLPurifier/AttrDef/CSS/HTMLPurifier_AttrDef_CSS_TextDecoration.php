<?php

namespace Security\HTMLPurifier\HTMLPurifier\AttrDef\CSS;
/**
 * Validates the value for the CSS property text-decoration
 * @note This class could be generalized into a version that acts sort of
 *       like Enum except you can compound the allowed values.
 */

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_AttrDef;

class HTMLPurifier_AttrDef_CSS_TextDecoration extends HTMLPurifier_AttrDef
{

    /**
     * @param string $string
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool|string
     */
    public function validate($string, $config, $context)
    {
        static $allowed_values = array(
            'line-through' => true,
            'overline' => true,
            'underline' => true,
        );

        $string = strtolower($this->parseCDATA($string));

        if ($string === 'none') {
            return $string;
        }

        $parts = explode(' ', $string);
        $final = '';
        foreach ($parts as $part) {
            if (isset($allowed_values[$part])) {
                $final .= $part . ' ';
            }
        }
        $final = rtrim($final);
        if ($final === '') {
            return false;
        }
        return $final;
    }
}


