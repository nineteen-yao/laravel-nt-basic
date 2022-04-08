<?php
/**
 * 返回媒体信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


class VoiceType extends BaseType
{
    /*
     * 报文demo
{
    "msgtype": "voice",
    "voice": {
       "media_id": "@lADOADmaWMzazQKA",
       "duration": "10"
    }
}
     */
    public function set(string $mediaId, int $duration): self
    {
        $this->data[$this->type] = [
            'media_id' => $mediaId,
            'duration' => $duration
        ];

        return $this;
    }
}
