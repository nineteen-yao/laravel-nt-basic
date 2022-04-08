<?php
/**
 * 返回文件信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


class FileType extends BaseType
{
    /*
     * 报文demo
{
    "msgtype": "file",
    "file": {
       "media_id": "MEDIA_ID"
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
