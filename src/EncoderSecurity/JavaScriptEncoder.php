<?php
/**
 * OWASP Enterprise Security API (ESAPI)
 *
 * This file is part of the Open Web Application Security Project (OWASP)
 * Enterprise Security API (ESAPI) project.
 *
 * LICENSE: This source file is subject to the New BSD license.  You should read
 * and accept the LICENSE before you use, modify, and/or redistribute this
 * software.
 *
 * PHP version 5.2
 *
 * @category  OWASP
 * @package   ESAPI_Codecs
 * @author    Mike Boberski <boberski_michael@bah.com>
 * @copyright 2009-2010 The OWASP Foundation
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD license
 * @version   SVN: $Id$
 * @link      http://www.owasp.org/index.php/ESAPI
 */
namespace Security\EncoderSecurity;

/**
 * Class JavaScriptEncoder
 * @package Security\EncoderSecurity
 */
class JavaScriptEncoder extends BaseEncoder
{

    /**
     * JavaScriptEncoder constructor.
     */
    function __construct()
    {
        parent::__construct();
    }


    /**
     * @param string $immune
     * @param string $c
     * @return string
     */
    public function encodeCharacter($immune, $c)
    {
        //确定编码方式
        $initialEncoding = $this->detectEncoding($c);

        // 标准化编码为 UTF-32
        $_4ByteUnencodedOutput = $this->normalizeEncoding($c, $initialEncoding);

        $encodedOutput = mb_convert_encoding("", $initialEncoding);

        // 取4字节字符.
        $_4ByteCharacter = $this->forceToSingleCharacter($_4ByteUnencodedOutput);

        // 获取ASCII值.
        list(, $ordinalValue) = unpack("N", $_4ByteCharacter);

        // 检查是否免疫字符
        if ($this->containsCharacter($_4ByteCharacter, $immune, $initialEncoding)) {
            return $encodedOutput . chr($ordinalValue);
        }
        // 数字字母字符原样输出
        $hex = $this->getHexForNonAlphanumeric($_4ByteCharacter);
        if ($hex === null) {
            return $encodedOutput . chr($ordinalValue);
        }
        // encode up to 256 with \\xHH
        $pad = mb_substr("00", mb_strlen($hex));
        if ($ordinalValue < 256) {
            return "\\x" . $pad . strtoupper($hex);
        }
        // otherwise encode with \\uHHHH
        $pad = mb_substr("0000", mb_strlen($hex));
        return "\\u" . $pad . strtoupper($hex);
    }
}
