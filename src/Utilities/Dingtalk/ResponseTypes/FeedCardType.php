<?php
/**
 * 返回文本信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


class FeedCardType extends BaseType
{
    protected $data = [
        'links' => [            //数组，多条记录
            [
                'title' => '',
                'messageURL' => '',
                'picURL' => ''
            ]
            //...
        ]
    ];

    public function response($links = null, $at = null)
    {
        foreach (['title', 'actionURL'] as $val) {
            foreach ($links as $link) {
                if (!isset($btn[$val])) {
                    throw new \Exception('数据参数缺少' . $val . '键值', -1);
                }
            }
        }
        
        return $this->makeBody(['links' => $links], $at);
    }
}
