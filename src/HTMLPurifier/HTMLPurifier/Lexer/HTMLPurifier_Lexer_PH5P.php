<?php

namespace Security\HTMLPurifier\HTMLPurifier\Lexer;
/**
 * Experimental HTML5-based parser using Jeroen van der Meer's PH5P library.
 * Occupies space in the HTML5 pseudo-namespace, which may cause conflicts.
 *
 * @note
 *    Recent changes to PHP's DOM extension have resulted in some fatal
 *    error conditions with the original version of PH5P. Pending changes,
 *    this lexer will punt to DirectLex if DOM throws an exception.
 */
class HTMLPurifier_Lexer_PH5P extends HTMLPurifier_Lexer_DOMLex
{
    /**
     * @param string $html
     * @param HTMLPurifier_Config $config
     * @param HTMLPurifier_Context $context
     * @return HTMLPurifier_Token[]
     */
    public function tokenizeHTML($html, $config, $context)
    {
        $new_html = $this->normalize($html, $config, $context);
        $new_html = $this->wrapHTML($new_html, $config, $context, false /* no div */);
        try {
            $parser = new HTML5($new_html);
            $doc = $parser->save();
        } catch (DOMException $e) {
            // Uh oh, it failed. Punt to DirectLex.
            $lexer = new HTMLPurifier_Lexer_DirectLex();
            $context->register('PH5PError', $e); // save the error, so we can detect it
            return $lexer->tokenizeHTML($html, $config, $context); // use original HTML
        }
        $tokens = array();
        $this->tokenizeDOM(
            $doc->getElementsByTagName('html')->item(0)-> // <html>
            getElementsByTagName('body')->item(0) //   <body>
            ,
            $tokens, $config
        );
        return $tokens;
    }
}