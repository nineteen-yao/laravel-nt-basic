<?php
/**
 * 云存储基础类
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-02-27 15:39
 */


namespace YLarNtBasic\Utilities\Oss;


use function config;

abstract class Connection
{
    protected $connection;

    protected $enable = true;

    protected $is_random_name = false;

    protected $config;

    protected $oss;

    /**
     * Connection constructor.
     * @param $connect
     */
    public function __construct($connect)
    {
        $this->connection = $connect;
        $this->config = config('oss.connections.' . $connect);

        set_time_limit(0);
        ini_set('memory_limit', '256m');
    }

    /**
     * 判断本地文件是否存在
     * @param $localAbsolutePath
     * @throws \Exception
     */
    protected function checkLocalAbsolutePath($localAbsolutePath)
    {
        if (!file_exists($localAbsolutePath)) {
            $errMsg = '要上传到OSS的本地文件“' . $localAbsolutePath . '”，未被计算机找到!';

            throw new \Exception($errMsg, -1);
        }
    }

    /**
     * 获取远程的OSS路径
     * @param $remoteRelativeUrl
     * @param $localAbsolutePath
     * @return mixed|string
     */
    protected function getRemoteRelativeUrl($remoteRelativeUrl, $localAbsolutePath)
    {
        if (!$remoteRelativeUrl || $this->is_random_name) {
            $ext = strrchr($localAbsolutePath, '.');
            $remoteRelativeUrl = date('Ymd', time()) . '/' . date('Hi', time()) . '-' . substr(md5(time() . '-' . rand(10000, 99999)), 3, 16) . $ext;
        }

        return $remoteRelativeUrl;
    }

    /**
     * 获取远程路径
     * @param $remoteRelativeUrl
     * @return string
     */
    protected function getRemoteAbsoluteUrl($remoteRelativeUrl)
    {
        if (empty($this->config['domain'])) {
            return $remoteRelativeUrl;
        }

        $domain = rtrim($this->config['domain'], '/') . '/';
        $uri = ltrim($remoteRelativeUrl, '/');

        return $domain . $uri;
    }

    public function __get($name)
    {
        return $this->config[$name] ?? null;
    }

    /**
     * 上传文件到OSS服务器
     * @param string $localAbsolutePath 文件在本地服务器存储的绝对路径
     * @param string $remoteRelativeUrl 上传到OSS服务器的远程相对URL
     * @return mixed
     */
    abstract public function upload($localAbsolutePath, $remoteRelativeUrl = null);
}
