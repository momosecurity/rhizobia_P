<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <projectone@immomo.com>
 * Date: 2019/4/17
 * Time: 下午7:33
 */
namespace Security\URLSecurity;

/**
 * Class HttpUtil
 * @package Security\HttpUtil
 */
class DefenseAgainstRedirect
{

    /**
     * DefenseAgainstRedirect constructor.
     */
    public function __construct()
    {
    }


    /**
     * @param $url
     * @param $white
     * @return bool
     */
    public function verifyRedirectUrl($url, $white)
    {
        if (is_array($url)) {
            return false;
        }
        $detail = parse_url($url);

        if (!isset($detail) || !isset($detail['scheme'])) {
            return false;
        }

        if (!in_array($detail['scheme'], array('http', 'https'))) {
            return false;
        }

        if (isset($detail["user"]) || isset($detail["pass"])) {
            return false;
        }

        if (!isset($detail["host"])) {
            return false;
        }
        $host = $detail["host"];
        $flag = false;
        foreach ($white as $item) {
            if (preg_match("/" . str_replace(".", "\\.", $item) . "$/i", $host)) {
                $flag = true;
            }
        }
        if (!$flag) {
            return false;
        }
        if ($this->isInvalidUrl($url, '?', $white)) {
            return false;
        }
        if ($this->isInvalidUrl($url, '\\', $white)) {
            return false;
        }
        return true;
    }


    /**
     * @param $url
     * @param $char
     * @param $white
     * @return bool
     */
    private function isInvalidUrl($url, $char, $white)
    {
        $invalidPos = strpos($url, $char);
        $flag_pos = false;
        foreach ($white as $item) {
            if (false !== $invalidPos && $invalidPos < strpos($url, $item)) {
                $flag_pos = true;
            }
        }
        return $flag_pos;
    }
}
