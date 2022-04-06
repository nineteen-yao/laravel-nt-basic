<?php

namespace YLarNtBasic\Utilities\Assistants;

class Regex
{
    /**
     * 判断是否合法手机号码
     *
     * @param string $phone
     * @return bool
     */
    public static function isPhoneNumber(string $phone): bool
    {
        if (preg_match('/^1[3456789]\d{9}$/', $phone)) {
            return true;
        }

        return false;
    }


    /*
     * 银行卡校验算法
      16-19 位卡号校验位采用 Luhn 校验方法计算：
      第一步：把信用卡号倒序（61789372994）
      第二步：取出倒序后的奇数位置上的号码， 相加等到总和s1。（eg:s1=6+7+9+7+9+4=42)
      第三步：取出倒序后的偶数位置上的号码，每个号码乘以2。   (eg:2,16,6,4,18)
      第四步：把第三步得到的大于10的号码转化为个位+十位。（eg:2,7,6,4,9)
      第五步：把处理好的偶数位号码相加，得到s2。 (eg:s2=2+7+6+4+9=28)
      第六步：判读(s1+s2)%10 == 0则有效，否则无效。（有效）
    */
    public static function isBankNo(string $card): bool
    {
        // step1 判断是否16到19位
        $pattern = '/^\d{16,19}$/';
        if (!preg_match($pattern, $card)) {
            return false;
        }

        // step2 luhn 算法校验
        $len = strlen($card);
        $sum = 0;
        for ($i = 0; $i < $len; $i++) {
            if (($i + $len) & 1) { // 奇数
                $sum += ord($card[$i]) - ord('0');
            } else { // 偶数
                $tmp = (ord($card[$i]) - ord('0')) * 2;
                $sum += floor($tmp / 10) + $tmp % 10;
            }
        }

        return $sum % 10 === 0;
    }
}