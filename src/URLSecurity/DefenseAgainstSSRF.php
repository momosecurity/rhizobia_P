<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <https://github.com/IIComing>
 * Date: 2019/4/17
 * Time: 下午7:33
 */

namespace Security\URLSecurity;

/**
 * Class DefenseAgainstSSRF
 * @package Security\URLSecurity
 */
class DefenseAgainstSSRF
{

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var int
     */
    private $limit;

    /**
     * DefenseAgainstSSRF constructor.
     */
    function __construct()
    {
        //默认2s超时
        $this->timeout = 2;
        //默认跳转2次
        $this->limit = 2;
    }


    /**
     * @param $var
     */
    public function setTimeout($var)
    {
        $this->timeout = $var;
    }


    /**
     * @param $var
     */
    public function setJmpLimit($var)
    {
        $this->limit = $var;
    }


    /**
     * @param $url
     * @return bool
     */
    public function verifySSRFURL($url)
    {

        if (!$this->checkDomain($url)) {
            return false;
        }

        $ip = $this->getRealIP($url);
        if (!$ip) {
            return false;
        }
        if ($this->isInnerIP($ip)) {
            return false;
        }
        return true;
    }


    /**
     * @param $url
     * @return bool
     */
    private function checkDomain($url)
    {

        if (!is_string($url)) {
            return false;
        }
        $host = parse_url($url);

        if (!isset($host) || !isset($host['scheme'])) {
            return false;
        }

        if (!in_array($host['scheme'], array('http', 'https'))) {
            return false;
        }

        if (isset($host["user"]) || isset($host["pass"])) {
            return false;
        }

        if (!isset($host["host"])) {
            return false;
        }
        return true;
    }


    /**
     * @param $url
     * @return bool|string
     */
    private function getRealIP($url)
    {
        $count = 0;
        $info = $this->getURLInfo($url);
        while ($count < $this->limit - 1 && $info['status'] >= 300 && $info['status'] < 400) {
            $count++;
            $info = $this->getURLInfo($info['location']);
        }
        //大于$limit 次跳转 或 最后一次请求出错
        if ($info['status'] >= 300 || $info['status'] < 200) {
            return false;
        }

        if (!$this->checkDomain($info['host'])) {
            return false;
        }

        $host = @parse_url($info['host'])['host'];
        $ip = gethostbyname($host);
        return $ip;
    }


    /**
     * @param $url
     * @return array
     */
    private function getURLInfo($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ret = array();
        $match = array();
        $ret['status'] = intval($status);
        if ($ret['status'] >= 300 && $ret['status'] < 400) {
            preg_match("#location: ([^\s]*)#i", $result, $match);


            if (substr($match[1], 0, 4) === 'http') {
                $ret['location'] = $match[1];
            } else {
                $ret['location'] = $url . $match[1];
            }
        }
        if ($ret['status'] == 200) {
            $ret['host'] = $url;
        }
        curl_close($ch);
        return $ret;
    }


    /**
     * @param $ip_arg
     * @return bool
     */
    private function isInnerIP($ip_arg)
    {
        $ip = ip2long($ip_arg);
        return ip2long('127.0.0.0') >> 24 === $ip >> 24 or \
                ip2long('10.0.0.0') >> 24 === $ip >> 24 or \
                ip2long('172.16.0.0') >> 20 === $ip >> 20 or \
                ip2long('192.168.0.0') >> 16 === $ip >> 16;
    }
}
