<?php
/**
 * 返回图片信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


class ImageType extends BaseType
{
    /*
     * 报文demo
{
    "msgtype": "image",
    "image": {
        "media_id": "@lADOADmaWMzazQKA"
    }
}
     */
    public function set(string $mediaId): self
    {
        $this->data[$this->type] = [
            'media_id' => $mediaId
        ];

        return $this;
    }
}
