<?php

namespace Security\HTMLPurifier\HTMLPurifier\Strategy;
/**
 * Composite strategy that runs multiple strategies on tokens.
 */

use Security\HTMLPurifier\HTMLPurifier\HTMLPurifier_Strategy;

abstract class HTMLPurifier_Strategy_Composite extends HTMLPurifier_Strategy
{

    /**
     * List of strategies to run tokens through.
     * @type HTMLPurifier_Strategy[]
     */
    protected $strategies = array();

    /**
     * @param HTMLPurifier_Token[] $tokens
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return HTMLPurifier_Token[]
     */
    public function execute($tokens, $config, $context)
    {
        foreach ($this->strategies as $strategy) {
            $tokens = $strategy->execute($tokens, $config, $context);
        }
        return $tokens;
    }
}


