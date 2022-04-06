<?php
/**
 * 华为云依赖于 guzzlehttp 6.3.0
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-02-27 16:10
 */


namespace YLarNtBasic\Utilities\Oss\Connectors;

use YLarNtBasic\Utilities\Oss\Connection;
use YLarNtBasic\Utilities\Oss\Connectors\HwObs\ObsClient;
use Ynineteen\Supports\Logger;

class HwObs extends Connection
{
    public function __construct($connect)
    {
        parent::__construct($connect);

        $this->oss = new ObsClient($this->config);
    }

    /**
     * 上传文件到OSS服务器
     * @param string $localAbsolutePath
     * @param string $remoteRelativeUrl
     * @return int|mixed|null
     * @throws \Exception
     */
    public function upload($localAbsolutePath, $remoteRelativeUrl = null)
    {
        $this->checkLocalAbsolutePath($localAbsolutePath);
        $remoteRelativeUrl = $this->getRemoteRelativeUrl($remoteRelativeUrl, $localAbsolutePath);
        $remoteRelativeUrl = $this->config['namespace'] . $remoteRelativeUrl;
        $promise = $this->oss->putObjectAsync([
            'Bucket' => $this->bucket,
            'Key' => $remoteRelativeUrl,
            'SourceFile' => $localAbsolutePath
        ], function ($obsException, $resp) {
            Logger::info(
                '华为OBS上传回调结果',
                $obsException,
                $resp
            );
        });
        $promise->wait();

        return [
            'connect' => $this->connection,
            'url' => $this->getRemoteAbsoluteUrl($remoteRelativeUrl)
        ];
    }
}
