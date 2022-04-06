<?php
/**
 * 全局函数
 *
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021/11/16 10:38
 */

use Illuminate\Support\Facades\Storage;
use YEasyDingTalk\Application;
use YLarNtBasic\Utilities\Dingtalk\DingStarter;

/**
 * 钉钉开发单例实例化
 *
 * @param array|string $config
 * @return Application
 */
function dingtalk($config = []): Application
{
    return DingStarter::app($config);
}