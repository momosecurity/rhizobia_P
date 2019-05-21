<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <https://github.com/IIComing>
 * Date: 2019/4/17
 * Time: 下午7:33
 */
namespace Security\URLSecurity;


/**
 * Class DefenseAgainstRedirect
 * @package Security\URLSecurity
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
        $host=$this->verifyUrl($url);
        if($host===false){
            return false;
        }
        if(is_string($white)) {
            $white = array($white);
        }

        $flag = false;
        foreach ($white as $item) {
            if(strpos($item,".")!==0){
                if($this->verifySingleRedirectUrl($url,$item,$host)){
                    $flag = true;
                    break;
                }
            }else if (strpos($item,".")===0 && preg_match("/" . str_replace(".", "\\.", $item) . "$/i", $host)) {
                $flag = true;
                break;
            }
        }
        return $flag && (!$this->isInvalidUrl($url, '?', $white)) && (!$this->isInvalidUrl($url, '\\', $white));
    }


    /**
     * @param $url 重定向url
     * @param $white 白名单
     * @param $host 重定向url host
     * @return bool
     */
    private function verifySingleRedirectUrl($url, $white, $host){

        $arrayWhite=array($white);
        if ($this->isInvalidUrl($url, '?', $arrayWhite)) {
            return false;
        }
        if ($this->isInvalidUrl($url, '\\', $arrayWhite)) {
            return false;
        }
        if($host===$white){
            return true;
        }
        return false;
    }

    /**
     * @param $url 重定向url
     * @return bool
     */
    private function verifyUrl($url){
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
        return $detail["host"];
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
