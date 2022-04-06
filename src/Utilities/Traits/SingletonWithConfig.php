<?php
/**
 * 带配置的单例模式
 *
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021/10/5 22:47
 */

namespace YLarNtBasic\Utilities\Traits;


trait SingletonWithConfig
{
    protected static $instance;

    /**
     * @param array $config
     * @return static
     */
    public static function getInstance($config = [])
    {
        $key = md5(json_encode($config));
        if (empty(static::$instance[$key])) {
            static::$instance[$key] = new static($config);
        }

        return static::$instance[$key];
    }
}
