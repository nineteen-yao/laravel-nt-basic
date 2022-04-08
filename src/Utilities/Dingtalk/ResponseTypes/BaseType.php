<?php
/**
 * 报文格式
 * 官方文档见 https://open.dingtalk.com/document/orgapp-server/message-types-and-data-format
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:44
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


use GuzzleHttp\Client;
use YLarNtBasic\Utilities\Dingtalk\MessageType;

abstract class BaseType
{

    protected $data = [
        'msgtype' => '',
    ];

    /**
     * @var string $type 消息类型，如果子类定义有值，将取得定义的值，否则以子类的类名作为类型值
     */
    protected $type;

    public function __construct()
    {
        $this->initialize();
    }

    protected function initialize()
    {
        if (empty($this->type)) {
            $this->type = substr(strtolower(class_basename(static::class)), 0, -4);
        }
        $this->data['msgtype'] = $this->type;
    }

    /**
     * 发送报文
     * @param string $accessToken
     * @param string|null $at
     * @return array
     * @throws \Exception
     */
    public function push(string $accessToken, string $at = null): array
    {
        if (empty($this->data[$this->type]) && $this->type !== MessageType::TYPE_EMPTY) {
            throw new \Exception('数据不完整', -1);
        }

        if (!empty($at)) {
            $this->data['at'] = $at;
        }

        $uri = 'https://oapi.dingtalk.com/robot/send';
        $httpClient = new Client([
            'timeoute' => 10
        ]);

        var_dump($this->data);

        $response = $httpClient->post($uri, [
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
            ],
            'body' => json_encode($this->data, JSON_UNESCAPED_UNICODE),
            'query' => [
                'access_token' => $accessToken
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('网络故障', -1);
        }

        $contents = $response->getBody()->getContents();

        return json_decode($contents, true);
    }

    public function response(): array
    {
        if (empty($this->data[$this->type]) && $this->type !== MessageType::TYPE_EMPTY) {
            return [];
        }

        return $this->data;
    }
}
