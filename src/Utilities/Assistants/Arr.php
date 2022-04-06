<?php
/**
 * 数组扩展辅助函数
 *
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2020/6/8 15:33
 */

namespace YLarNtBasic\Utilities\Assistants;


class Arr extends \Illuminate\Support\Arr
{
    /**
     * 累加累减
     * @param array $array
     * @param string $key
     * @param int $step
     * @return void
     */
    public static function incr(array &$array, string $key, int $step = 1)
    {
        parent::set($array, $key, parent::get($array, $key) + $step);
    }

    /**
     * 判断一个数组是否多维数组
     *
     * @param array $array
     * @return bool
     */
    public static function isMultiDimensions(array $array): bool
    {
        return count($array) === count($array, COUNT_RECURSIVE);
    }

    /**
     * 排除项数组
     *
     * @param array $array
     * @param array|string $needle
     * @return array
     */
    public static function except($array, $needle): array
    {
        if (!is_array($needle)) {
            $needle = [$needle];
        }

        if (parent::isAssoc($array)) {
            return parent::except($array, $needle);
        }

        foreach ($needle as $item) {
            $array = parent::where($array, function ($value, $key) use ($item) {
                return $value != $item;
            });
        }

        return $array;
    }

    /**
     * 将二维数组转化为树形结构
     *
     * @param array $list 数据体
     * @param string $idField id标识字段名
     * @param string $pidField 父id字段名
     * @param string $childField 子内容字段名
     * @return array
     */
    public static function toTree(array $list, string $idField = 'id', string $pidField = 'parent_id', string $childField = 'children'): array
    {
        if (empty($list)) {
            return $list;
        }

        $map = [];
        $tree = [];

        foreach ($list as &$row) {
            $map[$row[$idField]] = &$row;
        }

        foreach ($list as &$row) {
            $parent = &$map[$row[$pidField]];
            if ($parent) {
                $parent[$childField][] = &$row;
                continue;
            }

            $tree[] = &$row;
        }

        return $tree;
    }

    /**
     * @param mixed ...$arrs
     * @return array
     */
    public static function diff(...$arrs): array
    {
        $ret = array_diff(...$arrs);

        return array_values($ret);
    }

    /**
     * @param mixed ...$arrs
     * @return array
     */
    public static function intersect(...$arrs): array
    {
        $ret = array_intersect(...$arrs);

        return array_values($ret);
    }

    /**
     * 键的替换
     *
     * @param array $arr
     * @param array $map
     * @return array
     */
    public static function keyReplace(array $arr, array $map): array
    {
        foreach ($map as $oldKey => $newKey) {
            if (!isset($arr[$oldKey])) {
                continue;
            }
            //不能覆盖原来已经存在的key
            if (isset($arr[$newKey])) {
                continue;
            }

            $arr[$newKey] = $arr[$oldKey];

            unset($arr[$oldKey]);
        }

        return $arr;
    }

    /**
     * 获取一个键值对数组
     *
     * @param array $arr
     * @param string $keyName
     * @param string $valueName
     * @return array
     */
    public static function pairs(array $arr, string $keyName, string $valueName): array
    {

        $result = [];
        foreach ($arr as $item) {
            $result[$item[$keyName]] = $item[$valueName];
        }

        return $result;
    }
}
