<?php
/**
 * SHA1.php
 *
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021/11/18 14:16
 */

namespace YLarNtBasic\Utilities\Dingtalk\EventCallback;


class SHA1
{
    public function getSHA1($token, $timestamp, $nonce, $encrypt_msg): array
    {
        try {
            $array = array($encrypt_msg, $token, $timestamp, $nonce);
            sort($array, SORT_STRING);
            $str = implode($array);
            return array(ErrorCode::$OK, sha1($str));
        } catch (\Exception $e) {
            print $e . "\n";
            return array(ErrorCode::$ComputeSignatureError, null);
        }
    }
}
