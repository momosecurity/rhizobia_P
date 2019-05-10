<?php

namespace Security\HTMLPurifier\HTMLPurifier\Node;
/**
 * Concrete comment node class.
 */

use Security\HTMLPurifier\HTMLPurifier\Token\HTMLPurifier_Token_Comment;
use Security\HTMLPurifier\HTMLPurifier_Node;

class HTMLPurifier_Node_Comment extends HTMLPurifier_Node
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

    public function toTokenPair()
    {
        return array(new HTMLPurifier_Token_Comment($this->data, $this->line, $this->col), null);
    }
}
