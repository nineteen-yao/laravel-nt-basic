<?php
/**
 * 返回文本信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


class ActionCardType extends BaseType
{
    const TYPE_SINGLE = 1;          //整体跳转模式
    const TYPE_BTNS = 2;

    protected $type = self::TYPE_SINGLE;

    protected $data = [
        'title' => '',
        'text' => '',
        'singleTitle' => '',    //整体
        'singleURL' => '',      //整体
        'hideAvatar' => '',         //独立跳转
        'btnOrientation' => '',     //独立跳转
        'btns' => [                 //独立跳转
            [
                'title' => '',      //标题
                'actionURL' => ''   //跳转地址
            ],
            //...
        ],
    ];

    public function response($data = null, $at = null)
    {
        //卡片类型
        if (!empty($data['type']) && in_array($data['type'], [self::TYPE_SINGLE, self::TYPE_BTNS])) {
            $this->type = $data['type'];
        }

        unset($data['type']);

        //整体模式跳转
        if ($this->type === self::TYPE_SINGLE) {
            foreach (['title', 'text', 'singleTitle', 'singleURL'] as $key) {
                if (!isset($data[$key])) {
                    throw new \Exception('数据参数缺少' . $key . '键值', -1);
                }
            }
        }

        //独立跳转模式
        if ($this->type === self::TYPE_BTNS) {
            foreach (['title', 'text', 'hideAvatar', 'btnOrientation', 'btns'] as $key) {
                if (!isset($data[$key])) {
                    throw new \Exception('数据参数缺少' . $key . '键值', -1);
                }
            }

            foreach (['title', 'actionURL'] as $val) {
                foreach ($data['btns'] as $btn) {
                    if (!isset($btn[$val])) {
                        throw new \Exception('数据参数btns子项缺少' . $val . '键值', -1);
                    }
                }
            }
        }


        return $this->makeBody(array_merge($this->data, $data), $at);
    }
}
