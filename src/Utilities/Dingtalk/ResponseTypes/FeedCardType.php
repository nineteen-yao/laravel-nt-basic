<?php
/**
 * 返回文本信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


use YLarNtBasic\Utilities\Assistants\Arr;

class FeedCardType extends BaseType
{
    /*
     * 报文demo
{
    "feedCard": {
        "links": [
            {
                "title": "A.铁观音",
                "messageURL": "https://www.baidu.com/",
                "picURL": "http://118.25.182.23:8051/img/3e8cc4bc-e6ac-4ebc-9ff3-ae005aedb13d.png"
            },
            {
               "title": "B.龙井",
                "messageURL": "https://www.baidu.com/",
                "picURL": "http://118.25.182.23:8051/img/3e8cc4bc-e6ac-4ebc-9ff3-ae005aedb13d.png"
            },
            {
               "title": "C.菊花茶",
                "messageURL": "https://www.baidu.com/",
                "picURL": "http://118.25.182.23:8051/img/3e8cc4bc-e6ac-4ebc-9ff3-ae005aedb13d.png"
            },
            {
                "title": "D.红茶",
                "messageURL": "https://www.baidu.com/",
                "picURL": "http://118.25.182.23:8051/img/3e8cc4bc-e6ac-4ebc-9ff3-ae005aedb13d.png"
            }
        ]
    },
    "msgtype": "feedCard"
}
     */

    protected $type = 'feedCard';

    public function set(array $list): self
    {
        foreach ($list as &$item) {
            $item = Arr::only($item, ['title', 'messageURL', 'picURL']);
            if (count($item) !== 2) {
                throw new \Exception('feedCard类型列表数据的每个element的key应包含有title,messageURL,picURL', -1);
            }
        }

        $this->data[$this->type] = [
            'links' => $list
        ];

        return $this;
    }
}
