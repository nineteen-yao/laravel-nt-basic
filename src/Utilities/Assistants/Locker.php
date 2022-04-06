<?php

namespace YLarNtBasic\Utilities\Assistants;

use Illuminate\Support\Facades\Redis;
use Ynineteen\Supports\DTime;

class Locker
{

    public static function getLockKey($orginkey): string
    {
        return __FUNCTION__ . ':' . env('REDIS_PREFIX', '') . ':' . sha1($orginkey);
    }

    /**
     * 通过redis给任务上锁
     *
     * @param string $orginkey
     * @return bool
     */
    public static function lock(string $orginkey): bool
    {
        $key = static::getLockKey($orginkey);

        $locktime = Redis::get($key);
        $currenttime = DTime::time();
        if (!$locktime || ($currenttime - $locktime) > 600) {
            Redis::set($key, $currenttime);

            return false;
        }

        return true;
    }


    /**
     * 通过redis解除任务上的锁
     *
     * @param string $orginkey
     */
    public static function unlock(string $orginkey)
    {
        Redis::del(static::getLockKey($orginkey));
    }
}