<?php
/**
 * 返回连接信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


class LinkType extends BaseType
{
    /*
     * 报文demo
{
    "msgtype": "link",
    "link": {
        "messageUrl": "http://s.dingtalk.com/market/dingtalk/error_code.php",
        "picUrl":"@lALOACZwe2Rk",
        "title": "测试",
        "text": "测试"
    }
}
     */
    public function set(string $title, string $content, string $url, string $mediaId): self
    {
        $this->data[$this->type] = [
            'messageUrl' => $url,
            'picUrl' => $mediaId,
            'title' => $title,
            'text' => $content
        ];

        return $this;
    }
}
