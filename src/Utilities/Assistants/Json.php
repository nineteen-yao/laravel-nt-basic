<?php
/**
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2023/02/25 09:23
 */


namespace YLarNtBasic\Utilities\Assistants;



class Json
{
    public static function parse(string $value)
    {
        if (empty($value)) {
            return [];
        }
        $res = json_decode($value, true);

        return $res ?: [];
    }

    public static function stringify(array|string $value): string
    {
        if (empty($value)) {
            $value = [];
        }
        if (is_string($value)) {
            $res = json_decode($value, true);
            if (is_array($res)) {
                return $value;
            }
            $value = explode(',', $value);
        }
        $res = json_encode($value, JSON_UNESCAPED_UNICODE);
        return $res ?: '[]';
    }

}
