<?php
/**
 * Created by MOMOSEC.
 * User: thecastle
 * Date: 2019/4/17
 * Time: 下午7:33
 */
namespace Security\SQLSecurity;


class Mysql
{

    const PDO_TIMEOUT = 1;
    const PDO_CHARSET = "UTF8";
    private static $mysql;

    public static function getInstance()
    {
        if (isset(self::$mysql))
            return self::$mysql;
        self::$mysql = new Mysql();
        return self::$mysql;
    }

    /**
     * @param $config 连接配置
     * @param $num 重试次数
     * @return \Security\SQLSecurity\Database
     */
    public function initdb($config, $num = 3)
    {
        $retry = 0;
        while ($retry++ < $num && !isset($database)) {
            $database = $this->connectDB($config);
            if ($database != null) {
                break;
            }
        }
        return $database;
    }

    /**
     * @param $config
     * @return null|\Security\SQLSecurity\Database
     */
    private function connectDB($config)
    {
        $rt = null;
        try {
            $dsn = "mysql:host={$config['hostname']};port={$config['port']};dbname={$config['database']};charset=UTF8";
            // 设置默认超时时间
            $options = array(\PDO::ATTR_TIMEOUT => isset($config['timeout']) ? $config['timeout'] : self::PDO_TIMEOUT);
            // 设置数据库编码
            $charset = !empty($config['charset']) ? $config['charset'] : self::PDO_CHARSET;
            $options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES '{$charset}'";
            // 设置错误级别
            $options[\PDO::ATTR_ERRMODE] = isset($config['ATTR_ERRMODE'])&&$config['ATTR_ERRMODE'] ? $config['ATTR_ERRMODE'] : \PDO::ERRMODE_EXCEPTION;
            // 设置默认提取数据的模式
            $options[\PDO::ATTR_DEFAULT_FETCH_MODE] = isset($config['ATTR_DEFAULT_FETCH_MODE']) && $config['ATTR_DEFAULT_FETCH_MODE'] ? $config['ATTR_DEFAULT_FETCH_MODE'] : \PDO::FETCH_ASSOC;
            //设置默认不启用持久连接
            $options[\PDO::ATTR_PERSISTENT] = isset($config['ATTR_PERSISTENT']) && $config['ATTR_PERSISTENT'] ? true : false;
            $rt = new Database($dsn, $config['username'], $config['password'], $options);
        } catch (\PDOException $e) {
            //todo 异常信息写入日志
            trigger_error(' mysql连接异常: ' . $e->getMessage(), E_ERROR);
        }
        return $rt;
    }

}