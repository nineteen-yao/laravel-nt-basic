<?php
/**
 * 返回文本信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


class MarkdownType extends BaseType
{
    /*
     * 报文demo
{
    "msgtype": "markdown",
    "markdown": {
        "title": "首屏会话透出的展示内容",
        "text": "# 这是支持markdown的文本   \n   ## 标题2    \n   * 列表1   \n  ![alt 啊](https://img.alicdn.com/tps/TB1XLjqNVXXXXc4XVXXXXXXXXXX-170-64.png)"
    }
}
     */

    public function set(string $title, string $text): self
    {
        $this->data[$this->type] = [
            'title' => $title,
            'text' => $text
        ];

        return $this;
    }
}
