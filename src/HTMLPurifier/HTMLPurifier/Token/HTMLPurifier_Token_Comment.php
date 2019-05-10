<?php

namespace Security\HTMLPurifier\HTMLPurifier\Token;
/**
 * Concrete comment token class. Generally will be ignored.
 */

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_Token;

class HTMLPurifier_Token_Comment extends HTMLPurifier_Token
{
    /**
     * Character data within comment.
     * @type string
     */
    public $data;

    /**
     * @type bool
     */
    public $is_whitespace = true;

    /**
     * Transparent constructor.
     *
     * @param string $data String comment data.
     * @param int $line
     * @param int $col
     */
    public function __construct($data, $line = null, $col = null)
    {
        $this->data = $data;
        $this->line = $line;
        $this->col = $col;
    }

    public function toNode()
    {
        return new HTMLPurifier_Node_Comment($this->data, $this->line, $this->col);
    }
}


