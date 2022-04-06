<?php
/**
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2020/12/11 12:17
 */


namespace YLarNtBasic\Console;


use Illuminate\Console\Scheduling\Schedule;

abstract class ScheduleBase
{
    abstract public static function handle(Schedule &$schedule);
}
