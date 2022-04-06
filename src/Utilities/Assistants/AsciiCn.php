<?php
/**
 * 中文ASCII码互换
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2020/9/10 21:24
 */

namespace YLarNtBasic\Utilities\Assistants;


class AsciiCn
{
    /**
     * 编码，将字符转成16进制编码 AsciiCn::encode('ＣＤ');    --> A3C3 A3C4
     * @param $str
     * @return string
     */
    public static function encode($str)
    {
        $str = mb_convert_encoding($str, 'GBK');
        $asc = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $temp_str = dechex(ord($str[$i]));
            $asc .= $temp_str[0] . $temp_str[1];
        }
        return strtoupper($asc);
    }

    /**
     * 将16进制编码，转化为字符串
     * @param $ascii
     * @return bool|null|string|string[]
     */
    public static function decode($ascii)
    {
        $asc = str_split(strtolower($ascii), 2);
        $str = '';
        for ($i = 0; $i < count($asc); $i++) {
            $str .= chr(hexdec($asc[$i][0] . $asc[$i][1]));
        }
        return mb_convert_encoding($str, 'UTF-8', 'GBK');
    }

    /**
     * 将中文的标点符号，转成英文的标点符号  ；--> ;
     * @param $str
     * @return string
     */
    public static function punctuationToEn($str)
    {
        static $searchs;
        static $replaces;

        if (empty($searchs)) {
            $length = 94;
            $cnStart = 41888;
            $enStart = 32;

            $n = 0;
            while ($n <= $length) {
                $dec = $cnStart + $n;
                $hex = dechex($dec);
                $char = self::decode($hex);

                $searchs[] = $char;
                $replaces[] = chr($enStart + $n);

                $n++;
            }
        }

        return str_replace($searchs, $replaces, $str);
    }
}
