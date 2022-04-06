<?php
/**
 * 返回文本信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


class MarkdownType extends BaseType
{
    protected $data = [
        'title' => '',
        'text' => ''
    ];

    public function response($data = null, $at = null)
    {
        foreach ($this->data as $key => $val) {
            if (!isset($data[$key])) {
                throw new \Exception('数据参数缺少' . $key . '键值', -1);
            }
        }
        return $this->makeBody(array_merge($this->data, $data), $at);
    }
}
