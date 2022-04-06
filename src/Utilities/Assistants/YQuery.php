<?php

namespace YLarNtBasic\Utilities\Assistants;

use Ynineteen\Supports\DTime;

class YQuery
{
    public static function queryBetween($query, $field, $start = null, $end = null)
    {
        if ($start) {
            $query->where($field, '>', $start);
        }
        if ($end) {
            $query->where($field, '<', DTime::nextDay($end));
        }

        return $query;
    }
}