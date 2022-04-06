<?php
/**
 * OSS管理中心
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-02-27 16:02
 */


namespace YLarNtBasic\Utilities\Oss;


use function config;

class Oss
{
    protected static $driver;

    protected static $instance;

    protected static $connectName;


    public static function getDomain($connect)
    {
        return config('oss.connections.' . $connect . '.domain', '');
    }

    /**
     * 连接
     * @param null $name
     * @throws \Exception
     */
    protected static function connect($name = null)
    {
        $connect = $name ? $name : config('oss.default');
        $connectInfo = config('oss.connections.' . $connect);
        if (empty($connectInfo)) {
            throw new \Exception('未知的OSS连接:' . $connect, -1);
        }
        static::$connectName = $connect;

        $driver = config('oss.connections.' . $connect . '.driver');
        if (empty($driver)) {
            throw new \Exception('未配置的OSS驱动:' . $driver, -1);
        }

        $driver = __NAMESPACE__ . '\Connectors\\' . $driver;
        if (!class_exists($driver)) {
            throw new \Exception('不存在的OSS驱动:' . $driver, -1);
        }

        static::$driver = $driver;
    }

    public static function __callStatic($name, $arguments)
    {
        //设置默认驱动
        if (empty(static::$driver)) {
            static::connect();
        }

        if (empty(static::$instance)) {
            static::$instance = new static();
        }

        return call_user_func_array([static::$instance, $name], $arguments);
    }

    public function __call($name, $arguments)
    {
        $driver = new static::$driver(static::$connectName);

        return call_user_func_array([$driver, $name], $arguments);
    }
}
