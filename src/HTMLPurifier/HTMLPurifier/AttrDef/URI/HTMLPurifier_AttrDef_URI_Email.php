<?php

namespace Security\HTMLPurifier\HTMLPurifier\AttrDef\URI;

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_AttrDef;

abstract class HTMLPurifier_AttrDef_URI_Email extends HTMLPurifier_AttrDef
{

    /**
     * Unpacks a mailbox into its display-name and address
     * @param string $string
     * @return mixed
     */
    public function unpack($string)
    {
        // needs to be implemented
    }

}


