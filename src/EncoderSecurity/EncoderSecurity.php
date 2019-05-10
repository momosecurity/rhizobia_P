<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <projectone@immomo.com>
 * Date: 2019/4/17
 * Time: 下午7:33
 */
namespace Security\EncoderSecurity;

/**
 * Class EncoderSecurity
 * @package Security\EncoderSecurity
 */

use Security\HTMLPurifier\HTMLPurifier;

/**
 *
 * @property HtmlEntityEncoder $htmlEntityEncoder
 * @property JavaScriptEncoder $javascriptEncoder
 * @property HTMLPurifier $htmlPurifier
 */
class EncoderSecurity
{

    const CHAR_LOWERS = 'abcdefghijklmnopqrstuvwxyz';
    const CHAR_UPPERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHAR_DIGITS = '0123456789';
    const CHAR_SPECIALS = '.-_!@$^*=~|+?';
    const CHAR_LETTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const CHAR_ALPHANUMERICS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    /**
     * @var array
     */
    protected $component = array();

    /**
     * EncoderSecurity constructor.
     */
    function __construct()
    {
    }

    /**
     * @return HtmlEntityEncoder
     */
    public function getHtmlEntityEncoder()
    {
        return new HtmlEntityEncoder();
    }

    /**
     * @return JavaScriptEncoder
     */
    public function getJavascriptEncoder()
    {
        return new JavaScriptEncoder();
    }

    /**
     * @return HTMLPurifier
     */
    public function getHtmlPurifier()
    {
        return new HTMLPurifier();
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        if (!isset($this->component[$key])) {
            $func = "get" . $key;
            if (method_exists($this, $func)) {
                $this->component[$key] = $this->$func();
            } else {
                trigger_error(' unhandled key: ' . $key, E_USER_NOTICE);
            }
        }

        return isset($this->component[$key]) ? $this->component[$key] : null;
    }


}
