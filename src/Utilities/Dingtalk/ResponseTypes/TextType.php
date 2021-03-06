<?php
/**
 * 返回文本信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


class TextType extends BaseType
{
    /*
     * 报文demo
{
    "msgtype": "text",
    "text": {
        "content": "月会通知"
    }
}
     */
    public function set(string $message): self
    {
        $this->data[$this->type] = [
            'content' => $message
        ];

        return $this;
    }
}
