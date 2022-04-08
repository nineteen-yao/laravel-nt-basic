<?php
/**
 * 返回文本信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


use YLarNtBasic\Utilities\Assistants\Arr;

class ActionCardType extends BaseType
{
    /*
     * 报文demo
     * 整体跳转
{
    "msgtype": "action_card",
    "action_card": {
        "title": "是透出到会话列表和通知的文案",
        "text": "支持markdown格式的正文内容",
        "singleTitle": "查看详情",
        "singleURL": "https://open.dingtalk.com"
    }
}
     * 独立跳转
{
    "msgtype": "action_card",
    "action_card": {
        "title": "是透出到会话列表和通知的文案",
        "text": "支持markdown格式的正文内容",
        "hideAvatar": "0"
        "btnOrientation": "1",
        "type": 2,
        "btns": [
            {
                "title": "一个按钮",
                "actionURL": "https://www.taobao.com"
            },
            {
                "title": "两个按钮",
                "actionURL": "https://www.tmall.com"
            }
        ]
    }
}
    */

    protected $type = 'actionCard';

    public function setSingle(string $title, string $markdown, string $detailBtnText = '', string $url = ''): self
    {
        $this->data[$this->type] = [
            'title' => $title,
            'text' => $markdown,
            'singleTitle' => $detailBtnText,
            'singleURL' => $url,
            'type' => 1
        ];

        return $this;
    }

    public function setMult(string $title, string $markdown, array $list): self
    {

        foreach ($list as &$item) {
            $item = Arr::only($item, ['title', 'actionURL']);
            if (count($item) !== 2) {
                throw new \Exception('列表数据的每个element的key应包含有title,actionURL', -1);
            }
        }

        $this->data[$this->type] = [
            'title' => $title,
            'text' => $markdown,
            'hideAvatar' => '0',
            'btnOrientation' => "1",
            'type' => 2,
            'btns' => $list
        ];

        return $this;
    }
}
