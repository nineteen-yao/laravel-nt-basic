<?php
/**
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-13 14:44
 */


namespace YLarNtBasic\Utilities\Dingtalk\ResponseTypes;


use YLarNtBasic\Utilities\Dingtalk\MessageType;

abstract class BaseType
{

    protected $data = [];

    protected $type;

    public function __construct()
    {
        $this->type = substr(strtolower(class_basename(static::class)), 0, -4);
    }

    protected function makeBody($data = null, $at = null)
    {
        $body = [
            'msgtype' => $this->type,
        ];
        //非空数据
        if ($this->type !== MessageType::TYPE_EMPTY) {
            $body[$this->type] = $data;
        }

        if ($at) {
            $body['at'] = $at;
        }

        return $body;
    }

    abstract public function response($data = null, $at = null);
}
