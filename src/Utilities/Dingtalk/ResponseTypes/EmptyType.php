<?php
/**
 * 返回文本信息响应体
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:43
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


class EmptyType extends BaseType
{
    public function response($data = null, $at = null)
    {
        return $this->makeBody();
    }
}
