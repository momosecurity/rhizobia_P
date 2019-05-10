<?php

namespace Security\HTMLPurifier\HTMLPurifier\Lexer;
/*

Copyright 2007 Jeroen van der Meer <http://jero.net/>

Permission is hereby granted, free of charge, to any person obtaining a
copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/

class HTML5
{
    const PCDATA = 0;
    const RCDATA = 1;
    const CDATA = 2;
    const PLAINTEXT = 3;
    const DOCTYPE = 0;
    const STARTTAG = 1;
    const ENDTAG = 2;
    const COMMENT = 3;
    const CHARACTR = 4;
    const EOF = 5;
    private $data;
    private $char;
    private $EOF;
    private $state;
    private $tree;
    private $token;
    private $content_model;
    private $escape = false;
    private $entities = array(
        'AElig;',
        'AElig',
        'AMP;',
        'AMP',
        'Aacute;',
        'Aacute',
        'Acirc;',
        'Acirc',
        'Agrave;',
        'Agrave',
        'Alpha;',
        'Aring;',
        'Aring',
        'Atilde;',
        'Atilde',
        'Auml;',
        'Auml',
        'Beta;',
        'COPY;',
        'COPY',
        'Ccedil;',
        'Ccedil',
        'Chi;',
        'Dagger;',
        'Delta;',
        'ETH;',
        'ETH',
        'Eacute;',
        'Eacute',
        'Ecirc;',
        'Ecirc',
        'Egrave;',
        'Egrave',
        'Epsilon;',
        'Eta;',
        'Euml;',
        'Euml',
        'GT;',
        'GT',
        'Gamma;',
        'Iacute;',
        'Iacute',
        'Icirc;',
        'Icirc',
        'Igrave;',
        'Igrave',
        'Iota;',
        'Iuml;',
        'Iuml',
        'Kappa;',
        'LT;',
        'LT',
        'Lambda;',
        'Mu;',
        'Ntilde;',
        'Ntilde',
        'Nu;',
        'OElig;',
        'Oacute;',
        'Oacute',
        'Ocirc;',
        'Ocirc',
        'Ograve;',
        'Ograve',
        'Omega;',
        'Omicron;',
        'Oslash;',
        'Oslash',
        'Otilde;',
        'Otilde',
        'Ouml;',
        'Ouml',
        'Phi;',
        'Pi;',
        'Prime;',
        'Psi;',
        'QUOT;',
        'QUOT',
        'REG;',
        'REG',
        'Rho;',
        'Scaron;',
        'Sigma;',
        'THORN;',
        'THORN',
        'TRADE;',
        'Tau;',
        'Theta;',
        'Uacute;',
        'Uacute',
        'Ucirc;',
        'Ucirc',
        'Ugrave;',
        'Ugrave',
        'Upsilon;',
        'Uuml;',
        'Uuml',
        'Xi;',
        'Yacute;',
        'Yacute',
        'Yuml;',
        'Zeta;',
        'aacute;',
        'aacute',
        'acirc;',
        'acirc',
        'acute;',
        'acute',
        'aelig;',
        'aelig',
        'agrave;',
        'agrave',
        'alefsym;',
        'alpha;',
        'amp;',
        'amp',
        'and;',
        'ang;',
        'apos;',
        'aring;',
        'aring',
        'asymp;',
        'atilde;',
        'atilde',
        'auml;',
        'auml',
        'bdquo;',
        'beta;',
        'brvbar;',
        'brvbar',
        'bull;',
        'cap;',
        'ccedil;',
        'ccedil',
        'cedil;',
        'cedil',
        'cent;',
        'cent',
        'chi;',
        'circ;',
        'clubs;',
        'cong;',
        'copy;',
        'copy',
        'crarr;',
        'cup;',
        'curren;',
        'curren',
        'dArr;',
        'dagger;',
        'darr;',
        'deg;',
        'deg',
        'delta;',
        'diams;',
        'divide;',
        'divide',
        'eacute;',
        'eacute',
        'ecirc;',
        'ecirc',
        'egrave;',
        'egrave',
        'empty;',
        'emsp;',
        'ensp;',
        'epsilon;',
        'equiv;',
        'eta;',
        'eth;',
        'eth',
        'euml;',
        'euml',
        'euro;',
        'exist;',
        'fnof;',
        'forall;',
        'frac12;',
        'frac12',
        'frac14;',
        'frac14',
        'frac34;',
        'frac34',
        'frasl;',
        'gamma;',
        'ge;',
        'gt;',
        'gt',
        'hArr;',
        'harr;',
        'hearts;',
        'hellip;',
        'iacute;',
        'iacute',
        'icirc;',
        'icirc',
        'iexcl;',
        'iexcl',
        'igrave;',
        'igrave',
        'image;',
        'infin;',
        'int;',
        'iota;',
        'iquest;',
        'iquest',
        'isin;',
        'iuml;',
        'iuml',
        'kappa;',
        'lArr;',
        'lambda;',
        'lang;',
        'laquo;',
        'laquo',
        'larr;',
        'lceil;',
        'ldquo;',
        'le;',
        'lfloor;',
        'lowast;',
        'loz;',
        'lrm;',
        'lsaquo;',
        'lsquo;',
        'lt;',
        'lt',
        'macr;',
        'macr',
        'mdash;',
        'micro;',
        'micro',
        'middot;',
        'middot',
        'minus;',
        'mu;',
        'nabla;',
        'nbsp;',
        'nbsp',
        'ndash;',
        'ne;',
        'ni;',
        'not;',
        'not',
        'notin;',
        'nsub;',
        'ntilde;',
        'ntilde',
        'nu;',
        'oacute;',
        'oacute',
        'ocirc;',
        'ocirc',
        'oelig;',
        'ograve;',
        'ograve',
        'oline;',
        'omega;',
        'omicron;',
        'oplus;',
        'or;',
        'ordf;',
        'ordf',
        'ordm;',
        'ordm',
        'oslash;',
        'oslash',
        'otilde;',
        'otilde',
        'otimes;',
        'ouml;',
        'ouml',
        'para;',
        'para',
        'part;',
        'permil;',
        'perp;',
        'phi;',
        'pi;',
        'piv;',
        'plusmn;',
        'plusmn',
        'pound;',
        'pound',
        'prime;',
        'prod;',
        'prop;',
        'psi;',
        'quot;',
        'quot',
        'rArr;',
        'radic;',
        'rang;',
        'raquo;',
        'raquo',
        'rarr;',
        'rceil;',
        'rdquo;',
        'real;',
        'reg;',
        'reg',
        'rfloor;',
        'rho;',
        'rlm;',
        'rsaquo;',
        'rsquo;',
        'sbquo;',
        'scaron;',
        'sdot;',
        'sect;',
        'sect',
        'shy;',
        'shy',
        'sigma;',
        'sigmaf;',
        'sim;',
        'spades;',
        'sub;',
        'sube;',
        'sum;',
        'sup1;',
        'sup1',
        'sup2;',
        'sup2',
        'sup3;',
        'sup3',
        'sup;',
        'supe;',
        'szlig;',
        'szlig',
        'tau;',
        'there4;',
        'theta;',
        'thetasym;',
        'thinsp;',
        'thorn;',
        'thorn',
        'tilde;',
        'times;',
        'times',
        'trade;',
        'uArr;',
        'uacute;',
        'uacute',
        'uarr;',
        'ucirc;',
        'ucirc',
        'ugrave;',
        'ugrave',
        'uml;',
        'uml',
        'upsih;',
        'upsilon;',
        'uuml;',
        'uuml',
        'weierp;',
        'xi;',
        'yacute;',
        'yacute',
        'yen;',
        'yen',
        'yuml;',
        'yuml',
        'zeta;',
        'zwj;',
        'zwnj;'
    );

    public function __construct($data)
    {
        $this->data = $data;
        $this->char = -1;
        $this->EOF = strlen($data);
        $this->tree = new HTML5TreeConstructer;
        $this->content_model = self::PCDATA;

        $this->state = 'data';

        while ($this->state !== null) {
            $this->{$this->state . 'State'}();
        }
    }

    public function save()
    {
        return $this->tree->save();
    }

    private function dataState()
    {
        // Consume the next input character
        $this->char++;
        $char = $this->char();

        if ($char === '&' && ($this->content_model === self::PCDATA || $this->content_model === self::RCDATA)) {
            /* U+0026 AMPERSAND (&)
            When the content model flag is set to one of the PCDATA or RCDATA
            states: switch to the entity data state. Otherwise: treat it as per
            the "anything else"    entry below. */
            $this->state = 'entityData';

        } elseif ($char === '-') {
            /* If the content model flag is set to either the RCDATA state or
            the CDATA state, and the escape flag is false, and there are at
            least three characters before this one in the input stream, and the
            last four characters in the input stream, including this one, are
            U+003C LESS-THAN SIGN, U+0021 EXCLAMATION MARK, U+002D HYPHEN-MINUS,
            and U+002D HYPHEN-MINUS ("<!--"), then set the escape flag to true. */
            if (($this->content_model === self::RCDATA || $this->content_model ===
                    self::CDATA) && $this->escape === false &&
                $this->char >= 3 && $this->character($this->char - 4, 4) === '<!--'
            ) {
                $this->escape = true;
            }

            /* In any case, emit the input character as a character token. Stay
            in the data state. */
            $this->emitToken(
                array(
                    'type' => self::CHARACTR,
                    'data' => $char
                )
            );

            /* U+003C LESS-THAN SIGN (<) */
        } elseif ($char === '<' && ($this->content_model === self::PCDATA ||
                (($this->content_model === self::RCDATA ||
                        $this->content_model === self::CDATA) && $this->escape === false))
        ) {
            /* When the content model flag is set to the PCDATA state: switch
            to the tag open state.

            When the content model flag is set to either the RCDATA state or
            the CDATA state and the escape flag is false: switch to the tag
            open state.

            Otherwise: treat it as per the "anything else" entry below. */
            $this->state = 'tagOpen';

            /* U+003E GREATER-THAN SIGN (>) */
        } elseif ($char === '>') {
            /* If the content model flag is set to either the RCDATA state or
            the CDATA state, and the escape flag is true, and the last three
            characters in the input stream including this one are U+002D
            HYPHEN-MINUS, U+002D HYPHEN-MINUS, U+003E GREATER-THAN SIGN ("-->"),
            set the escape flag to false. */
            if (($this->content_model === self::RCDATA ||
                    $this->content_model === self::CDATA) && $this->escape === true &&
                $this->character($this->char, 3) === '-->'
            ) {
                $this->escape = false;
            }

            /* In any case, emit the input character as a character token.
            Stay in the data state. */
            $this->emitToken(
                array(
                    'type' => self::CHARACTR,
                    'data' => $char
                )
            );

        } elseif ($this->char === $this->EOF) {
            /* EOF
            Emit an end-of-file token. */
            $this->EOF();

        } elseif ($this->content_model === self::PLAINTEXT) {
            /* When the content model flag is set to the PLAINTEXT state
            THIS DIFFERS GREATLY FROM THE SPEC: Get the remaining characters of
            the text and emit it as a character token. */
            $this->emitToken(
                array(
                    'type' => self::CHARACTR,
                    'data' => substr($this->data, $this->char)
                )
            );

            $this->EOF();

        } else {
            /* Anything else
            THIS DIFFERS GREATLY FROM THE SPEC: Get as many character that
            otherwise would also be treated as a character token and emit it
            as a single character token. Stay in the data state. */
            $len = strcspn($this->data, '<&', $this->char);
            $char = substr($this->data, $this->char, $len);
            $this->char += $len - 1;

            $this->emitToken(
                array(
                    'type' => self::CHARACTR,
                    'data' => $char
                )
            );

            $this->state = 'data';
        }
    }

    private function char()
    {
        return ($this->char < $this->EOF)
            ? $this->data[$this->char]
            : false;
    }

    private function character($s, $l = 0)
    {
        if ($s + $l < $this->EOF) {
            if ($l === 0) {
                return $this->data[$s];
            } else {
                return substr($this->data, $s, $l);
            }
        }
    }

    private function emitToken($token)
    {
        $emit = $this->tree->emitToken($token);

        if (is_int($emit)) {
            $this->content_model = $emit;

        } elseif ($token['type'] === self::ENDTAG) {
            $this->content_model = self::PCDATA;
        }
    }

    private function EOF()
    {
        $this->state = null;
        $this->tree->emitToken(
            array(
                'type' => self::EOF
            )
        );
    }

    private function entityDataState()
    {
        // Attempt to consume an entity.
        $entity = $this->entity();

        // If nothing is returned, emit a U+0026 AMPERSAND character token.
        // Otherwise, emit the character token that was returned.
        $char = (!$entity) ? '&' : $entity;
        $this->emitToken(
            array(
                'type' => self::CHARACTR,
                'data' => $char
            )
        );

        // Finally, switch to the data state.
        $this->state = 'data';
    }

    private function entity()
    {
        $start = $this->char;

        // This section defines how to consume an entity. This definition is
        // used when parsing entities in text and in attributes.

        // The behaviour depends on the identity of the next character (the
        // one immediately after the U+0026 AMPERSAND character):

        switch ($this->character($this->char + 1)) {
            // U+0023 NUMBER SIGN (#)
            case '#':

                // The behaviour further depends on the character after the
                // U+0023 NUMBER SIGN:
                switch ($this->character($this->char + 1)) {
                    // U+0078 LATIN SMALL LETTER X
                    // U+0058 LATIN CAPITAL LETTER X
                    case 'x':
                    case 'X':
                        // Follow the steps below, but using the range of
                        // characters U+0030 DIGIT ZERO through to U+0039 DIGIT
                        // NINE, U+0061 LATIN SMALL LETTER A through to U+0066
                        // LATIN SMALL LETTER F, and U+0041 LATIN CAPITAL LETTER
                        // A, through to U+0046 LATIN CAPITAL LETTER F (in other
                        // words, 0-9, A-F, a-f).
                        $char = 1;
                        $char_class = '0-9A-Fa-f';
                        break;

                    // Anything else
                    default:
                        // Follow the steps below, but using the range of
                        // characters U+0030 DIGIT ZERO through to U+0039 DIGIT
                        // NINE (i.e. just 0-9).
                        $char = 0;
                        $char_class = '0-9';
                        break;
                }

                // Consume as many characters as match the range of characters
                // given above.
                $this->char++;
                $e_name = $this->characters($char_class, $this->char + $char + 1);
                $entity = $this->character($start, $this->char);
                $cond = strlen($e_name) > 0;

                // The rest of the parsing happens below.
                break;

            // Anything else
            default:
                // Consume the maximum number of characters possible, with the
                // consumed characters case-sensitively matching one of the
                // identifiers in the first column of the entities table.

                $e_name = $this->characters('0-9A-Za-z;', $this->char + 1);
                $len = strlen($e_name);

                for ($c = 1; $c <= $len; $c++) {
                    $id = substr($e_name, 0, $c);
                    $this->char++;

                    if (in_array($id, $this->entities)) {
                        if ($e_name[$c - 1] !== ';') {
                            if ($c < $len && $e_name[$c] == ';') {
                                $this->char++; // consume extra semicolon
                            }
                        }
                        $entity = $id;
                        break;
                    }
                }

                $cond = isset($entity);
                // The rest of the parsing happens below.
                break;
        }

        if (!$cond) {
            // If no match can be made, then this is a parse error. No
            // characters are consumed, and nothing is returned.
            $this->char = $start;
            return false;
        }

        // Return a character token for the character corresponding to the
        // entity name (as given by the second column of the entities table).
        return html_entity_decode('&' . rtrim($entity, ';') . ';', ENT_QUOTES, 'UTF-8');
    }

    private function characters($char_class, $start)
    {
        return preg_replace('#^([' . $char_class . ']+).*#s', '\\1', substr($this->data, $start));
    }

    private function tagOpenState()
    {
        switch ($this->content_model) {
            case self::RCDATA:
            case self::CDATA:
                /* If the next input character is a U+002F SOLIDUS (/) character,
                consume it and switch to the close tag open state. If the next
                input character is not a U+002F SOLIDUS (/) character, emit a
                U+003C LESS-THAN SIGN character token and switch to the data
                state to process the next input character. */
                if ($this->character($this->char + 1) === '/') {
                    $this->char++;
                    $this->state = 'closeTagOpen';

                } else {
                    $this->emitToken(
                        array(
                            'type' => self::CHARACTR,
                            'data' => '<'
                        )
                    );

                    $this->state = 'data';
                }
                break;

            case self::PCDATA:
                // If the content model flag is set to the PCDATA state
                // Consume the next input character:
                $this->char++;
                $char = $this->char();

                if ($char === '!') {
                    /* U+0021 EXCLAMATION MARK (!)
                    Switch to the markup declaration open state. */
                    $this->state = 'markupDeclarationOpen';

                } elseif ($char === '/') {
                    /* U+002F SOLIDUS (/)
                    Switch to the close tag open state. */
                    $this->state = 'closeTagOpen';

                } elseif (preg_match('/^[A-Za-z]$/', $char)) {
                    /* U+0041 LATIN LETTER A through to U+005A LATIN LETTER Z
                    Create a new start tag token, set its tag name to the lowercase
                    version of the input character (add 0x0020 to the character's code
                    point), then switch to the tag name state. (Don't emit the token
                    yet; further details will be filled in before it is emitted.) */
                    $this->token = array(
                        'name' => strtolower($char),
                        'type' => self::STARTTAG,
                        'attr' => array()
                    );

                    $this->state = 'tagName';

                } elseif ($char === '>') {
                    /* U+003E GREATER-THAN SIGN (>)
                    Parse error. Emit a U+003C LESS-THAN SIGN character token and a
                    U+003E GREATER-THAN SIGN character token. Switch to the data state. */
                    $this->emitToken(
                        array(
                            'type' => self::CHARACTR,
                            'data' => '<>'
                        )
                    );

                    $this->state = 'data';

                } elseif ($char === '?') {
                    /* U+003F QUESTION MARK (?)
                    Parse error. Switch to the bogus comment state. */
                    $this->state = 'bogusComment';

                } else {
                    /* Anything else
                    Parse error. Emit a U+003C LESS-THAN SIGN character token and
                    reconsume the current input character in the data state. */
                    $this->emitToken(
                        array(
                            'type' => self::CHARACTR,
                            'data' => '<'
                        )
                    );

                    $this->char--;
                    $this->state = 'data';
                }
                break;
        }
    }

    private function closeTagOpenState()
    {
        $next_node = strtolower($this->characters('A-Za-z', $this->char + 1));
        $the_same = count($this->tree->stack) > 0 && $next_node === end($this->tree->stack)->nodeName;

        if (($this->content_model === self::RCDATA || $this->content_model === self::CDATA) &&
            (!$the_same || ($the_same && (!preg_match(
                            '/[\t\n\x0b\x0c >\/]/',
                            $this->character($this->char + 1 + strlen($next_node))
                        ) || $this->EOF === $this->char)))
        ) {
            /* If the content model flag is set to the RCDATA or CDATA states then
            examine the next few characters. If they do not match the tag name of
            the last start tag token emitted (case insensitively), or if they do but
            they are not immediately followed by one of the following characters:
                * U+0009 CHARACTER TABULATION
                * U+000A LINE FEED (LF)
                * U+000B LINE TABULATION
                * U+000C FORM FEED (FF)
                * U+0020 SPACE
                * U+003E GREATER-THAN SIGN (>)
                * U+002F SOLIDUS (/)
                * EOF
            ...then there is a parse error. Emit a U+003C LESS-THAN SIGN character
            token, a U+002F SOLIDUS character token, and switch to the data state
            to process the next input character. */
            $this->emitToken(
                array(
                    'type' => self::CHARACTR,
                    'data' => '</'
                )
            );

            $this->state = 'data';

        } else {
            /* Otherwise, if the content model flag is set to the PCDATA state,
            or if the next few characters do match that tag name, consume the
            next input character: */
            $this->char++;
            $char = $this->char();

            if (preg_match('/^[A-Za-z]$/', $char)) {
                /* U+0041 LATIN LETTER A through to U+005A LATIN LETTER Z
                Create a new end tag token, set its tag name to the lowercase version
                of the input character (add 0x0020 to the character's code point), then
                switch to the tag name state. (Don't emit the token yet; further details
                will be filled in before it is emitted.) */
                $this->token = array(
                    'name' => strtolower($char),
                    'type' => self::ENDTAG
                );

                $this->state = 'tagName';

            } elseif ($char === '>') {
                /* U+003E GREATER-THAN SIGN (>)
                Parse error. Switch to the data state. */
                $this->state = 'data';

            } elseif ($this->char === $this->EOF) {
                /* EOF
                Parse error. Emit a U+003C LESS-THAN SIGN character token and a U+002F
                SOLIDUS character token. Reconsume the EOF character in the data state. */
                $this->emitToken(
                    array(
                        'type' => self::CHARACTR,
                        'data' => '</'
                    )
                );

                $this->char--;
                $this->state = 'data';

            } else {
                /* Parse error. Switch to the bogus comment state. */
                $this->state = 'bogusComment';
            }
        }
    }

    private function tagNameState()
    {
        // Consume the next input character:
        $this->char++;
        $char = $this->character($this->char);

        if (preg_match('/^[\t\n\x0b\x0c ]$/', $char)) {
            /* U+0009 CHARACTER TABULATION
            U+000A LINE FEED (LF)
            U+000B LINE TABULATION
            U+000C FORM FEED (FF)
            U+0020 SPACE
            Switch to the before attribute name state. */
            $this->state = 'beforeAttributeName';

        } elseif ($char === '>') {
            /* U+003E GREATER-THAN SIGN (>)
            Emit the current tag token. Switch to the data state. */
            $this->emitToken($this->token);
            $this->state = 'data';

        } elseif ($this->char === $this->EOF) {
            /* EOF
            Parse error. Emit the current tag token. Reconsume the EOF
            character in the data state. */
            $this->emitToken($this->token);

            $this->char--;
            $this->state = 'data';

        } elseif ($char === '/') {
            /* U+002F SOLIDUS (/)
            Parse error unless this is a permitted slash. Switch to the before
            attribute name state. */
            $this->state = 'beforeAttributeName';

        } else {
            /* Anything else
            Append the current input character to the current tag token's tag name.
            Stay in the tag name state. */
            $this->token['name'] .= strtolower($char);
            $this->state = 'tagName';
        }
    }

    private function beforeAttributeNameState()
    {
        // Consume the next input character:
        $this->char++;
        $char = $this->character($this->char);

        if (preg_match('/^[\t\n\x0b\x0c ]$/', $char)) {
            /* U+0009 CHARACTER TABULATION
            U+000A LINE FEED (LF)
            U+000B LINE TABULATION
            U+000C FORM FEED (FF)
            U+0020 SPACE
            Stay in the before attribute name state. */
            $this->state = 'beforeAttributeName';

        } elseif ($char === '>') {
            /* U+003E GREATER-THAN SIGN (>)
            Emit the current tag token. Switch to the data state. */
            $this->emitToken($this->token);
            $this->state = 'data';

        } elseif ($char === '/') {
            /* U+002F SOLIDUS (/)
            Parse error unless this is a permitted slash. Stay in the before
            attribute name state. */
            $this->state = 'beforeAttributeName';

        } elseif ($this->char === $this->EOF) {
            /* EOF
            Parse error. Emit the current tag token. Reconsume the EOF
            character in the data state. */
            $this->emitToken($this->token);

            $this->char--;
            $this->state = 'data';

        } else {
            /* Anything else
            Start a new attribute in the current tag token. Set that attribute's
            name to the current input character, and its value to the empty string.
            Switch to the attribute name state. */
            $this->token['attr'][] = array(
                'name' => strtolower($char),
                'value' => null
            );

            $this->state = 'attributeName';
        }
    }

    private function attributeNameState()
    {
        // Consume the next input character:
        $this->char++;
        $char = $this->character($this->char);

        if (preg_match('/^[\t\n\x0b\x0c ]$/', $char)) {
            /* U+0009 CHARACTER TABULATION
            U+000A LINE FEED (LF)
            U+000B LINE TABULATION
            U+000C FORM FEED (FF)
            U+0020 SPACE
            Stay in the before attribute name state. */
            $this->state = 'afterAttributeName';

        } elseif ($char === '=') {
            /* U+003D EQUALS SIGN (=)
            Switch to the before attribute value state. */
            $this->state = 'beforeAttributeValue';

        } elseif ($char === '>') {
            /* U+003E GREATER-THAN SIGN (>)
            Emit the current tag token. Switch to the data state. */
            $this->emitToken($this->token);
            $this->state = 'data';

        } elseif ($char === '/' && $this->character($this->char + 1) !== '>') {
            /* U+002F SOLIDUS (/)
            Parse error unless this is a permitted slash. Switch to the before
            attribute name state. */
            $this->state = 'beforeAttributeName';

        } elseif ($this->char === $this->EOF) {
            /* EOF
            Parse error. Emit the current tag token. Reconsume the EOF
            character in the data state. */
            $this->emitToken($this->token);

            $this->char--;
            $this->state = 'data';

        } else {
            /* Anything else
            Append the current input character to the current attribute's name.
            Stay in the attribute name state. */
            $last = count($this->token['attr']) - 1;
            $this->token['attr'][$last]['name'] .= strtolower($char);

            $this->state = 'attributeName';
        }
    }

    private function afterAttributeNameState()
    {
        // Consume the next input character:
        $this->char++;
        $char = $this->character($this->char);

        if (preg_match('/^[\t\n\x0b\x0c ]$/', $char)) {
            /* U+0009 CHARACTER TABULATION
            U+000A LINE FEED (LF)
            U+000B LINE TABULATION
            U+000C FORM FEED (FF)
            U+0020 SPACE
            Stay in the after attribute name state. */
            $this->state = 'afterAttributeName';

        } elseif ($char === '=') {
            /* U+003D EQUALS SIGN (=)
            Switch to the before attribute value state. */
            $this->state = 'beforeAttributeValue';

        } elseif ($char === '>') {
            /* U+003E GREATER-THAN SIGN (>)
            Emit the current tag token. Switch to the data state. */
            $this->emitToken($this->token);
            $this->state = 'data';

        } elseif ($char === '/' && $this->character($this->char + 1) !== '>') {
            /* U+002F SOLIDUS (/)
            Parse error unless this is a permitted slash. Switch to the
            before attribute name state. */
            $this->state = 'beforeAttributeName';

        } elseif ($this->char === $this->EOF) {
            /* EOF
            Parse error. Emit the current tag token. Reconsume the EOF
            character in the data state. */
            $this->emitToken($this->token);

            $this->char--;
            $this->state = 'data';

        } else {
            /* Anything else
            Start a new attribute in the current tag token. Set that attribute's
            name to the current input character, and its value to the empty string.
            Switch to the attribute name state. */
            $this->token['attr'][] = array(
                'name' => strtolower($char),
                'value' => null
            );

            $this->state = 'attributeName';
        }
    }

    private function beforeAttributeValueState()
    {
        // Consume the next input character:
        $this->char++;
        $char = $this->character($this->char);

        if (preg_match('/^[\t\n\x0b\x0c ]$/', $char)) {
            /* U+0009 CHARACTER TABULATION
            U+000A LINE FEED (LF)
            U+000B LINE TABULATION
            U+000C FORM FEED (FF)
            U+0020 SPACE
            Stay in the before attribute value state. */
            $this->state = 'beforeAttributeValue';

        } elseif ($char === '"') {
            /* U+0022 QUOTATION MARK (")
            Switch to the attribute value (double-quoted) state. */
            $this->state = 'attributeValueDoubleQuoted';

        } elseif ($char === '&') {
            /* U+0026 AMPERSAND (&)
            Switch to the attribute value (unquoted) state and reconsume
            this input character. */
            $this->char--;
            $this->state = 'attributeValueUnquoted';

        } elseif ($char === '\'') {
            /* U+0027 APOSTROPHE (')
            Switch to the attribute value (single-quoted) state. */
            $this->state = 'attributeValueSingleQuoted';

        } elseif ($char === '>') {
            /* U+003E GREATER-THAN SIGN (>)
            Emit the current tag token. Switch to the data state. */
            $this->emitToken($this->token);
            $this->state = 'data';

        } else {
            /* Anything else
            Append the current input character to the current attribute's value.
            Switch to the attribute value (unquoted) state. */
            $last = count($this->token['attr']) - 1;
            $this->token['attr'][$last]['value'] .= $char;

            $this->state = 'attributeValueUnquoted';
        }
    }

    private function attributeValueDoubleQuotedState()
    {
        // Consume the next input character:
        $this->char++;
        $char = $this->character($this->char);

        if ($char === '"') {
            /* U+0022 QUOTATION MARK (")
            Switch to the before attribute name state. */
            $this->state = 'beforeAttributeName';

        } elseif ($char === '&') {
            /* U+0026 AMPERSAND (&)
            Switch to the entity in attribute value state. */
            $this->entityInAttributeValueState('double');

        } elseif ($this->char === $this->EOF) {
            /* EOF
            Parse error. Emit the current tag token. Reconsume the character
            in the data state. */
            $this->emitToken($this->token);

            $this->char--;
            $this->state = 'data';

        } else {
            /* Anything else
            Append the current input character to the current attribute's value.
            Stay in the attribute value (double-quoted) state. */
            $last = count($this->token['attr']) - 1;
            $this->token['attr'][$last]['value'] .= $char;

            $this->state = 'attributeValueDoubleQuoted';
        }
    }

    private function entityInAttributeValueState()
    {
        // Attempt to consume an entity.
        $entity = $this->entity();

        // If nothing is returned, append a U+0026 AMPERSAND character to the
        // current attribute's value. Otherwise, emit the character token that
        // was returned.
        $char = (!$entity)
            ? '&'
            : $entity;

        $last = count($this->token['attr']) - 1;
        $this->token['attr'][$last]['value'] .= $char;
    }

    private function attributeValueSingleQuotedState()
    {
        // Consume the next input character:
        $this->char++;
        $char = $this->character($this->char);

        if ($char === '\'') {
            /* U+0022 QUOTATION MARK (')
            Switch to the before attribute name state. */
            $this->state = 'beforeAttributeName';

        } elseif ($char === '&') {
            /* U+0026 AMPERSAND (&)
            Switch to the entity in attribute value state. */
            $this->entityInAttributeValueState('single');

        } elseif ($this->char === $this->EOF) {
            /* EOF
            Parse error. Emit the current tag token. Reconsume the character
            in the data state. */
            $this->emitToken($this->token);

            $this->char--;
            $this->state = 'data';

        } else {
            /* Anything else
            Append the current input character to the current attribute's value.
            Stay in the attribute value (single-quoted) state. */
            $last = count($this->token['attr']) - 1;
            $this->token['attr'][$last]['value'] .= $char;

            $this->state = 'attributeValueSingleQuoted';
        }
    }

    private function attributeValueUnquotedState()
    {
        // Consume the next input character:
        $this->char++;
        $char = $this->character($this->char);

        if (preg_match('/^[\t\n\x0b\x0c ]$/', $char)) {
            /* U+0009 CHARACTER TABULATION
            U+000A LINE FEED (LF)
            U+000B LINE TABULATION
            U+000C FORM FEED (FF)
            U+0020 SPACE
            Switch to the before attribute name state. */
            $this->state = 'beforeAttributeName';

        } elseif ($char === '&') {
            /* U+0026 AMPERSAND (&)
            Switch to the entity in attribute value state. */
            $this->entityInAttributeValueState();

        } elseif ($char === '>') {
            /* U+003E GREATER-THAN SIGN (>)
            Emit the current tag token. Switch to the data state. */
            $this->emitToken($this->token);
            $this->state = 'data';

        } else {
            /* Anything else
            Append the current input character to the current attribute's value.
            Stay in the attribute value (unquoted) state. */
            $last = count($this->token['attr']) - 1;
            $this->token['attr'][$last]['value'] .= $char;

            $this->state = 'attributeValueUnquoted';
        }
    }

    private function bogusCommentState()
    {
        /* Consume every character up to the first U+003E GREATER-THAN SIGN
        character (>) or the end of the file (EOF), whichever comes first. Emit
        a comment token whose data is the concatenation of all the characters
        starting from and including the character that caused the state machine
        to switch into the bogus comment state, up to and including the last
        consumed character before the U+003E character, if any, or up to the
        end of the file otherwise. (If the comment was started by the end of
        the file (EOF), the token is empty.) */
        $data = $this->characters('^>', $this->char);
        $this->emitToken(
            array(
                'data' => $data,
                'type' => self::COMMENT
            )
        );

        $this->char += strlen($data);

        /* Switch to the data state. */
        $this->state = 'data';

        /* If the end of the file was reached, reconsume the EOF character. */
        if ($this->char === $this->EOF) {
            $this->char = $this->EOF - 1;
        }
    }

    private function markupDeclarationOpenState()
    {
        /* If the next two characters are both U+002D HYPHEN-MINUS (-)
        characters, consume those two characters, create a comment token whose
        data is the empty string, and switch to the comment state. */
        if ($this->character($this->char + 1, 2) === '--') {
            $this->char += 2;
            $this->state = 'comment';
            $this->token = array(
                'data' => null,
                'type' => self::COMMENT
            );

            /* Otherwise if the next seven chacacters are a case-insensitive match
            for the word "DOCTYPE", then consume those characters and switch to the
            DOCTYPE state. */
        } elseif (strtolower($this->character($this->char + 1, 7)) === 'doctype') {
            $this->char += 7;
            $this->state = 'doctype';

            /* Otherwise, is is a parse error. Switch to the bogus comment state.
            The next character that is consumed, if any, is the first character
            that will be in the comment. */
        } else {
            $this->char++;
            $this->state = 'bogusComment';
        }
    }

    private function commentState()
    {
        /* Consume the next input character: */
        $this->char++;
        $char = $this->char();

        /* U+002D HYPHEN-MINUS (-) */
        if ($char === '-') {
            /* Switch to the comment dash state  */
            $this->state = 'commentDash';

            /* EOF */
        } elseif ($this->char === $this->EOF) {
            /* Parse error. Emit the comment token. Reconsume the EOF character
            in the data state. */
            $this->emitToken($this->token);
            $this->char--;
            $this->state = 'data';

            /* Anything else */
        } else {
            /* Append the input character to the comment token's data. Stay in
            the comment state. */
            $this->token['data'] .= $char;
        }
    }

    private function commentDashState()
    {
        /* Consume the next input character: */
        $this->char++;
        $char = $this->char();

        /* U+002D HYPHEN-MINUS (-) */
        if ($char === '-') {
            /* Switch to the comment end state  */
            $this->state = 'commentEnd';

            /* EOF */
        } elseif ($this->char === $this->EOF) {
            /* Parse error. Emit the comment token. Reconsume the EOF character
            in the data state. */
            $this->emitToken($this->token);
            $this->char--;
            $this->state = 'data';

            /* Anything else */
        } else {
            /* Append a U+002D HYPHEN-MINUS (-) character and the input
            character to the comment token's data. Switch to the comment state. */
            $this->token['data'] .= '-' . $char;
            $this->state = 'comment';
        }
    }

    private function commentEndState()
    {
        /* Consume the next input character: */
        $this->char++;
        $char = $this->char();

        if ($char === '>') {
            $this->emitToken($this->token);
            $this->state = 'data';

        } elseif ($char === '-') {
            $this->token['data'] .= '-';

        } elseif ($this->char === $this->EOF) {
            $this->emitToken($this->token);
            $this->char--;
            $this->state = 'data';

        } else {
            $this->token['data'] .= '--' . $char;
            $this->state = 'comment';
        }
    }

    private function doctypeState()
    {
        /* Consume the next input character: */
        $this->char++;
        $char = $this->char();

        if (preg_match('/^[\t\n\x0b\x0c ]$/', $char)) {
            $this->state = 'beforeDoctypeName';

        } else {
            $this->char--;
            $this->state = 'beforeDoctypeName';
        }
    }

    private function beforeDoctypeNameState()
    {
        /* Consume the next input character: */
        $this->char++;
        $char = $this->char();

        if (preg_match('/^[\t\n\x0b\x0c ]$/', $char)) {
            // Stay in the before DOCTYPE name state.

        } elseif (preg_match('/^[a-z]$/', $char)) {
            $this->token = array(
                'name' => strtoupper($char),
                'type' => self::DOCTYPE,
                'error' => true
            );

            $this->state = 'doctypeName';

        } elseif ($char === '>') {
            $this->emitToken(
                array(
                    'name' => null,
                    'type' => self::DOCTYPE,
                    'error' => true
                )
            );

            $this->state = 'data';

        } elseif ($this->char === $this->EOF) {
            $this->emitToken(
                array(
                    'name' => null,
                    'type' => self::DOCTYPE,
                    'error' => true
                )
            );

            $this->char--;
            $this->state = 'data';

        } else {
            $this->token = array(
                'name' => $char,
                'type' => self::DOCTYPE,
                'error' => true
            );

            $this->state = 'doctypeName';
        }
    }

    private function doctypeNameState()
    {
        /* Consume the next input character: */
        $this->char++;
        $char = $this->char();

        if (preg_match('/^[\t\n\x0b\x0c ]$/', $char)) {
            $this->state = 'AfterDoctypeName';

        } elseif ($char === '>') {
            $this->emitToken($this->token);
            $this->state = 'data';

        } elseif (preg_match('/^[a-z]$/', $char)) {
            $this->token['name'] .= strtoupper($char);

        } elseif ($this->char === $this->EOF) {
            $this->emitToken($this->token);
            $this->char--;
            $this->state = 'data';

        } else {
            $this->token['name'] .= $char;
        }

        $this->token['error'] = ($this->token['name'] === 'HTML')
            ? false
            : true;
    }

    private function afterDoctypeNameState()
    {
        /* Consume the next input character: */
        $this->char++;
        $char = $this->char();

        if (preg_match('/^[\t\n\x0b\x0c ]$/', $char)) {
            // Stay in the DOCTYPE name state.

        } elseif ($char === '>') {
            $this->emitToken($this->token);
            $this->state = 'data';

        } elseif ($this->char === $this->EOF) {
            $this->emitToken($this->token);
            $this->char--;
            $this->state = 'data';

        } else {
            $this->token['error'] = true;
            $this->state = 'bogusDoctype';
        }
    }

    private function bogusDoctypeState()
    {
        /* Consume the next input character: */
        $this->char++;
        $char = $this->char();

        if ($char === '>') {
            $this->emitToken($this->token);
            $this->state = 'data';

        } elseif ($this->char === $this->EOF) {
            $this->emitToken($this->token);
            $this->char--;
            $this->state = 'data';

        } else {
            // Stay in the bogus DOCTYPE state.
        }
    }
}