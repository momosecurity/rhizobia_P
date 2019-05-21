<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <https://github.com/IIComing>
 * Date: 2019/4/17
 * Time: 下午7:33
 */

namespace Security\URLSecurity;

/**
 * Class DefenseAgainstCSRF
 * @package Security\URLSecurity
 */
class DefenseAgainstCSRF
{
    /**
     * DefenseAgainstCSRF constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $csrf_token_name
     * @param string $session_name
     * @param string $salt
     * @return bool
     */
    public function verifyCSRFToken($csrf_token_name = 'csrf_token', $session_name = 'session', $salt = '')
    {
        if (!isset($_POST[$csrf_token_name]) || !isset($_COOKIE[$session_name])) {
            return false;
        }
        $session = $_COOKIE[$session_name] ? $_COOKIE[$session_name] : null;
        $session = trim($session);
        $csrf_token = $_POST[$csrf_token_name] ? $_POST[$csrf_token_name] : null;
        $csrf_token = trim($csrf_token);
        if (!$session || !$csrf_token) {
            return false;
        }
        if (strlen($session) !== 32 || strlen($csrf_token) !== 32) {
            return false;
        }
        if ($csrf_token === $this->getCSRFToken($session, $salt)) {
            return true;
        }
        return false;
    }


    /**
     * @param $session
     * @param $salt
     * @return string
     */
    private function getCSRFToken($session, $salt)
    {
        return md5(md5($session.'|'.$salt).'|'.$salt);
    }
}
