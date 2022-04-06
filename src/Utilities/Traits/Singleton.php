<?php
/**
 * Singleton.php
 *
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021/10/5 22:47
 */

namespace YLarNtBasic\Utilities\Traits;


trait Singleton
{
    protected static $instance;

    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
