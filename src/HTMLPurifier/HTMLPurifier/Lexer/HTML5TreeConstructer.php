<?php

namespace Security\HTMLPurifier\HTMLPurifier\Lexer;
class HTML5TreeConstructer
{
    const INIT_PHASE = 0;
    const ROOT_PHASE = 1;
    const MAIN_PHASE = 2;
    const END_PHASE = 3;
    const BEFOR_HEAD = 0;
    const IN_HEAD = 1;
    const AFTER_HEAD = 2;
    const IN_BODY = 3;
    const IN_TABLE = 4;
    const IN_CAPTION = 5;
    const IN_CGROUP = 6;

    // The different phases.
    const IN_TBODY = 7;
    const IN_ROW = 8;
    const IN_CELL = 9;
    const IN_SELECT = 10;

    // The different insertion modes for the main phase.
    const AFTER_BODY = 11;
    const IN_FRAME = 12;
    const AFTR_FRAME = 13;
    const SPECIAL = 0;
    const SCOPING = 1;
    const FORMATTING = 2;
    const PHRASING = 3;
    const MARKER = 0;
    public $stack = array();
    private $phase;
    private $mode;
    private $dom;
    private $foster_parent = null;
    private $a_formatting = array();

    // The different types of elements.
    private $head_pointer = null;
    private $form_pointer = null;
    private $scoping = array('button', 'caption', 'html', 'marquee', 'object', 'table', 'td', 'th');
    private $formatting = array(
        'a',
        'b',
        'big',
        'em',
        'font',
        'i',
        'nobr',
        's',
        'small',
        'strike',
        'strong',
        'tt',
        'u'
    );
    private $special = array(
        'address',
        'area',
        'base',
        'basefont',
        'bgsound',
        'blockquote',
        'body',
        'br',
        'center',
        'col',
        'colgroup',
        'dd',
        'dir',
        'div',
        'dl',
        'dt',
        'embed',
        'fieldset',
        'form',
        'frame',
        'frameset',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'head',
        'hr',
        'iframe',
        'image',
        'img',
        'input',
        'isindex',
        'li',
        'link',
        'listing',
        'menu',
        'meta',
        'noembed',
        'noframes',
        'noscript',
        'ol',
        'optgroup',
        'option',
        'p',
        'param',
        'plaintext',
        'pre',
        'script',
        'select',
        'spacer',
        'style',
        'tbody',
        'textarea',
        'tfoot',
        'thead',
        'title',
        'tr',
        'ul',
        'wbr'
    );

    public function __construct()
    {
        $this->phase = self::INIT_PHASE;
        $this->mode = self::BEFOR_HEAD;
        $this->dom = new \DOMDocument();

        $this->dom->encoding = 'UTF-8';
        $this->dom->preserveWhiteSpace = true;
        $this->dom->substituteEntities = true;
        $this->dom->strictErrorChecking = false;
    }

    // Process tag tokens
    public function emitToken($token)
    {
        switch ($this->phase) {
            case self::INIT_PHASE:
                return $this->initPhase($token);
                break;
            case self::ROOT_PHASE:
                return $this->rootElementPhase($token);
                break;
            case self::MAIN_PHASE:
                return $this->mainPhase($token);
                break;
            case self::END_PHASE :
                return $this->trailingEndPhase($token);
                break;
        }
    }

    private function initPhase($token)
    {
        /* Initially, the tree construction stage must handle each token
        emitted from the tokenisation stage as follows: */

        /* A DOCTYPE token that is marked as being in error
        A comment token
        A start tag token
        An end tag token
        A character token that is not one of one of U+0009 CHARACTER TABULATION,
            U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
            or U+0020 SPACE
        An end-of-file token */
        if ((isset($token['error']) && $token['error']) ||
            $token['type'] === HTML5::COMMENT ||
            $token['type'] === HTML5::STARTTAG ||
            $token['type'] === HTML5::ENDTAG ||
            $token['type'] === HTML5::EOF ||
            ($token['type'] === HTML5::CHARACTR && isset($token['data']) &&
                !preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data']))
        ) {
            /* This specification does not define how to handle this case. In
            particular, user agents may ignore the entirety of this specification
            altogether for such documents, and instead invoke special parse modes
            with a greater emphasis on backwards compatibility. */

            $this->phase = self::ROOT_PHASE;
            return $this->rootElementPhase($token);

            /* A DOCTYPE token marked as being correct */
        } elseif (isset($token['error']) && !$token['error']) {
            /* Append a DocumentType node to the Document  node, with the name
            attribute set to the name given in the DOCTYPE token (which will be
            "HTML"), and the other attributes specific to DocumentType objects
            set to null, empty lists, or the empty string as appropriate. */
            $doctype = new \DOMDocumentType(null, null, 'HTML');

            /* Then, switch to the root element phase of the tree construction
            stage. */
            $this->phase = self::ROOT_PHASE;

            /* A character token that is one of one of U+0009 CHARACTER TABULATION,
            U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
            or U+0020 SPACE */
        } elseif (isset($token['data']) && preg_match(
                '/^[\t\n\x0b\x0c ]+$/',
                $token['data']
            )
        ) {
            /* Append that character  to the Document node. */
            $text = $this->dom->createTextNode($token['data']);
            $this->dom->appendChild($text);
        }
    }

    private function rootElementPhase($token)
    {
        /* After the initial phase, as each token is emitted from the tokenisation
        stage, it must be processed as described in this section. */

        /* A DOCTYPE token */
        if ($token['type'] === HTML5::DOCTYPE) {
            // Parse error. Ignore the token.

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the Document object with the data
            attribute set to the data given in the comment token. */
            $comment = $this->dom->createComment($token['data']);
            $this->dom->appendChild($comment);

            /* A character token that is one of one of U+0009 CHARACTER TABULATION,
            U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
            or U+0020 SPACE */
        } elseif ($token['type'] === HTML5::CHARACTR &&
            preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])
        ) {
            /* Append that character  to the Document node. */
            $text = $this->dom->createTextNode($token['data']);
            $this->dom->appendChild($text);

            /* A character token that is not one of U+0009 CHARACTER TABULATION,
                U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED
                (FF), or U+0020 SPACE
            A start tag token
            An end tag token
            An end-of-file token */
        } elseif (($token['type'] === HTML5::CHARACTR &&
                !preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])) ||
            $token['type'] === HTML5::STARTTAG ||
            $token['type'] === HTML5::ENDTAG ||
            $token['type'] === HTML5::EOF
        ) {
            /* Create an HTMLElement node with the tag name html, in the HTML
            namespace. Append it to the Document object. Switch to the main
            phase and reprocess the current token. */
            $html = $this->dom->createElement('html');
            $this->dom->appendChild($html);
            $this->stack[] = $html;

            $this->phase = self::MAIN_PHASE;
            return $this->mainPhase($token);
        }
    }

    private function mainPhase($token)
    {
        /* Tokens in the main phase must be handled as follows: */

        /* A DOCTYPE token */
        if ($token['type'] === HTML5::DOCTYPE) {
            // Parse error. Ignore the token.

            /* A start tag token with the tag name "html" */
        } elseif ($token['type'] === HTML5::STARTTAG && $token['name'] === 'html') {
            /* If this start tag token was not the first start tag token, then
            it is a parse error. */

            /* For each attribute on the token, check to see if the attribute
            is already present on the top element of the stack of open elements.
            If it is not, add the attribute and its corresponding value to that
            element. */
            foreach ($token['attr'] as $attr) {
                if (!$this->stack[0]->hasAttribute($attr['name'])) {
                    $this->stack[0]->setAttribute($attr['name'], $attr['value']);
                }
            }

            /* An end-of-file token */
        } elseif ($token['type'] === HTML5::EOF) {
            /* Generate implied end tags. */
            $this->generateImpliedEndTags();

            /* Anything else. */
        } else {
            /* Depends on the insertion mode: */
            switch ($this->mode) {
                case self::BEFOR_HEAD:
                    return $this->beforeHead($token);
                    break;
                case self::IN_HEAD:
                    return $this->inHead($token);
                    break;
                case self::AFTER_HEAD:
                    return $this->afterHead($token);
                    break;
                case self::IN_BODY:
                    return $this->inBody($token);
                    break;
                case self::IN_TABLE:
                    return $this->inTable($token);
                    break;
                case self::IN_CAPTION:
                    return $this->inCaption($token);
                    break;
                case self::IN_CGROUP:
                    return $this->inColumnGroup($token);
                    break;
                case self::IN_TBODY:
                    return $this->inTableBody($token);
                    break;
                case self::IN_ROW:
                    return $this->inRow($token);
                    break;
                case self::IN_CELL:
                    return $this->inCell($token);
                    break;
                case self::IN_SELECT:
                    return $this->inSelect($token);
                    break;
                case self::AFTER_BODY:
                    return $this->afterBody($token);
                    break;
                case self::IN_FRAME:
                    return $this->inFrameset($token);
                    break;
                case self::AFTR_FRAME:
                    return $this->afterFrameset($token);
                    break;
                case self::END_PHASE:
                    return $this->trailingEndPhase($token);
                    break;
            }
        }
    }

    private function generateImpliedEndTags($exclude = array())
    {
        /* When the steps below require the UA to generate implied end tags,
        then, if the current node is a dd element, a dt element, an li element,
        a p element, a td element, a th  element, or a tr element, the UA must
        act as if an end tag with the respective tag name had been seen and
        then generate implied end tags again. */
        $node = end($this->stack);
        $elements = array_diff(array('dd', 'dt', 'li', 'p', 'td', 'th', 'tr'), $exclude);

        while (in_array(end($this->stack)->nodeName, $elements)) {
            array_pop($this->stack);
        }
    }

    private function beforeHead($token)
    {
        /* Handle the token as follows: */

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE */
        if ($token['type'] === HTML5::CHARACTR &&
            preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])
        ) {
            /* Append the character to the current node. */
            $this->insertText($token['data']);

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the current node with the data attribute
            set to the data given in the comment token. */
            $this->insertComment($token['data']);

            /* A start tag token with the tag name "head" */
        } elseif ($token['type'] === HTML5::STARTTAG && $token['name'] === 'head') {
            /* Create an element for the token, append the new element to the
            current node and push it onto the stack of open elements. */
            $element = $this->insertElement($token);

            /* Set the head element pointer to this new element node. */
            $this->head_pointer = $element;

            /* Change the insertion mode to "in head". */
            $this->mode = self::IN_HEAD;

            /* A start tag token whose tag name is one of: "base", "link", "meta",
            "script", "style", "title". Or an end tag with the tag name "html".
            Or a character token that is not one of U+0009 CHARACTER TABULATION,
            U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
            or U+0020 SPACE. Or any other start tag token */
        } elseif ($token['type'] === HTML5::STARTTAG ||
            ($token['type'] === HTML5::ENDTAG && $token['name'] === 'html') ||
            ($token['type'] === HTML5::CHARACTR && !preg_match(
                    '/^[\t\n\x0b\x0c ]$/',
                    $token['data']
                ))
        ) {
            /* Act as if a start tag token with the tag name "head" and no
            attributes had been seen, then reprocess the current token. */
            $this->beforeHead(
                array(
                    'name' => 'head',
                    'type' => HTML5::STARTTAG,
                    'attr' => array()
                )
            );

            return $this->inHead($token);

            /* Any other end tag */
        } elseif ($token['type'] === HTML5::ENDTAG) {
            /* Parse error. Ignore the token. */
        }
    }

    private function insertText($data)
    {
        $text = $this->dom->createTextNode($data);
        $this->appendToRealParent($text);
    }

    private function appendToRealParent($node)
    {
        if ($this->foster_parent === null) {
            end($this->stack)->appendChild($node);

        } elseif ($this->foster_parent !== null) {
            /* If the foster parent element is the parent element of the
            last table element in the stack of open elements, then the new
            node must be inserted immediately before the last table element
            in the stack of open elements in the foster parent element;
            otherwise, the new node must be appended to the foster parent
            element. */
            for ($n = count($this->stack) - 1; $n >= 0; $n--) {
                if ($this->stack[$n]->nodeName === 'table' &&
                    $this->stack[$n]->parentNode !== null
                ) {
                    $table = $this->stack[$n];
                    break;
                }
            }

            if (isset($table) && $this->foster_parent->isSameNode($table->parentNode)) {
                $this->foster_parent->insertBefore($node, $table);
            } else {
                $this->foster_parent->appendChild($node);
            }

            $this->foster_parent = null;
        }
    }

    private function insertComment($data)
    {
        $comment = $this->dom->createComment($data);
        $this->appendToRealParent($comment);
    }

    private function insertElement($token, $append = true, $check = false)
    {
        // Proprietary workaround for libxml2's limitations with tag names
        if ($check) {
            // Slightly modified HTML5 tag-name modification,
            // removing anything that's not an ASCII letter, digit, or hyphen
            $token['name'] = preg_replace('/[^a-z0-9-]/i', '', $token['name']);
            // Remove leading hyphens and numbers
            $token['name'] = ltrim($token['name'], '-0..9');
            // In theory, this should ever be needed, but just in case
            if ($token['name'] === '') {
                $token['name'] = 'span';
            } // arbitrary generic choice
        }

        $el = $this->dom->createElement($token['name']);

        foreach ($token['attr'] as $attr) {
            if (!$el->hasAttribute($attr['name'])) {
                $el->setAttribute($attr['name'], $attr['value']);
            }
        }

        $this->appendToRealParent($el);
        $this->stack[] = $el;

        return $el;
    }

    private function inHead($token)
    {
        /* Handle the token as follows: */

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE.

        THIS DIFFERS FROM THE SPEC: If the current node is either a title, style
        or script element, append the character to the current node regardless
        of its content. */
        if (($token['type'] === HTML5::CHARACTR &&
                preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])) || (
                $token['type'] === HTML5::CHARACTR && in_array(
                    end($this->stack)->nodeName,
                    array('title', 'style', 'script')
                ))
        ) {
            /* Append the character to the current node. */
            $this->insertText($token['data']);

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the current node with the data attribute
            set to the data given in the comment token. */
            $this->insertComment($token['data']);

        } elseif ($token['type'] === HTML5::ENDTAG &&
            in_array($token['name'], array('title', 'style', 'script'))
        ) {
            array_pop($this->stack);
            return HTML5::PCDATA;

            /* A start tag with the tag name "title" */
        } elseif ($token['type'] === HTML5::STARTTAG && $token['name'] === 'title') {
            /* Create an element for the token and append the new element to the
            node pointed to by the head element pointer, or, if that is null
            (innerHTML case), to the current node. */
            if ($this->head_pointer !== null) {
                $element = $this->insertElement($token, false);
                $this->head_pointer->appendChild($element);

            } else {
                $element = $this->insertElement($token);
            }

            /* Switch the tokeniser's content model flag  to the RCDATA state. */
            return HTML5::RCDATA;

            /* A start tag with the tag name "style" */
        } elseif ($token['type'] === HTML5::STARTTAG && $token['name'] === 'style') {
            /* Create an element for the token and append the new element to the
            node pointed to by the head element pointer, or, if that is null
            (innerHTML case), to the current node. */
            if ($this->head_pointer !== null) {
                $element = $this->insertElement($token, false);
                $this->head_pointer->appendChild($element);

            } else {
                $this->insertElement($token);
            }

            /* Switch the tokeniser's content model flag  to the CDATA state. */
            return HTML5::CDATA;

            /* A start tag with the tag name "script" */
        } elseif ($token['type'] === HTML5::STARTTAG && $token['name'] === 'script') {
            /* Create an element for the token. */
            $element = $this->insertElement($token, false);
            $this->head_pointer->appendChild($element);

            /* Switch the tokeniser's content model flag  to the CDATA state. */
            return HTML5::CDATA;

            /* A start tag with the tag name "base", "link", or "meta" */
        } elseif ($token['type'] === HTML5::STARTTAG && in_array(
                $token['name'],
                array('base', 'link', 'meta')
            )
        ) {
            /* Create an element for the token and append the new element to the
            node pointed to by the head element pointer, or, if that is null
            (innerHTML case), to the current node. */
            if ($this->head_pointer !== null) {
                $element = $this->insertElement($token, false);
                $this->head_pointer->appendChild($element);
                array_pop($this->stack);

            } else {
                $this->insertElement($token);
            }

            /* An end tag with the tag name "head" */
        } elseif ($token['type'] === HTML5::ENDTAG && $token['name'] === 'head') {
            /* If the current node is a head element, pop the current node off
            the stack of open elements. */
            if ($this->head_pointer->isSameNode(end($this->stack))) {
                array_pop($this->stack);

                /* Otherwise, this is a parse error. */
            } else {
                // k
            }

            /* Change the insertion mode to "after head". */
            $this->mode = self::AFTER_HEAD;

            /* A start tag with the tag name "head" or an end tag except "html". */
        } elseif (($token['type'] === HTML5::STARTTAG && $token['name'] === 'head') ||
            ($token['type'] === HTML5::ENDTAG && $token['name'] !== 'html')
        ) {
            // Parse error. Ignore the token.

            /* Anything else */
        } else {
            /* If the current node is a head element, act as if an end tag
            token with the tag name "head" had been seen. */
            if ($this->head_pointer->isSameNode(end($this->stack))) {
                $this->inHead(
                    array(
                        'name' => 'head',
                        'type' => HTML5::ENDTAG
                    )
                );

                /* Otherwise, change the insertion mode to "after head". */
            } else {
                $this->mode = self::AFTER_HEAD;
            }

            /* Then, reprocess the current token. */
            return $this->afterHead($token);
        }
    }

    private function afterHead($token)
    {
        /* Handle the token as follows: */

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE */
        if ($token['type'] === HTML5::CHARACTR &&
            preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])
        ) {
            /* Append the character to the current node. */
            $this->insertText($token['data']);

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the current node with the data attribute
            set to the data given in the comment token. */
            $this->insertComment($token['data']);

            /* A start tag token with the tag name "body" */
        } elseif ($token['type'] === HTML5::STARTTAG && $token['name'] === 'body') {
            /* Insert a body element for the token. */
            $this->insertElement($token);

            /* Change the insertion mode to "in body". */
            $this->mode = self::IN_BODY;

            /* A start tag token with the tag name "frameset" */
        } elseif ($token['type'] === HTML5::STARTTAG && $token['name'] === 'frameset') {
            /* Insert a frameset element for the token. */
            $this->insertElement($token);

            /* Change the insertion mode to "in frameset". */
            $this->mode = self::IN_FRAME;

            /* A start tag token whose tag name is one of: "base", "link", "meta",
            "script", "style", "title" */
        } elseif ($token['type'] === HTML5::STARTTAG && in_array(
                $token['name'],
                array('base', 'link', 'meta', 'script', 'style', 'title')
            )
        ) {
            /* Parse error. Switch the insertion mode back to "in head" and
            reprocess the token. */
            $this->mode = self::IN_HEAD;
            return $this->inHead($token);

            /* Anything else */
        } else {
            /* Act as if a start tag token with the tag name "body" and no
            attributes had been seen, and then reprocess the current token. */
            $this->afterHead(
                array(
                    'name' => 'body',
                    'type' => HTML5::STARTTAG,
                    'attr' => array()
                )
            );

            return $this->inBody($token);
        }
    }

    private function inBody($token)
    {
        /* Handle the token as follows: */

        switch ($token['type']) {
            /* A character token */
            case HTML5::CHARACTR:
                /* Reconstruct the active formatting elements, if any. */
                $this->reconstructActiveFormattingElements();

                /* Append the token's character to the current node. */
                $this->insertText($token['data']);
                break;

            /* A comment token */
            case HTML5::COMMENT:
                /* Append a Comment node to the current node with the data
                attribute set to the data given in the comment token. */
                $this->insertComment($token['data']);
                break;

            case HTML5::STARTTAG:
                switch ($token['name']) {
                    /* A start tag token whose tag name is one of: "script",
                    "style" */
                    case 'script':
                    case 'style':
                        /* Process the token as if the insertion mode had been "in
                        head". */
                        return $this->inHead($token);
                        break;

                    /* A start tag token whose tag name is one of: "base", "link",
                    "meta", "title" */
                    case 'base':
                    case 'link':
                    case 'meta':
                    case 'title':
                        /* Parse error. Process the token as if the insertion mode
                        had    been "in head". */
                        return $this->inHead($token);
                        break;

                    /* A start tag token with the tag name "body" */
                    case 'body':
                        /* Parse error. If the second element on the stack of open
                        elements is not a body element, or, if the stack of open
                        elements has only one node on it, then ignore the token.
                        (innerHTML case) */
                        if (count($this->stack) === 1 || $this->stack[1]->nodeName !== 'body') {
                            // Ignore

                            /* Otherwise, for each attribute on the token, check to see
                            if the attribute is already present on the body element (the
                            second element)    on the stack of open elements. If it is not,
                            add the attribute and its corresponding value to that
                            element. */
                        } else {
                            foreach ($token['attr'] as $attr) {
                                if (!$this->stack[1]->hasAttribute($attr['name'])) {
                                    $this->stack[1]->setAttribute($attr['name'], $attr['value']);
                                }
                            }
                        }
                        break;

                    /* A start tag whose tag name is one of: "address",
                    "blockquote", "center", "dir", "div", "dl", "fieldset",
                    "listing", "menu", "ol", "p", "ul" */
                    case 'address':
                    case 'blockquote':
                    case 'center':
                    case 'dir':
                    case 'div':
                    case 'dl':
                    case 'fieldset':
                    case 'listing':
                    case 'menu':
                    case 'ol':
                    case 'p':
                    case 'ul':
                        /* If the stack of open elements has a p element in scope,
                        then act as if an end tag with the tag name p had been
                        seen. */
                        if ($this->elementInScope('p')) {
                            $this->emitToken(
                                array(
                                    'name' => 'p',
                                    'type' => HTML5::ENDTAG
                                )
                            );
                        }

                        /* Insert an HTML element for the token. */
                        $this->insertElement($token);
                        break;

                    /* A start tag whose tag name is "form" */
                    case 'form':
                        /* If the form element pointer is not null, ignore the
                        token with a parse error. */
                        if ($this->form_pointer !== null) {
                            // Ignore.

                            /* Otherwise: */
                        } else {
                            /* If the stack of open elements has a p element in
                            scope, then act as if an end tag with the tag name p
                            had been seen. */
                            if ($this->elementInScope('p')) {
                                $this->emitToken(
                                    array(
                                        'name' => 'p',
                                        'type' => HTML5::ENDTAG
                                    )
                                );
                            }

                            /* Insert an HTML element for the token, and set the
                            form element pointer to point to the element created. */
                            $element = $this->insertElement($token);
                            $this->form_pointer = $element;
                        }
                        break;

                    /* A start tag whose tag name is "li", "dd" or "dt" */
                    case 'li':
                    case 'dd':
                    case 'dt':
                        /* If the stack of open elements has a p  element in scope,
                        then act as if an end tag with the tag name p had been
                        seen. */
                        if ($this->elementInScope('p')) {
                            $this->emitToken(
                                array(
                                    'name' => 'p',
                                    'type' => HTML5::ENDTAG
                                )
                            );
                        }

                        $stack_length = count($this->stack) - 1;

                        for ($n = $stack_length; 0 <= $n; $n--) {
                            /* 1. Initialise node to be the current node (the
                            bottommost node of the stack). */
                            $stop = false;
                            $node = $this->stack[$n];
                            $cat = $this->getElementCategory($node->tagName);

                            /* 2. If node is an li, dd or dt element, then pop all
                            the    nodes from the current node up to node, including
                            node, then stop this algorithm. */
                            if ($token['name'] === $node->tagName || ($token['name'] !== 'li'
                                    && ($node->tagName === 'dd' || $node->tagName === 'dt'))
                            ) {
                                for ($x = $stack_length; $x >= $n; $x--) {
                                    array_pop($this->stack);
                                }

                                break;
                            }

                            /* 3. If node is not in the formatting category, and is
                            not    in the phrasing category, and is not an address or
                            div element, then stop this algorithm. */
                            if ($cat !== self::FORMATTING && $cat !== self::PHRASING &&
                                $node->tagName !== 'address' && $node->tagName !== 'div'
                            ) {
                                break;
                            }
                        }

                        /* Finally, insert an HTML element with the same tag
                        name as the    token's. */
                        $this->insertElement($token);
                        break;

                    /* A start tag token whose tag name is "plaintext" */
                    case 'plaintext':
                        /* If the stack of open elements has a p  element in scope,
                        then act as if an end tag with the tag name p had been
                        seen. */
                        if ($this->elementInScope('p')) {
                            $this->emitToken(
                                array(
                                    'name' => 'p',
                                    'type' => HTML5::ENDTAG
                                )
                            );
                        }

                        /* Insert an HTML element for the token. */
                        $this->insertElement($token);

                        return HTML5::PLAINTEXT;
                        break;

                    /* A start tag whose tag name is one of: "h1", "h2", "h3", "h4",
                    "h5", "h6" */
                    case 'h1':
                    case 'h2':
                    case 'h3':
                    case 'h4':
                    case 'h5':
                    case 'h6':
                        /* If the stack of open elements has a p  element in scope,
                        then act as if an end tag with the tag name p had been seen. */
                        if ($this->elementInScope('p')) {
                            $this->emitToken(
                                array(
                                    'name' => 'p',
                                    'type' => HTML5::ENDTAG
                                )
                            );
                        }

                        /* If the stack of open elements has in scope an element whose
                        tag name is one of "h1", "h2", "h3", "h4", "h5", or "h6", then
                        this is a parse error; pop elements from the stack until an
                        element with one of those tag names has been popped from the
                        stack. */
                        while ($this->elementInScope(array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'))) {
                            array_pop($this->stack);
                        }

                        /* Insert an HTML element for the token. */
                        $this->insertElement($token);
                        break;

                    /* A start tag whose tag name is "a" */
                    case 'a':
                        /* If the list of active formatting elements contains
                        an element whose tag name is "a" between the end of the
                        list and the last marker on the list (or the start of
                        the list if there is no marker on the list), then this
                        is a parse error; act as if an end tag with the tag name
                        "a" had been seen, then remove that element from the list
                        of active formatting elements and the stack of open
                        elements if the end tag didn't already remove it (it
                        might not have if the element is not in table scope). */
                        $leng = count($this->a_formatting);

                        for ($n = $leng - 1; $n >= 0; $n--) {
                            if ($this->a_formatting[$n] === self::MARKER) {
                                break;

                            } elseif ($this->a_formatting[$n]->nodeName === 'a') {
                                $this->emitToken(
                                    array(
                                        'name' => 'a',
                                        'type' => HTML5::ENDTAG
                                    )
                                );
                                break;
                            }
                        }

                        /* Reconstruct the active formatting elements, if any. */
                        $this->reconstructActiveFormattingElements();

                        /* Insert an HTML element for the token. */
                        $el = $this->insertElement($token);

                        /* Add that element to the list of active formatting
                        elements. */
                        $this->a_formatting[] = $el;
                        break;

                    /* A start tag whose tag name is one of: "b", "big", "em", "font",
                    "i", "nobr", "s", "small", "strike", "strong", "tt", "u" */
                    case 'b':
                    case 'big':
                    case 'em':
                    case 'font':
                    case 'i':
                    case 'nobr':
                    case 's':
                    case 'small':
                    case 'strike':
                    case 'strong':
                    case 'tt':
                    case 'u':
                        /* Reconstruct the active formatting elements, if any. */
                        $this->reconstructActiveFormattingElements();

                        /* Insert an HTML element for the token. */
                        $el = $this->insertElement($token);

                        /* Add that element to the list of active formatting
                        elements. */
                        $this->a_formatting[] = $el;
                        break;

                    /* A start tag token whose tag name is "button" */
                    case 'button':
                        /* If the stack of open elements has a button element in scope,
                        then this is a parse error; act as if an end tag with the tag
                        name "button" had been seen, then reprocess the token. (We don't
                        do that. Unnecessary.) */
                        if ($this->elementInScope('button')) {
                            $this->inBody(
                                array(
                                    'name' => 'button',
                                    'type' => HTML5::ENDTAG
                                )
                            );
                        }

                        /* Reconstruct the active formatting elements, if any. */
                        $this->reconstructActiveFormattingElements();

                        /* Insert an HTML element for the token. */
                        $this->insertElement($token);

                        /* Insert a marker at the end of the list of active
                        formatting elements. */
                        $this->a_formatting[] = self::MARKER;
                        break;

                    /* A start tag token whose tag name is one of: "marquee", "object" */
                    case 'marquee':
                    case 'object':
                        /* Reconstruct the active formatting elements, if any. */
                        $this->reconstructActiveFormattingElements();

                        /* Insert an HTML element for the token. */
                        $this->insertElement($token);

                        /* Insert a marker at the end of the list of active
                        formatting elements. */
                        $this->a_formatting[] = self::MARKER;
                        break;

                    /* A start tag token whose tag name is "xmp" */
                    case 'xmp':
                        /* Reconstruct the active formatting elements, if any. */
                        $this->reconstructActiveFormattingElements();

                        /* Insert an HTML element for the token. */
                        $this->insertElement($token);

                        /* Switch the content model flag to the CDATA state. */
                        return HTML5::CDATA;
                        break;

                    /* A start tag whose tag name is "table" */
                    case 'table':
                        /* If the stack of open elements has a p element in scope,
                        then act as if an end tag with the tag name p had been seen. */
                        if ($this->elementInScope('p')) {
                            $this->emitToken(
                                array(
                                    'name' => 'p',
                                    'type' => HTML5::ENDTAG
                                )
                            );
                        }

                        /* Insert an HTML element for the token. */
                        $this->insertElement($token);

                        /* Change the insertion mode to "in table". */
                        $this->mode = self::IN_TABLE;
                        break;

                    /* A start tag whose tag name is one of: "area", "basefont",
                    "bgsound", "br", "embed", "img", "param", "spacer", "wbr" */
                    case 'area':
                    case 'basefont':
                    case 'bgsound':
                    case 'br':
                    case 'embed':
                    case 'img':
                    case 'param':
                    case 'spacer':
                    case 'wbr':
                        /* Reconstruct the active formatting elements, if any. */
                        $this->reconstructActiveFormattingElements();

                        /* Insert an HTML element for the token. */
                        $this->insertElement($token);

                        /* Immediately pop the current node off the stack of open elements. */
                        array_pop($this->stack);
                        break;

                    /* A start tag whose tag name is "hr" */
                    case 'hr':
                        /* If the stack of open elements has a p element in scope,
                        then act as if an end tag with the tag name p had been seen. */
                        if ($this->elementInScope('p')) {
                            $this->emitToken(
                                array(
                                    'name' => 'p',
                                    'type' => HTML5::ENDTAG
                                )
                            );
                        }

                        /* Insert an HTML element for the token. */
                        $this->insertElement($token);

                        /* Immediately pop the current node off the stack of open elements. */
                        array_pop($this->stack);
                        break;

                    /* A start tag whose tag name is "image" */
                    case 'image':
                        /* Parse error. Change the token's tag name to "img" and
                        reprocess it. (Don't ask.) */
                        $token['name'] = 'img';
                        return $this->inBody($token);
                        break;

                    /* A start tag whose tag name is "input" */
                    case 'input':
                        /* Reconstruct the active formatting elements, if any. */
                        $this->reconstructActiveFormattingElements();

                        /* Insert an input element for the token. */
                        $element = $this->insertElement($token, false);

                        /* If the form element pointer is not null, then associate the
                        input element with the form element pointed to by the form
                        element pointer. */
                        $this->form_pointer !== null
                            ? $this->form_pointer->appendChild($element)
                            : end($this->stack)->appendChild($element);

                        /* Pop that input element off the stack of open elements. */
                        array_pop($this->stack);
                        break;

                    /* A start tag whose tag name is "isindex" */
                    case 'isindex':
                        /* Parse error. */
                        // w/e

                        /* If the form element pointer is not null,
                        then ignore the token. */
                        if ($this->form_pointer === null) {
                            /* Act as if a start tag token with the tag name "form" had
                            been seen. */
                            $this->inBody(
                                array(
                                    'name' => 'body',
                                    'type' => HTML5::STARTTAG,
                                    'attr' => array()
                                )
                            );

                            /* Act as if a start tag token with the tag name "hr" had
                            been seen. */
                            $this->inBody(
                                array(
                                    'name' => 'hr',
                                    'type' => HTML5::STARTTAG,
                                    'attr' => array()
                                )
                            );

                            /* Act as if a start tag token with the tag name "p" had
                            been seen. */
                            $this->inBody(
                                array(
                                    'name' => 'p',
                                    'type' => HTML5::STARTTAG,
                                    'attr' => array()
                                )
                            );

                            /* Act as if a start tag token with the tag name "label"
                            had been seen. */
                            $this->inBody(
                                array(
                                    'name' => 'label',
                                    'type' => HTML5::STARTTAG,
                                    'attr' => array()
                                )
                            );

                            /* Act as if a stream of character tokens had been seen. */
                            $this->insertText(
                                'This is a searchable index. ' .
                                'Insert your search keywords here: '
                            );

                            /* Act as if a start tag token with the tag name "input"
                            had been seen, with all the attributes from the "isindex"
                            token, except with the "name" attribute set to the value
                            "isindex" (ignoring any explicit "name" attribute). */
                            $attr = $token['attr'];
                            $attr[] = array('name' => 'name', 'value' => 'isindex');

                            $this->inBody(
                                array(
                                    'name' => 'input',
                                    'type' => HTML5::STARTTAG,
                                    'attr' => $attr
                                )
                            );

                            /* Act as if a stream of character tokens had been seen
                            (see below for what they should say). */
                            $this->insertText(
                                'This is a searchable index. ' .
                                'Insert your search keywords here: '
                            );

                            /* Act as if an end tag token with the tag name "label"
                            had been seen. */
                            $this->inBody(
                                array(
                                    'name' => 'label',
                                    'type' => HTML5::ENDTAG
                                )
                            );

                            /* Act as if an end tag token with the tag name "p" had
                            been seen. */
                            $this->inBody(
                                array(
                                    'name' => 'p',
                                    'type' => HTML5::ENDTAG
                                )
                            );

                            /* Act as if a start tag token with the tag name "hr" had
                            been seen. */
                            $this->inBody(
                                array(
                                    'name' => 'hr',
                                    'type' => HTML5::ENDTAG
                                )
                            );

                            /* Act as if an end tag token with the tag name "form" had
                            been seen. */
                            $this->inBody(
                                array(
                                    'name' => 'form',
                                    'type' => HTML5::ENDTAG
                                )
                            );
                        }
                        break;

                    /* A start tag whose tag name is "textarea" */
                    case 'textarea':
                        $this->insertElement($token);

                        /* Switch the tokeniser's content model flag to the
                        RCDATA state. */
                        return HTML5::RCDATA;
                        break;

                    /* A start tag whose tag name is one of: "iframe", "noembed",
                    "noframes" */
                    case 'iframe':
                    case 'noembed':
                    case 'noframes':
                        $this->insertElement($token);

                        /* Switch the tokeniser's content model flag to the CDATA state. */
                        return HTML5::CDATA;
                        break;

                    /* A start tag whose tag name is "select" */
                    case 'select':
                        /* Reconstruct the active formatting elements, if any. */
                        $this->reconstructActiveFormattingElements();

                        /* Insert an HTML element for the token. */
                        $this->insertElement($token);

                        /* Change the insertion mode to "in select". */
                        $this->mode = self::IN_SELECT;
                        break;

                    /* A start or end tag whose tag name is one of: "caption", "col",
                    "colgroup", "frame", "frameset", "head", "option", "optgroup",
                    "tbody", "td", "tfoot", "th", "thead", "tr". */
                    case 'caption':
                    case 'col':
                    case 'colgroup':
                    case 'frame':
                    case 'frameset':
                    case 'head':
                    case 'option':
                    case 'optgroup':
                    case 'tbody':
                    case 'td':
                    case 'tfoot':
                    case 'th':
                    case 'thead':
                    case 'tr':
                        // Parse error. Ignore the token.
                        break;

                    /* A start or end tag whose tag name is one of: "event-source",
                    "section", "nav", "article", "aside", "header", "footer",
                    "datagrid", "command" */
                    case 'event-source':
                    case 'section':
                    case 'nav':
                    case 'article':
                    case 'aside':
                    case 'header':
                    case 'footer':
                    case 'datagrid':
                    case 'command':
                        // Work in progress!
                        break;

                    /* A start tag token not covered by the previous entries */
                    default:
                        /* Reconstruct the active formatting elements, if any. */
                        $this->reconstructActiveFormattingElements();

                        $this->insertElement($token, true, true);
                        break;
                }
                break;

            case HTML5::ENDTAG:
                switch ($token['name']) {
                    /* An end tag with the tag name "body" */
                    case 'body':
                        /* If the second element in the stack of open elements is
                        not a body element, this is a parse error. Ignore the token.
                        (innerHTML case) */
                        if (count($this->stack) < 2 || $this->stack[1]->nodeName !== 'body') {
                            // Ignore.

                            /* If the current node is not the body element, then this
                            is a parse error. */
                        } elseif (end($this->stack)->nodeName !== 'body') {
                            // Parse error.
                        }

                        /* Change the insertion mode to "after body". */
                        $this->mode = self::AFTER_BODY;
                        break;

                    /* An end tag with the tag name "html" */
                    case 'html':
                        /* Act as if an end tag with tag name "body" had been seen,
                        then, if that token wasn't ignored, reprocess the current
                        token. */
                        $this->inBody(
                            array(
                                'name' => 'body',
                                'type' => HTML5::ENDTAG
                            )
                        );

                        return $this->afterBody($token);
                        break;

                    /* An end tag whose tag name is one of: "address", "blockquote",
                    "center", "dir", "div", "dl", "fieldset", "listing", "menu",
                    "ol", "pre", "ul" */
                    case 'address':
                    case 'blockquote':
                    case 'center':
                    case 'dir':
                    case 'div':
                    case 'dl':
                    case 'fieldset':
                    case 'listing':
                    case 'menu':
                    case 'ol':
                    case 'pre':
                    case 'ul':
                        /* If the stack of open elements has an element in scope
                        with the same tag name as that of the token, then generate
                        implied end tags. */
                        if ($this->elementInScope($token['name'])) {
                            $this->generateImpliedEndTags();

                            /* Now, if the current node is not an element with
                            the same tag name as that of the token, then this
                            is a parse error. */
                            // w/e

                            /* If the stack of open elements has an element in
                            scope with the same tag name as that of the token,
                            then pop elements from this stack until an element
                            with that tag name has been popped from the stack. */
                            for ($n = count($this->stack) - 1; $n >= 0; $n--) {
                                if ($this->stack[$n]->nodeName === $token['name']) {
                                    $n = -1;
                                }

                                array_pop($this->stack);
                            }
                        }
                        break;

                    /* An end tag whose tag name is "form" */
                    case 'form':
                        /* If the stack of open elements has an element in scope
                        with the same tag name as that of the token, then generate
                        implied    end tags. */
                        if ($this->elementInScope($token['name'])) {
                            $this->generateImpliedEndTags();

                        }

                        if (end($this->stack)->nodeName !== $token['name']) {
                            /* Now, if the current node is not an element with the
                            same tag name as that of the token, then this is a parse
                            error. */
                            // w/e

                        } else {
                            /* Otherwise, if the current node is an element with
                            the same tag name as that of the token pop that element
                            from the stack. */
                            array_pop($this->stack);
                        }

                        /* In any case, set the form element pointer to null. */
                        $this->form_pointer = null;
                        break;

                    /* An end tag whose tag name is "p" */
                    case 'p':
                        /* If the stack of open elements has a p element in scope,
                        then generate implied end tags, except for p elements. */
                        if ($this->elementInScope('p')) {
                            $this->generateImpliedEndTags(array('p'));

                            /* If the current node is not a p element, then this is
                            a parse error. */
                            // k

                            /* If the stack of open elements has a p element in
                            scope, then pop elements from this stack until the stack
                            no longer has a p element in scope. */
                            for ($n = count($this->stack) - 1; $n >= 0; $n--) {
                                if ($this->elementInScope('p')) {
                                    array_pop($this->stack);

                                } else {
                                    break;
                                }
                            }
                        }
                        break;

                    /* An end tag whose tag name is "dd", "dt", or "li" */
                    case 'dd':
                    case 'dt':
                    case 'li':
                        /* If the stack of open elements has an element in scope
                        whose tag name matches the tag name of the token, then
                        generate implied end tags, except for elements with the
                        same tag name as the token. */
                        if ($this->elementInScope($token['name'])) {
                            $this->generateImpliedEndTags(array($token['name']));

                            /* If the current node is not an element with the same
                            tag name as the token, then this is a parse error. */
                            // w/e

                            /* If the stack of open elements has an element in scope
                            whose tag name matches the tag name of the token, then
                            pop elements from this stack until an element with that
                            tag name has been popped from the stack. */
                            for ($n = count($this->stack) - 1; $n >= 0; $n--) {
                                if ($this->stack[$n]->nodeName === $token['name']) {
                                    $n = -1;
                                }

                                array_pop($this->stack);
                            }
                        }
                        break;

                    /* An end tag whose tag name is one of: "h1", "h2", "h3", "h4",
                    "h5", "h6" */
                    case 'h1':
                    case 'h2':
                    case 'h3':
                    case 'h4':
                    case 'h5':
                    case 'h6':
                        $elements = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');

                        /* If the stack of open elements has in scope an element whose
                        tag name is one of "h1", "h2", "h3", "h4", "h5", or "h6", then
                        generate implied end tags. */
                        if ($this->elementInScope($elements)) {
                            $this->generateImpliedEndTags();

                            /* Now, if the current node is not an element with the same
                            tag name as that of the token, then this is a parse error. */
                            // w/e

                            /* If the stack of open elements has in scope an element
                            whose tag name is one of "h1", "h2", "h3", "h4", "h5", or
                            "h6", then pop elements from the stack until an element
                            with one of those tag names has been popped from the stack. */
                            while ($this->elementInScope($elements)) {
                                array_pop($this->stack);
                            }
                        }
                        break;

                    /* An end tag whose tag name is one of: "a", "b", "big", "em",
                    "font", "i", "nobr", "s", "small", "strike", "strong", "tt", "u" */
                    case 'a':
                    case 'b':
                    case 'big':
                    case 'em':
                    case 'font':
                    case 'i':
                    case 'nobr':
                    case 's':
                    case 'small':
                    case 'strike':
                    case 'strong':
                    case 'tt':
                    case 'u':
                        /* 1. Let the formatting element be the last element in
                        the list of active formatting elements that:
                            * is between the end of the list and the last scope
                            marker in the list, if any, or the start of the list
                            otherwise, and
                            * has the same tag name as the token.
                        */
                        while (true) {
                            for ($a = count($this->a_formatting) - 1; $a >= 0; $a--) {
                                if ($this->a_formatting[$a] === self::MARKER) {
                                    break;

                                } elseif ($this->a_formatting[$a]->tagName === $token['name']) {
                                    $formatting_element = $this->a_formatting[$a];
                                    $in_stack = in_array($formatting_element, $this->stack, true);
                                    $fe_af_pos = $a;
                                    break;
                                }
                            }

                            /* If there is no such node, or, if that node is
                            also in the stack of open elements but the element
                            is not in scope, then this is a parse error. Abort
                            these steps. The token is ignored. */
                            if (!isset($formatting_element) || ($in_stack &&
                                    !$this->elementInScope($token['name']))
                            ) {
                                break;

                                /* Otherwise, if there is such a node, but that node
                                is not in the stack of open elements, then this is a
                                parse error; remove the element from the list, and
                                abort these steps. */
                            } elseif (isset($formatting_element) && !$in_stack) {
                                unset($this->a_formatting[$fe_af_pos]);
                                $this->a_formatting = array_merge($this->a_formatting);
                                break;
                            }

                            /* 2. Let the furthest block be the topmost node in the
                            stack of open elements that is lower in the stack
                            than the formatting element, and is not an element in
                            the phrasing or formatting categories. There might
                            not be one. */
                            $fe_s_pos = array_search($formatting_element, $this->stack, true);
                            $length = count($this->stack);

                            for ($s = $fe_s_pos + 1; $s < $length; $s++) {
                                $category = $this->getElementCategory($this->stack[$s]->nodeName);

                                if ($category !== self::PHRASING && $category !== self::FORMATTING) {
                                    $furthest_block = $this->stack[$s];
                                }
                            }

                            /* 3. If there is no furthest block, then the UA must
                            skip the subsequent steps and instead just pop all
                            the nodes from the bottom of the stack of open
                            elements, from the current node up to the formatting
                            element, and remove the formatting element from the
                            list of active formatting elements. */
                            if (!isset($furthest_block)) {
                                for ($n = $length - 1; $n >= $fe_s_pos; $n--) {
                                    array_pop($this->stack);
                                }

                                unset($this->a_formatting[$fe_af_pos]);
                                $this->a_formatting = array_merge($this->a_formatting);
                                break;
                            }

                            /* 4. Let the common ancestor be the element
                            immediately above the formatting element in the stack
                            of open elements. */
                            $common_ancestor = $this->stack[$fe_s_pos - 1];

                            /* 5. If the furthest block has a parent node, then
                            remove the furthest block from its parent node. */
                            if ($furthest_block->parentNode !== null) {
                                $furthest_block->parentNode->removeChild($furthest_block);
                            }

                            /* 6. Let a bookmark note the position of the
                            formatting element in the list of active formatting
                            elements relative to the elements on either side
                            of it in the list. */
                            $bookmark = $fe_af_pos;

                            /* 7. Let node and last node  be the furthest block.
                            Follow these steps: */
                            $node = $furthest_block;
                            $last_node = $furthest_block;

                            while (true) {
                                for ($n = array_search($node, $this->stack, true) - 1; $n >= 0; $n--) {
                                    /* 7.1 Let node be the element immediately
                                    prior to node in the stack of open elements. */
                                    $node = $this->stack[$n];

                                    /* 7.2 If node is not in the list of active
                                    formatting elements, then remove node from
                                    the stack of open elements and then go back
                                    to step 1. */
                                    if (!in_array($node, $this->a_formatting, true)) {
                                        unset($this->stack[$n]);
                                        $this->stack = array_merge($this->stack);

                                    } else {
                                        break;
                                    }
                                }

                                /* 7.3 Otherwise, if node is the formatting
                                element, then go to the next step in the overall
                                algorithm. */
                                if ($node === $formatting_element) {
                                    break;

                                    /* 7.4 Otherwise, if last node is the furthest
                                    block, then move the aforementioned bookmark to
                                    be immediately after the node in the list of
                                    active formatting elements. */
                                } elseif ($last_node === $furthest_block) {
                                    $bookmark = array_search($node, $this->a_formatting, true) + 1;
                                }

                                /* 7.5 If node has any children, perform a
                                shallow clone of node, replace the entry for
                                node in the list of active formatting elements
                                with an entry for the clone, replace the entry
                                for node in the stack of open elements with an
                                entry for the clone, and let node be the clone. */
                                if ($node->hasChildNodes()) {
                                    $clone = $node->cloneNode();
                                    $s_pos = array_search($node, $this->stack, true);
                                    $a_pos = array_search($node, $this->a_formatting, true);

                                    $this->stack[$s_pos] = $clone;
                                    $this->a_formatting[$a_pos] = $clone;
                                    $node = $clone;
                                }

                                /* 7.6 Insert last node into node, first removing
                                it from its previous parent node if any. */
                                if ($last_node->parentNode !== null) {
                                    $last_node->parentNode->removeChild($last_node);
                                }

                                $node->appendChild($last_node);

                                /* 7.7 Let last node be node. */
                                $last_node = $node;
                            }

                            /* 8. Insert whatever last node ended up being in
                            the previous step into the common ancestor node,
                            first removing it from its previous parent node if
                            any. */
                            if ($last_node->parentNode !== null) {
                                $last_node->parentNode->removeChild($last_node);
                            }

                            $common_ancestor->appendChild($last_node);

                            /* 9. Perform a shallow clone of the formatting
                            element. */
                            $clone = $formatting_element->cloneNode();

                            /* 10. Take all of the child nodes of the furthest
                            block and append them to the clone created in the
                            last step. */
                            while ($furthest_block->hasChildNodes()) {
                                $child = $furthest_block->firstChild;
                                $furthest_block->removeChild($child);
                                $clone->appendChild($child);
                            }

                            /* 11. Append that clone to the furthest block. */
                            $furthest_block->appendChild($clone);

                            /* 12. Remove the formatting element from the list
                            of active formatting elements, and insert the clone
                            into the list of active formatting elements at the
                            position of the aforementioned bookmark. */
                            $fe_af_pos = array_search($formatting_element, $this->a_formatting, true);
                            unset($this->a_formatting[$fe_af_pos]);
                            $this->a_formatting = array_merge($this->a_formatting);

                            $af_part1 = array_slice($this->a_formatting, 0, $bookmark - 1);
                            $af_part2 = array_slice($this->a_formatting, $bookmark, count($this->a_formatting));
                            $this->a_formatting = array_merge($af_part1, array($clone), $af_part2);

                            /* 13. Remove the formatting element from the stack
                            of open elements, and insert the clone into the stack
                            of open elements immediately after (i.e. in a more
                            deeply nested position than) the position of the
                            furthest block in that stack. */
                            $fe_s_pos = array_search($formatting_element, $this->stack, true);
                            $fb_s_pos = array_search($furthest_block, $this->stack, true);
                            unset($this->stack[$fe_s_pos]);

                            $s_part1 = array_slice($this->stack, 0, $fb_s_pos);
                            $s_part2 = array_slice($this->stack, $fb_s_pos + 1, count($this->stack));
                            $this->stack = array_merge($s_part1, array($clone), $s_part2);

                            /* 14. Jump back to step 1 in this series of steps. */
                            unset($formatting_element, $fe_af_pos, $fe_s_pos, $furthest_block);
                        }
                        break;

                    /* An end tag token whose tag name is one of: "button",
                    "marquee", "object" */
                    case 'button':
                    case 'marquee':
                    case 'object':
                        /* If the stack of open elements has an element in scope whose
                        tag name matches the tag name of the token, then generate implied
                        tags. */
                        if ($this->elementInScope($token['name'])) {
                            $this->generateImpliedEndTags();

                            /* Now, if the current node is not an element with the same
                            tag name as the token, then this is a parse error. */
                            // k

                            /* Now, if the stack of open elements has an element in scope
                            whose tag name matches the tag name of the token, then pop
                            elements from the stack until that element has been popped from
                            the stack, and clear the list of active formatting elements up
                            to the last marker. */
                            for ($n = count($this->stack) - 1; $n >= 0; $n--) {
                                if ($this->stack[$n]->nodeName === $token['name']) {
                                    $n = -1;
                                }

                                array_pop($this->stack);
                            }

                            $marker = end(array_keys($this->a_formatting, self::MARKER, true));

                            for ($n = count($this->a_formatting) - 1; $n > $marker; $n--) {
                                array_pop($this->a_formatting);
                            }
                        }
                        break;

                    /* Or an end tag whose tag name is one of: "area", "basefont",
                    "bgsound", "br", "embed", "hr", "iframe", "image", "img",
                    "input", "isindex", "noembed", "noframes", "param", "select",
                    "spacer", "table", "textarea", "wbr" */
                    case 'area':
                    case 'basefont':
                    case 'bgsound':
                    case 'br':
                    case 'embed':
                    case 'hr':
                    case 'iframe':
                    case 'image':
                    case 'img':
                    case 'input':
                    case 'isindex':
                    case 'noembed':
                    case 'noframes':
                    case 'param':
                    case 'select':
                    case 'spacer':
                    case 'table':
                    case 'textarea':
                    case 'wbr':
                        // Parse error. Ignore the token.
                        break;

                    /* An end tag token not covered by the previous entries */
                    default:
                        for ($n = count($this->stack) - 1; $n >= 0; $n--) {
                            /* Initialise node to be the current node (the bottommost
                            node of the stack). */
                            $node = end($this->stack);

                            /* If node has the same tag name as the end tag token,
                            then: */
                            if ($token['name'] === $node->nodeName) {
                                /* Generate implied end tags. */
                                $this->generateImpliedEndTags();

                                /* If the tag name of the end tag token does not
                                match the tag name of the current node, this is a
                                parse error. */
                                // k

                                /* Pop all the nodes from the current node up to
                                node, including node, then stop this algorithm. */
                                for ($x = count($this->stack) - $n; $x >= $n; $x--) {
                                    array_pop($this->stack);
                                }

                            } else {
                                $category = $this->getElementCategory($node);

                                if ($category !== self::SPECIAL && $category !== self::SCOPING) {
                                    /* Otherwise, if node is in neither the formatting
                                    category nor the phrasing category, then this is a
                                    parse error. Stop this algorithm. The end tag token
                                    is ignored. */
                                    return false;
                                }
                            }
                        }
                        break;
                }
                break;
        }
    }

    private function reconstructActiveFormattingElements()
    {
        /* 1. If there are no entries in the list of active formatting elements,
        then there is nothing to reconstruct; stop this algorithm. */
        $formatting_elements = count($this->a_formatting);

        if ($formatting_elements === 0) {
            return false;
        }

        /* 3. Let entry be the last (most recently added) element in the list
        of active formatting elements. */
        $entry = end($this->a_formatting);

        /* 2. If the last (most recently added) entry in the list of active
        formatting elements is a marker, or if it is an element that is in the
        stack of open elements, then there is nothing to reconstruct; stop this
        algorithm. */
        if ($entry === self::MARKER || in_array($entry, $this->stack, true)) {
            return false;
        }

        for ($a = $formatting_elements - 1; $a >= 0; true) {
            /* 4. If there are no entries before entry in the list of active
            formatting elements, then jump to step 8. */
            if ($a === 0) {
                $step_seven = false;
                break;
            }

            /* 5. Let entry be the entry one earlier than entry in the list of
            active formatting elements. */
            $a--;
            $entry = $this->a_formatting[$a];

            /* 6. If entry is neither a marker nor an element that is also in
            thetack of open elements, go to step 4. */
            if ($entry === self::MARKER || in_array($entry, $this->stack, true)) {
                break;
            }
        }

        while (true) {
            /* 7. Let entry be the element one later than entry in the list of
            active formatting elements. */
            if (isset($step_seven) && $step_seven === true) {
                $a++;
                $entry = $this->a_formatting[$a];
            }

            /* 8. Perform a shallow clone of the element entry to obtain clone. */
            $clone = $entry->cloneNode();

            /* 9. Append clone to the current node and push it onto the stack
            of open elements  so that it is the new current node. */
            end($this->stack)->appendChild($clone);
            $this->stack[] = $clone;

            /* 10. Replace the entry for entry in the list with an entry for
            clone. */
            $this->a_formatting[$a] = $clone;

            /* 11. If the entry for clone in the list of active formatting
            elements is not the last entry in the list, return to step 7. */
            if (end($this->a_formatting) !== $clone) {
                $step_seven = true;
            } else {
                break;
            }
        }
    }

    private function elementInScope($el, $table = false)
    {
        if (is_array($el)) {
            foreach ($el as $element) {
                if ($this->elementInScope($element, $table)) {
                    return true;
                }
            }

            return false;
        }

        $leng = count($this->stack);

        for ($n = 0; $n < $leng; $n++) {
            /* 1. Initialise node to be the current node (the bottommost node of
            the stack). */
            $node = $this->stack[$leng - 1 - $n];

            if ($node->tagName === $el) {
                /* 2. If node is the target node, terminate in a match state. */
                return true;

            } elseif ($node->tagName === 'table') {
                /* 3. Otherwise, if node is a table element, terminate in a failure
                state. */
                return false;

            } elseif ($table === true && in_array(
                    $node->tagName,
                    array(
                        'caption',
                        'td',
                        'th',
                        'button',
                        'marquee',
                        'object'
                    )
                )
            ) {
                /* 4. Otherwise, if the algorithm is the "has an element in scope"
                variant (rather than the "has an element in table scope" variant),
                and node is one of the following, terminate in a failure state. */
                return false;

            } elseif ($node === $node->ownerDocument->documentElement) {
                /* 5. Otherwise, if node is an html element (root element), terminate
                in a failure state. (This can only happen if the node is the topmost
                node of the    stack of open elements, and prevents the next step from
                being invoked if there are no more elements in the stack.) */
                return false;
            }

            /* Otherwise, set node to the previous entry in the stack of open
            elements and return to step 2. (This will never fail, since the loop
            will always terminate in the previous step if the top of the stack
            is reached.) */
        }
    }

    private function getElementCategory($node)
    {
        $name = $node->tagName;
        if (in_array($name, $this->special)) {
            return self::SPECIAL;
        } elseif (in_array($name, $this->scoping)) {
            return self::SCOPING;
        } elseif (in_array($name, $this->formatting)) {
            return self::FORMATTING;
        } else {
            return self::PHRASING;
        }
    }

    private function afterBody($token)
    {
        /* Handle the token as follows: */

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE */
        if ($token['type'] === HTML5::CHARACTR &&
            preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])
        ) {
            /* Process the token as it would be processed if the insertion mode
            was "in body". */
            $this->inBody($token);

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the first element in the stack of open
            elements (the html element), with the data attribute set to the
            data given in the comment token. */
            $comment = $this->dom->createComment($token['data']);
            $this->stack[0]->appendChild($comment);

            /* An end tag with the tag name "html" */
        } elseif ($token['type'] === HTML5::ENDTAG && $token['name'] === 'html') {
            /* If the parser was originally created in order to handle the
            setting of an element's innerHTML attribute, this is a parse error;
            ignore the token. (The element will be an html element in this
            case.) (innerHTML case) */

            /* Otherwise, switch to the trailing end phase. */
            $this->phase = self::END_PHASE;

            /* Anything else */
        } else {
            /* Parse error. Set the insertion mode to "in body" and reprocess
            the token. */
            $this->mode = self::IN_BODY;
            return $this->inBody($token);
        }
    }

    private function inTable($token)
    {
        $clear = array('html', 'table');

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE */
        if ($token['type'] === HTML5::CHARACTR &&
            preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])
        ) {
            /* Append the character to the current node. */
            $text = $this->dom->createTextNode($token['data']);
            end($this->stack)->appendChild($text);

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the current node with the data
            attribute set to the data given in the comment token. */
            $comment = $this->dom->createComment($token['data']);
            end($this->stack)->appendChild($comment);

            /* A start tag whose tag name is "caption" */
        } elseif ($token['type'] === HTML5::STARTTAG &&
            $token['name'] === 'caption'
        ) {
            /* Clear the stack back to a table context. */
            $this->clearStackToTableContext($clear);

            /* Insert a marker at the end of the list of active
            formatting elements. */
            $this->a_formatting[] = self::MARKER;

            /* Insert an HTML element for the token, then switch the
            insertion mode to "in caption". */
            $this->insertElement($token);
            $this->mode = self::IN_CAPTION;

            /* A start tag whose tag name is "colgroup" */
        } elseif ($token['type'] === HTML5::STARTTAG &&
            $token['name'] === 'colgroup'
        ) {
            /* Clear the stack back to a table context. */
            $this->clearStackToTableContext($clear);

            /* Insert an HTML element for the token, then switch the
            insertion mode to "in column group". */
            $this->insertElement($token);
            $this->mode = self::IN_CGROUP;

            /* A start tag whose tag name is "col" */
        } elseif ($token['type'] === HTML5::STARTTAG &&
            $token['name'] === 'col'
        ) {
            $this->inTable(
                array(
                    'name' => 'colgroup',
                    'type' => HTML5::STARTTAG,
                    'attr' => array()
                )
            );

            $this->inColumnGroup($token);

            /* A start tag whose tag name is one of: "tbody", "tfoot", "thead" */
        } elseif ($token['type'] === HTML5::STARTTAG && in_array(
                $token['name'],
                array('tbody', 'tfoot', 'thead')
            )
        ) {
            /* Clear the stack back to a table context. */
            $this->clearStackToTableContext($clear);

            /* Insert an HTML element for the token, then switch the insertion
            mode to "in table body". */
            $this->insertElement($token);
            $this->mode = self::IN_TBODY;

            /* A start tag whose tag name is one of: "td", "th", "tr" */
        } elseif ($token['type'] === HTML5::STARTTAG &&
            in_array($token['name'], array('td', 'th', 'tr'))
        ) {
            /* Act as if a start tag token with the tag name "tbody" had been
            seen, then reprocess the current token. */
            $this->inTable(
                array(
                    'name' => 'tbody',
                    'type' => HTML5::STARTTAG,
                    'attr' => array()
                )
            );

            return $this->inTableBody($token);

            /* A start tag whose tag name is "table" */
        } elseif ($token['type'] === HTML5::STARTTAG &&
            $token['name'] === 'table'
        ) {
            /* Parse error. Act as if an end tag token with the tag name "table"
            had been seen, then, if that token wasn't ignored, reprocess the
            current token. */
            $this->inTable(
                array(
                    'name' => 'table',
                    'type' => HTML5::ENDTAG
                )
            );

            return $this->mainPhase($token);

            /* An end tag whose tag name is "table" */
        } elseif ($token['type'] === HTML5::ENDTAG &&
            $token['name'] === 'table'
        ) {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. (innerHTML case) */
            if (!$this->elementInScope($token['name'], true)) {
                return false;

                /* Otherwise: */
            } else {
                /* Generate implied end tags. */
                $this->generateImpliedEndTags();

                /* Now, if the current node is not a table element, then this
                is a parse error. */
                // w/e

                /* Pop elements from this stack until a table element has been
                popped from the stack. */
                while (true) {
                    $current = end($this->stack)->nodeName;
                    array_pop($this->stack);

                    if ($current === 'table') {
                        break;
                    }
                }

                /* Reset the insertion mode appropriately. */
                $this->resetInsertionMode();
            }

            /* An end tag whose tag name is one of: "body", "caption", "col",
            "colgroup", "html", "tbody", "td", "tfoot", "th", "thead", "tr" */
        } elseif ($token['type'] === HTML5::ENDTAG && in_array(
                $token['name'],
                array(
                    'body',
                    'caption',
                    'col',
                    'colgroup',
                    'html',
                    'tbody',
                    'td',
                    'tfoot',
                    'th',
                    'thead',
                    'tr'
                )
            )
        ) {
            // Parse error. Ignore the token.

            /* Anything else */
        } else {
            /* Parse error. Process the token as if the insertion mode was "in
            body", with the following exception: */

            /* If the current node is a table, tbody, tfoot, thead, or tr
            element, then, whenever a node would be inserted into the current
            node, it must instead be inserted into the foster parent element. */
            if (in_array(
                end($this->stack)->nodeName,
                array('table', 'tbody', 'tfoot', 'thead', 'tr')
            )
            ) {
                /* The foster parent element is the parent element of the last
                table element in the stack of open elements, if there is a
                table element and it has such a parent element. If there is no
                table element in the stack of open elements (innerHTML case),
                then the foster parent element is the first element in the
                stack of open elements (the html  element). Otherwise, if there
                is a table element in the stack of open elements, but the last
                table element in the stack of open elements has no parent, or
                its parent node is not an element, then the foster parent
                element is the element before the last table element in the
                stack of open elements. */
                for ($n = count($this->stack) - 1; $n >= 0; $n--) {
                    if ($this->stack[$n]->nodeName === 'table') {
                        $table = $this->stack[$n];
                        break;
                    }
                }

                if (isset($table) && $table->parentNode !== null) {
                    $this->foster_parent = $table->parentNode;

                } elseif (!isset($table)) {
                    $this->foster_parent = $this->stack[0];

                } elseif (isset($table) && ($table->parentNode === null ||
                        $table->parentNode->nodeType !== XML_ELEMENT_NODE)
                ) {
                    $this->foster_parent = $this->stack[$n - 1];
                }
            }

            $this->inBody($token);
        }
    }

    private function clearStackToTableContext($elements)
    {
        /* When the steps above require the UA to clear the stack back to a
        table context, it means that the UA must, while the current node is not
        a table element or an html element, pop elements from the stack of open
        elements. If this causes any elements to be popped from the stack, then
        this is a parse error. */
        while (true) {
            $node = end($this->stack)->nodeName;

            if (in_array($node, $elements)) {
                break;
            } else {
                array_pop($this->stack);
            }
        }
    }

    private function inColumnGroup($token)
    {
        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        or U+0020 SPACE */
        if ($token['type'] === HTML5::CHARACTR &&
            preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])
        ) {
            /* Append the character to the current node. */
            $text = $this->dom->createTextNode($token['data']);
            end($this->stack)->appendChild($text);

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the current node with the data
            attribute set to the data given in the comment token. */
            $comment = $this->dom->createComment($token['data']);
            end($this->stack)->appendChild($comment);

            /* A start tag whose tag name is "col" */
        } elseif ($token['type'] === HTML5::STARTTAG && $token['name'] === 'col') {
            /* Insert a col element for the token. Immediately pop the current
            node off the stack of open elements. */
            $this->insertElement($token);
            array_pop($this->stack);

            /* An end tag whose tag name is "colgroup" */
        } elseif ($token['type'] === HTML5::ENDTAG &&
            $token['name'] === 'colgroup'
        ) {
            /* If the current node is the root html element, then this is a
            parse error, ignore the token. (innerHTML case) */
            if (end($this->stack)->nodeName === 'html') {
                // Ignore

                /* Otherwise, pop the current node (which will be a colgroup
                element) from the stack of open elements. Switch the insertion
                mode to "in table". */
            } else {
                array_pop($this->stack);
                $this->mode = self::IN_TABLE;
            }

            /* An end tag whose tag name is "col" */
        } elseif ($token['type'] === HTML5::ENDTAG && $token['name'] === 'col') {
            /* Parse error. Ignore the token. */

            /* Anything else */
        } else {
            /* Act as if an end tag with the tag name "colgroup" had been seen,
            and then, if that token wasn't ignored, reprocess the current token. */
            $this->inColumnGroup(
                array(
                    'name' => 'colgroup',
                    'type' => HTML5::ENDTAG
                )
            );

            return $this->inTable($token);
        }
    }

    private function inTableBody($token)
    {
        $clear = array('tbody', 'tfoot', 'thead', 'html');

        /* A start tag whose tag name is "tr" */
        if ($token['type'] === HTML5::STARTTAG && $token['name'] === 'tr') {
            /* Clear the stack back to a table body context. */
            $this->clearStackToTableContext($clear);

            /* Insert a tr element for the token, then switch the insertion
            mode to "in row". */
            $this->insertElement($token);
            $this->mode = self::IN_ROW;

            /* A start tag whose tag name is one of: "th", "td" */
        } elseif ($token['type'] === HTML5::STARTTAG &&
            ($token['name'] === 'th' || $token['name'] === 'td')
        ) {
            /* Parse error. Act as if a start tag with the tag name "tr" had
            been seen, then reprocess the current token. */
            $this->inTableBody(
                array(
                    'name' => 'tr',
                    'type' => HTML5::STARTTAG,
                    'attr' => array()
                )
            );

            return $this->inRow($token);

            /* An end tag whose tag name is one of: "tbody", "tfoot", "thead" */
        } elseif ($token['type'] === HTML5::ENDTAG &&
            in_array($token['name'], array('tbody', 'tfoot', 'thead'))
        ) {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. */
            if (!$this->elementInScope($token['name'], true)) {
                // Ignore

                /* Otherwise: */
            } else {
                /* Clear the stack back to a table body context. */
                $this->clearStackToTableContext($clear);

                /* Pop the current node from the stack of open elements. Switch
                the insertion mode to "in table". */
                array_pop($this->stack);
                $this->mode = self::IN_TABLE;
            }

            /* A start tag whose tag name is one of: "caption", "col", "colgroup",
            "tbody", "tfoot", "thead", or an end tag whose tag name is "table" */
        } elseif (($token['type'] === HTML5::STARTTAG && in_array(
                    $token['name'],
                    array('caption', 'col', 'colgroup', 'tbody', 'tfoor', 'thead')
                )) ||
            ($token['type'] === HTML5::STARTTAG && $token['name'] === 'table')
        ) {
            /* If the stack of open elements does not have a tbody, thead, or
            tfoot element in table scope, this is a parse error. Ignore the
            token. (innerHTML case) */
            if (!$this->elementInScope(array('tbody', 'thead', 'tfoot'), true)) {
                // Ignore.

                /* Otherwise: */
            } else {
                /* Clear the stack back to a table body context. */
                $this->clearStackToTableContext($clear);

                /* Act as if an end tag with the same tag name as the current
                node ("tbody", "tfoot", or "thead") had been seen, then
                reprocess the current token. */
                $this->inTableBody(
                    array(
                        'name' => end($this->stack)->nodeName,
                        'type' => HTML5::ENDTAG
                    )
                );

                return $this->mainPhase($token);
            }

            /* An end tag whose tag name is one of: "body", "caption", "col",
            "colgroup", "html", "td", "th", "tr" */
        } elseif ($token['type'] === HTML5::ENDTAG && in_array(
                $token['name'],
                array('body', 'caption', 'col', 'colgroup', 'html', 'td', 'th', 'tr')
            )
        ) {
            /* Parse error. Ignore the token. */

            /* Anything else */
        } else {
            /* Process the token as if the insertion mode was "in table". */
            $this->inTable($token);
        }
    }

    private function inRow($token)
    {
        $clear = array('tr', 'html');

        /* A start tag whose tag name is one of: "th", "td" */
        if ($token['type'] === HTML5::STARTTAG &&
            ($token['name'] === 'th' || $token['name'] === 'td')
        ) {
            /* Clear the stack back to a table row context. */
            $this->clearStackToTableContext($clear);

            /* Insert an HTML element for the token, then switch the insertion
            mode to "in cell". */
            $this->insertElement($token);
            $this->mode = self::IN_CELL;

            /* Insert a marker at the end of the list of active formatting
            elements. */
            $this->a_formatting[] = self::MARKER;

            /* An end tag whose tag name is "tr" */
        } elseif ($token['type'] === HTML5::ENDTAG && $token['name'] === 'tr') {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. (innerHTML case) */
            if (!$this->elementInScope($token['name'], true)) {
                // Ignore.

                /* Otherwise: */
            } else {
                /* Clear the stack back to a table row context. */
                $this->clearStackToTableContext($clear);

                /* Pop the current node (which will be a tr element) from the
                stack of open elements. Switch the insertion mode to "in table
                body". */
                array_pop($this->stack);
                $this->mode = self::IN_TBODY;
            }

            /* A start tag whose tag name is one of: "caption", "col", "colgroup",
            "tbody", "tfoot", "thead", "tr" or an end tag whose tag name is "table" */
        } elseif ($token['type'] === HTML5::STARTTAG && in_array(
                $token['name'],
                array('caption', 'col', 'colgroup', 'tbody', 'tfoot', 'thead', 'tr')
            )
        ) {
            /* Act as if an end tag with the tag name "tr" had been seen, then,
            if that token wasn't ignored, reprocess the current token. */
            $this->inRow(
                array(
                    'name' => 'tr',
                    'type' => HTML5::ENDTAG
                )
            );

            return $this->inCell($token);

            /* An end tag whose tag name is one of: "tbody", "tfoot", "thead" */
        } elseif ($token['type'] === HTML5::ENDTAG &&
            in_array($token['name'], array('tbody', 'tfoot', 'thead'))
        ) {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. */
            if (!$this->elementInScope($token['name'], true)) {
                // Ignore.

                /* Otherwise: */
            } else {
                /* Otherwise, act as if an end tag with the tag name "tr" had
                been seen, then reprocess the current token. */
                $this->inRow(
                    array(
                        'name' => 'tr',
                        'type' => HTML5::ENDTAG
                    )
                );

                return $this->inCell($token);
            }

            /* An end tag whose tag name is one of: "body", "caption", "col",
            "colgroup", "html", "td", "th" */
        } elseif ($token['type'] === HTML5::ENDTAG && in_array(
                $token['name'],
                array('body', 'caption', 'col', 'colgroup', 'html', 'td', 'th', 'tr')
            )
        ) {
            /* Parse error. Ignore the token. */

            /* Anything else */
        } else {
            /* Process the token as if the insertion mode was "in table". */
            $this->inTable($token);
        }
    }

    private function inCell($token)
    {
        /* An end tag whose tag name is one of: "td", "th" */
        if ($token['type'] === HTML5::ENDTAG &&
            ($token['name'] === 'td' || $token['name'] === 'th')
        ) {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as that of the token, then this is a
            parse error and the token must be ignored. */
            if (!$this->elementInScope($token['name'], true)) {
                // Ignore.

                /* Otherwise: */
            } else {
                /* Generate implied end tags, except for elements with the same
                tag name as the token. */
                $this->generateImpliedEndTags(array($token['name']));

                /* Now, if the current node is not an element with the same tag
                name as the token, then this is a parse error. */
                // k

                /* Pop elements from this stack until an element with the same
                tag name as the token has been popped from the stack. */
                while (true) {
                    $node = end($this->stack)->nodeName;
                    array_pop($this->stack);

                    if ($node === $token['name']) {
                        break;
                    }
                }

                /* Clear the list of active formatting elements up to the last
                marker. */
                $this->clearTheActiveFormattingElementsUpToTheLastMarker();

                /* Switch the insertion mode to "in row". (The current node
                will be a tr element at this point.) */
                $this->mode = self::IN_ROW;
            }

            /* A start tag whose tag name is one of: "caption", "col", "colgroup",
            "tbody", "td", "tfoot", "th", "thead", "tr" */
        } elseif ($token['type'] === HTML5::STARTTAG && in_array(
                $token['name'],
                array(
                    'caption',
                    'col',
                    'colgroup',
                    'tbody',
                    'td',
                    'tfoot',
                    'th',
                    'thead',
                    'tr'
                )
            )
        ) {
            /* If the stack of open elements does not have a td or th element
            in table scope, then this is a parse error; ignore the token.
            (innerHTML case) */
            if (!$this->elementInScope(array('td', 'th'), true)) {
                // Ignore.

                /* Otherwise, close the cell (see below) and reprocess the current
                token. */
            } else {
                $this->closeCell();
                return $this->inRow($token);
            }

            /* A start tag whose tag name is one of: "caption", "col", "colgroup",
            "tbody", "td", "tfoot", "th", "thead", "tr" */
        } elseif ($token['type'] === HTML5::STARTTAG && in_array(
                $token['name'],
                array(
                    'caption',
                    'col',
                    'colgroup',
                    'tbody',
                    'td',
                    'tfoot',
                    'th',
                    'thead',
                    'tr'
                )
            )
        ) {
            /* If the stack of open elements does not have a td or th element
            in table scope, then this is a parse error; ignore the token.
            (innerHTML case) */
            if (!$this->elementInScope(array('td', 'th'), true)) {
                // Ignore.

                /* Otherwise, close the cell (see below) and reprocess the current
                token. */
            } else {
                $this->closeCell();
                return $this->inRow($token);
            }

            /* An end tag whose tag name is one of: "body", "caption", "col",
            "colgroup", "html" */
        } elseif ($token['type'] === HTML5::ENDTAG && in_array(
                $token['name'],
                array('body', 'caption', 'col', 'colgroup', 'html')
            )
        ) {
            /* Parse error. Ignore the token. */

            /* An end tag whose tag name is one of: "table", "tbody", "tfoot",
            "thead", "tr" */
        } elseif ($token['type'] === HTML5::ENDTAG && in_array(
                $token['name'],
                array('table', 'tbody', 'tfoot', 'thead', 'tr')
            )
        ) {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as that of the token (which can only
            happen for "tbody", "tfoot" and "thead", or, in the innerHTML case),
            then this is a parse error and the token must be ignored. */
            if (!$this->elementInScope($token['name'], true)) {
                // Ignore.

                /* Otherwise, close the cell (see below) and reprocess the current
                token. */
            } else {
                $this->closeCell();
                return $this->inRow($token);
            }

            /* Anything else */
        } else {
            /* Process the token as if the insertion mode was "in body". */
            $this->inBody($token);
        }
    }

    private function clearTheActiveFormattingElementsUpToTheLastMarker()
    {
        /* When the steps below require the UA to clear the list of active
        formatting elements up to the last marker, the UA must perform the
        following steps: */

        while (true) {
            /* 1. Let entry be the last (most recently added) entry in the list
            of active formatting elements. */
            $entry = end($this->a_formatting);

            /* 2. Remove entry from the list of active formatting elements. */
            array_pop($this->a_formatting);

            /* 3. If entry was a marker, then stop the algorithm at this point.
            The list has been cleared up to the last marker. */
            if ($entry === self::MARKER) {
                break;
            }
        }
    }

    private function closeCell()
    {
        /* If the stack of open elements has a td or th element in table scope,
        then act as if an end tag token with that tag name had been seen. */
        foreach (array('td', 'th') as $cell) {
            if ($this->elementInScope($cell, true)) {
                $this->inCell(
                    array(
                        'name' => $cell,
                        'type' => HTML5::ENDTAG
                    )
                );

                break;
            }
        }
    }

    private function resetInsertionMode()
    {
        /* 1. Let last be false. */
        $last = false;
        $leng = count($this->stack);

        for ($n = $leng - 1; $n >= 0; $n--) {
            /* 2. Let node be the last node in the stack of open elements. */
            $node = $this->stack[$n];

            /* 3. If node is the first node in the stack of open elements, then
            set last to true. If the element whose innerHTML  attribute is being
            set is neither a td  element nor a th element, then set node to the
            element whose innerHTML  attribute is being set. (innerHTML  case) */
            if ($this->stack[0]->isSameNode($node)) {
                $last = true;
            }

            /* 4. If node is a select element, then switch the insertion mode to
            "in select" and abort these steps. (innerHTML case) */
            if ($node->nodeName === 'select') {
                $this->mode = self::IN_SELECT;
                break;

                /* 5. If node is a td or th element, then switch the insertion mode
                to "in cell" and abort these steps. */
            } elseif ($node->nodeName === 'td' || $node->nodeName === 'th') {
                $this->mode = self::IN_CELL;
                break;

                /* 6. If node is a tr element, then switch the insertion mode to
                "in    row" and abort these steps. */
            } elseif ($node->nodeName === 'tr') {
                $this->mode = self::IN_ROW;
                break;

                /* 7. If node is a tbody, thead, or tfoot element, then switch the
                insertion mode to "in table body" and abort these steps. */
            } elseif (in_array($node->nodeName, array('tbody', 'thead', 'tfoot'))) {
                $this->mode = self::IN_TBODY;
                break;

                /* 8. If node is a caption element, then switch the insertion mode
                to "in caption" and abort these steps. */
            } elseif ($node->nodeName === 'caption') {
                $this->mode = self::IN_CAPTION;
                break;

                /* 9. If node is a colgroup element, then switch the insertion mode
                to "in column group" and abort these steps. (innerHTML case) */
            } elseif ($node->nodeName === 'colgroup') {
                $this->mode = self::IN_CGROUP;
                break;

                /* 10. If node is a table element, then switch the insertion mode
                to "in table" and abort these steps. */
            } elseif ($node->nodeName === 'table') {
                $this->mode = self::IN_TABLE;
                break;

                /* 11. If node is a head element, then switch the insertion mode
                to "in body" ("in body"! not "in head"!) and abort these steps.
                (innerHTML case) */
            } elseif ($node->nodeName === 'head') {
                $this->mode = self::IN_BODY;
                break;

                /* 12. If node is a body element, then switch the insertion mode to
                "in body" and abort these steps. */
            } elseif ($node->nodeName === 'body') {
                $this->mode = self::IN_BODY;
                break;

                /* 13. If node is a frameset element, then switch the insertion
                mode to "in frameset" and abort these steps. (innerHTML case) */
            } elseif ($node->nodeName === 'frameset') {
                $this->mode = self::IN_FRAME;
                break;

                /* 14. If node is an html element, then: if the head element
                pointer is null, switch the insertion mode to "before head",
                otherwise, switch the insertion mode to "after head". In either
                case, abort these steps. (innerHTML case) */
            } elseif ($node->nodeName === 'html') {
                $this->mode = ($this->head_pointer === null)
                    ? self::BEFOR_HEAD
                    : self::AFTER_HEAD;

                break;

                /* 15. If last is true, then set the insertion mode to "in body"
                and    abort these steps. (innerHTML case) */
            } elseif ($last) {
                $this->mode = self::IN_BODY;
                break;
            }
        }
    }

    private function inCaption($token)
    {
        /* An end tag whose tag name is "caption" */
        if ($token['type'] === HTML5::ENDTAG && $token['name'] === 'caption') {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. (innerHTML case) */
            if (!$this->elementInScope($token['name'], true)) {
                // Ignore

                /* Otherwise: */
            } else {
                /* Generate implied end tags. */
                $this->generateImpliedEndTags();

                /* Now, if the current node is not a caption element, then this
                is a parse error. */
                // w/e

                /* Pop elements from this stack until a caption element has
                been popped from the stack. */
                while (true) {
                    $node = end($this->stack)->nodeName;
                    array_pop($this->stack);

                    if ($node === 'caption') {
                        break;
                    }
                }

                /* Clear the list of active formatting elements up to the last
                marker. */
                $this->clearTheActiveFormattingElementsUpToTheLastMarker();

                /* Switch the insertion mode to "in table". */
                $this->mode = self::IN_TABLE;
            }

            /* A start tag whose tag name is one of: "caption", "col", "colgroup",
            "tbody", "td", "tfoot", "th", "thead", "tr", or an end tag whose tag
            name is "table" */
        } elseif (($token['type'] === HTML5::STARTTAG && in_array(
                    $token['name'],
                    array(
                        'caption',
                        'col',
                        'colgroup',
                        'tbody',
                        'td',
                        'tfoot',
                        'th',
                        'thead',
                        'tr'
                    )
                )) || ($token['type'] === HTML5::ENDTAG &&
                $token['name'] === 'table')
        ) {
            /* Parse error. Act as if an end tag with the tag name "caption"
            had been seen, then, if that token wasn't ignored, reprocess the
            current token. */
            $this->inCaption(
                array(
                    'name' => 'caption',
                    'type' => HTML5::ENDTAG
                )
            );

            return $this->inTable($token);

            /* An end tag whose tag name is one of: "body", "col", "colgroup",
            "html", "tbody", "td", "tfoot", "th", "thead", "tr" */
        } elseif ($token['type'] === HTML5::ENDTAG && in_array(
                $token['name'],
                array(
                    'body',
                    'col',
                    'colgroup',
                    'html',
                    'tbody',
                    'tfoot',
                    'th',
                    'thead',
                    'tr'
                )
            )
        ) {
            // Parse error. Ignore the token.

            /* Anything else */
        } else {
            /* Process the token as if the insertion mode was "in body". */
            $this->inBody($token);
        }
    }

    private function inSelect($token)
    {
        /* Handle the token as follows: */

        /* A character token */
        if ($token['type'] === HTML5::CHARACTR) {
            /* Append the token's character to the current node. */
            $this->insertText($token['data']);

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the current node with the data
            attribute set to the data given in the comment token. */
            $this->insertComment($token['data']);

            /* A start tag token whose tag name is "option" */
        } elseif ($token['type'] === HTML5::STARTTAG &&
            $token['name'] === 'option'
        ) {
            /* If the current node is an option element, act as if an end tag
            with the tag name "option" had been seen. */
            if (end($this->stack)->nodeName === 'option') {
                $this->inSelect(
                    array(
                        'name' => 'option',
                        'type' => HTML5::ENDTAG
                    )
                );
            }

            /* Insert an HTML element for the token. */
            $this->insertElement($token);

            /* A start tag token whose tag name is "optgroup" */
        } elseif ($token['type'] === HTML5::STARTTAG &&
            $token['name'] === 'optgroup'
        ) {
            /* If the current node is an option element, act as if an end tag
            with the tag name "option" had been seen. */
            if (end($this->stack)->nodeName === 'option') {
                $this->inSelect(
                    array(
                        'name' => 'option',
                        'type' => HTML5::ENDTAG
                    )
                );
            }

            /* If the current node is an optgroup element, act as if an end tag
            with the tag name "optgroup" had been seen. */
            if (end($this->stack)->nodeName === 'optgroup') {
                $this->inSelect(
                    array(
                        'name' => 'optgroup',
                        'type' => HTML5::ENDTAG
                    )
                );
            }

            /* Insert an HTML element for the token. */
            $this->insertElement($token);

            /* An end tag token whose tag name is "optgroup" */
        } elseif ($token['type'] === HTML5::ENDTAG &&
            $token['name'] === 'optgroup'
        ) {
            /* First, if the current node is an option element, and the node
            immediately before it in the stack of open elements is an optgroup
            element, then act as if an end tag with the tag name "option" had
            been seen. */
            $elements_in_stack = count($this->stack);

            if ($this->stack[$elements_in_stack - 1]->nodeName === 'option' &&
                $this->stack[$elements_in_stack - 2]->nodeName === 'optgroup'
            ) {
                $this->inSelect(
                    array(
                        'name' => 'option',
                        'type' => HTML5::ENDTAG
                    )
                );
            }

            /* If the current node is an optgroup element, then pop that node
            from the stack of open elements. Otherwise, this is a parse error,
            ignore the token. */
            if ($this->stack[$elements_in_stack - 1] === 'optgroup') {
                array_pop($this->stack);
            }

            /* An end tag token whose tag name is "option" */
        } elseif ($token['type'] === HTML5::ENDTAG &&
            $token['name'] === 'option'
        ) {
            /* If the current node is an option element, then pop that node
            from the stack of open elements. Otherwise, this is a parse error,
            ignore the token. */
            if (end($this->stack)->nodeName === 'option') {
                array_pop($this->stack);
            }

            /* An end tag whose tag name is "select" */
        } elseif ($token['type'] === HTML5::ENDTAG &&
            $token['name'] === 'select'
        ) {
            /* If the stack of open elements does not have an element in table
            scope with the same tag name as the token, this is a parse error.
            Ignore the token. (innerHTML case) */
            if (!$this->elementInScope($token['name'], true)) {
                // w/e

                /* Otherwise: */
            } else {
                /* Pop elements from the stack of open elements until a select
                element has been popped from the stack. */
                while (true) {
                    $current = end($this->stack)->nodeName;
                    array_pop($this->stack);

                    if ($current === 'select') {
                        break;
                    }
                }

                /* Reset the insertion mode appropriately. */
                $this->resetInsertionMode();
            }

            /* A start tag whose tag name is "select" */
        } elseif ($token['name'] === 'select' &&
            $token['type'] === HTML5::STARTTAG
        ) {
            /* Parse error. Act as if the token had been an end tag with the
            tag name "select" instead. */
            $this->inSelect(
                array(
                    'name' => 'select',
                    'type' => HTML5::ENDTAG
                )
            );

            /* An end tag whose tag name is one of: "caption", "table", "tbody",
            "tfoot", "thead", "tr", "td", "th" */
        } elseif (in_array(
                $token['name'],
                array(
                    'caption',
                    'table',
                    'tbody',
                    'tfoot',
                    'thead',
                    'tr',
                    'td',
                    'th'
                )
            ) && $token['type'] === HTML5::ENDTAG
        ) {
            /* Parse error. */
            // w/e

            /* If the stack of open elements has an element in table scope with
            the same tag name as that of the token, then act as if an end tag
            with the tag name "select" had been seen, and reprocess the token.
            Otherwise, ignore the token. */
            if ($this->elementInScope($token['name'], true)) {
                $this->inSelect(
                    array(
                        'name' => 'select',
                        'type' => HTML5::ENDTAG
                    )
                );

                $this->mainPhase($token);
            }

            /* Anything else */
        } else {
            /* Parse error. Ignore the token. */
        }
    }

    private function inFrameset($token)
    {
        /* Handle the token as follows: */

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        U+000D CARRIAGE RETURN (CR), or U+0020 SPACE */
        if ($token['type'] === HTML5::CHARACTR &&
            preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])
        ) {
            /* Append the character to the current node. */
            $this->insertText($token['data']);

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the current node with the data
            attribute set to the data given in the comment token. */
            $this->insertComment($token['data']);

            /* A start tag with the tag name "frameset" */
        } elseif ($token['name'] === 'frameset' &&
            $token['type'] === HTML5::STARTTAG
        ) {
            $this->insertElement($token);

            /* An end tag with the tag name "frameset" */
        } elseif ($token['name'] === 'frameset' &&
            $token['type'] === HTML5::ENDTAG
        ) {
            /* If the current node is the root html element, then this is a
            parse error; ignore the token. (innerHTML case) */
            if (end($this->stack)->nodeName === 'html') {
                // Ignore

            } else {
                /* Otherwise, pop the current node from the stack of open
                elements. */
                array_pop($this->stack);

                /* If the parser was not originally created in order to handle
                the setting of an element's innerHTML attribute (innerHTML case),
                and the current node is no longer a frameset element, then change
                the insertion mode to "after frameset". */
                $this->mode = self::AFTR_FRAME;
            }

            /* A start tag with the tag name "frame" */
        } elseif ($token['name'] === 'frame' &&
            $token['type'] === HTML5::STARTTAG
        ) {
            /* Insert an HTML element for the token. */
            $this->insertElement($token);

            /* Immediately pop the current node off the stack of open elements. */
            array_pop($this->stack);

            /* A start tag with the tag name "noframes" */
        } elseif ($token['name'] === 'noframes' &&
            $token['type'] === HTML5::STARTTAG
        ) {
            /* Process the token as if the insertion mode had been "in body". */
            $this->inBody($token);

            /* Anything else */
        } else {
            /* Parse error. Ignore the token. */
        }
    }

    private function afterFrameset($token)
    {
        /* Handle the token as follows: */

        /* A character token that is one of one of U+0009 CHARACTER TABULATION,
        U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
        U+000D CARRIAGE RETURN (CR), or U+0020 SPACE */
        if ($token['type'] === HTML5::CHARACTR &&
            preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])
        ) {
            /* Append the character to the current node. */
            $this->insertText($token['data']);

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the current node with the data
            attribute set to the data given in the comment token. */
            $this->insertComment($token['data']);

            /* An end tag with the tag name "html" */
        } elseif ($token['name'] === 'html' &&
            $token['type'] === HTML5::ENDTAG
        ) {
            /* Switch to the trailing end phase. */
            $this->phase = self::END_PHASE;

            /* A start tag with the tag name "noframes" */
        } elseif ($token['name'] === 'noframes' &&
            $token['type'] === HTML5::STARTTAG
        ) {
            /* Process the token as if the insertion mode had been "in body". */
            $this->inBody($token);

            /* Anything else */
        } else {
            /* Parse error. Ignore the token. */
        }
    }

    private function trailingEndPhase($token)
    {
        /* After the main phase, as each token is emitted from the tokenisation
        stage, it must be processed as described in this section. */

        /* A DOCTYPE token */
        if ($token['type'] === HTML5::DOCTYPE) {
            // Parse error. Ignore the token.

            /* A comment token */
        } elseif ($token['type'] === HTML5::COMMENT) {
            /* Append a Comment node to the Document object with the data
            attribute set to the data given in the comment token. */
            $comment = $this->dom->createComment($token['data']);
            $this->dom->appendChild($comment);

            /* A character token that is one of one of U+0009 CHARACTER TABULATION,
            U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
            or U+0020 SPACE */
        } elseif ($token['type'] === HTML5::CHARACTR &&
            preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])
        ) {
            /* Process the token as it would be processed in the main phase. */
            $this->mainPhase($token);

            /* A character token that is not one of U+0009 CHARACTER TABULATION,
            U+000A LINE FEED (LF), U+000B LINE TABULATION, U+000C FORM FEED (FF),
            or U+0020 SPACE. Or a start tag token. Or an end tag token. */
        } elseif (($token['type'] === HTML5::CHARACTR &&
                preg_match('/^[\t\n\x0b\x0c ]+$/', $token['data'])) ||
            $token['type'] === HTML5::STARTTAG || $token['type'] === HTML5::ENDTAG
        ) {
            /* Parse error. Switch back to the main phase and reprocess the
            token. */
            $this->phase = self::MAIN_PHASE;
            return $this->mainPhase($token);

            /* An end-of-file token */
        } elseif ($token['type'] === HTML5::EOF) {
            /* OMG DONE!! */
        }
    }

    public function save()
    {
        return $this->dom;
    }
}
