<?php

namespace Security\HTMLPurifier\HTMLPurifier\Injector;
/**
 * Injector that displays the URL of an anchor instead of linking to it, in addition to showing the text of the link.
 */

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_Injector;
use Security\HTMLPurifier\HTMLPurifier\Token\HTMLPurifier_Token_Text;

class HTMLPurifier_Injector_DisplayLinkURI extends HTMLPurifier_Injector
{
    /**
     * @type string
     */
    public $name = 'DisplayLinkURI';

    /**
     * @type array
     */
    public $needed = array('a');

    /**
     * @param $token
     */
    public function handleElement(&$token)
    {
    }

    /**
     * @param HTMLPurifier_Token $token
     */
    public function handleEnd(&$token)
    {
        if (isset($token->start->attr['href'])) {
            $url = $token->start->attr['href'];
            unset($token->start->attr['href']);
            $token = array($token, new HTMLPurifier_Token_Text(" ($url)"));
        } else {
            // nothing to display
        }
    }
}


