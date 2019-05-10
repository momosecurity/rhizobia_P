<?php

namespace Security\HTMLPurifier\HTMLPurifier\URIScheme;
/**
 * Validates http (HyperText Transfer Protocol) as defined by RFC 2616
 */

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_URIScheme;

class HTMLPurifier_URIScheme_http extends HTMLPurifier_URIScheme
{
    /**
     * @type int
     */
    public $default_port = 80;

    /**
     * @type bool
     */
    public $browsable = true;

    /**
     * @type bool
     */
    public $hierarchical = true;

    /**
     * @param HTMLPurifier_URI $uri
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return bool
     */
    public function doValidate(&$uri, $config, $context)
    {
        $uri->userinfo = null;
        return true;
    }
}


