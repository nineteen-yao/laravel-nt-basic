<?php
/**
 * ErrorTrait.php
 *
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021/10/8 11:00
 */

namespace YLarNtBasic\Console;


use Ynineteen\Supports\Logger;

trait ErrorTrait
{
    protected function showErr(\Throwable $t, $extra = [])
    {
        Logger::error($t, $extra);
        if (!is_array($extra)) {
            $extra = [$extra];
        }
        if ($extra) {
            echo json_encode($extra, JSON_UNESCAPED_UNICODE);
        }
        echo $t->getMessage() . '-->' . $t->getFile() . '(' . $t->getLine() . ')' . "\n";
    }

    protected function puts(...$args)
    {
        foreach ($args as $arg) {
            if (is_array($arg) || is_object($arg)) {
                echo json_encode($arg, JSON_UNESCAPED_UNICODE) . "\n";
            } else {
                echo $arg . "\n";
            }
        }
    }
}
