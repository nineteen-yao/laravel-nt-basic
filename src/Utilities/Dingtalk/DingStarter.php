<?php
/**
 * DingStarter.php
 *
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021/11/18 14:51
 */

namespace YLarNtBasic\Utilities\Dingtalk;

use YEasyDingTalk\Application;
use YLarNtBasic\Utilities\Dingtalk\EventCallback\DingCallbackCrypto;
use function app;
use function config;

class DingStarter
{
    /**
     * 获取一个应用实例
     *
     * @param array $config
     * @return Application
     */
    public static function app($config = []): Application
    {
        static $instance;

        if (empty($config)) {
            $default = config('dingtalk.corp', 'default');
            $config = config('dingtalk.connections.' . $default, []);
        }
        if (is_string($config)) {
            $config = config('dingtalk.connections.' . $config, []);
        }

        $key = md5(json_encode($config, JSON_UNESCAPED_UNICODE));

        if (empty($instance[$key])) {
            $instance[$key] = new Application($config);
        }

        return $instance[$key];
    }

    /**
     * 时间监听事件
     *
     * @param \YEasyDingTalk\Application $app
     * @param array $config
     * @return array
     * @throws \Exception
     */
    public static function eventCallback(Application $app, array $config)
    {
        $token = trim($config['token']);
        $aesKey = trim($config['aes_key']);
        $ownerKey = $app->config->app_key;

        $crypt = new DingCallbackCrypto($token, $aesKey, $ownerKey);

        $input = app()->request->input();
        $body = app()->request->getContent();
        $encryptData = json_decode($body, true);

        $eventJson = $crypt->getDecryptMsg($input['msg_signature'], $input['nonce'], $encryptData['encrypt'], $input['timestamp']);
        $responseJson = $crypt->getEncryptedMap("success");

        $eventData = json_decode($eventJson, true);
        $responseData = json_decode($responseJson, true);

        return array_merge($eventData, [
            'response' => $responseData
        ]);
    }
}
