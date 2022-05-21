<?php
/**
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2020/5/29 11:55
 */

namespace YLarNtBasic\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;
use YLarNtBasic\Utilities\Assistants\Url;
use Ynineteen\Supports\Logger;
use function class_basename;

abstract class BaseModel extends Model
{
    //关闭默认的时间方法
//    public $timestamps = false;

    /**
     * @var array $mapByPk 每个表的ID与名称的hash映射
     */
    protected static $mapByPk;

    /**
     * @var string $nameKey 一个表中表示名称的字段名
     */
    protected static $nameKey = 'name';

    protected static $ignoreFields = [];

    //去掉表名加s复数的方式
    public function getTable()
    {
        $tableName = $this->table ? $this->table : strtolower(Str::snake(class_basename($this)));
        $suffix = substr($tableName, -6);

        return $suffix === '_model' ? substr($tableName, 0, -6) : $tableName;
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');
    }

    /**
     * 获取模型的数据库连接
     *
     * @return \Illuminate\Database\Connection|\Illuminate\Database\ConnectionInterface
     */
    public static function db()
    {
        static $static;
        if (empty($static)) {
            $static = new static();
        }

        return $static->getConnection();
    }

    /**
     * 获取表的所有字段
     *
     * @param array $except
     * @param array $only
     * @return array
     */
    public static function columns(array $except = [], array $only = []): array
    {
        $table = static::getModel()->getTable();
        $connectName = static::getModel()->getConnectionName();

        $columns = Schema::connection($connectName)->getColumnListing($table);

        $res = [];
        foreach ($columns as $column) {
            if (!empty($except) && in_array($column, $except)) {
                continue;
            }
            if (!empty($only) && !in_array($column, $only)) {
                continue;
            }
            $res[] = $column;
        }

        return $res;
    }

    /**
     * 事务开始
     *
     * @throws Throwable
     */
    public static function transactionStart()
    {
        static::db()->beginTransaction();
    }

    /**
     * 事务提交
     *
     * @throws Throwable
     */
    public static function transactionCommit()
    {
        static::db()->commit();
    }

    /**
     * 事务回滚并且处理异常
     *
     * @param Throwable|null $throwable
     * @throws Throwable
     */
    public static function transactionRollback(Throwable $throwable = null)
    {
        static::db()->rollBack();

        if ($throwable) {
            throw new \Exception($throwable->getMessage(), (int)$throwable->getCode());
        }
    }

    /**
     * 开启简单的标准事务流
     *
     * @param callable $resolve 执行匿名函数，一个不带参数的匿名函数
     * @param callable|null $reject 出错执行匿名函数
     * @return mixed
     * @throws Throwable
     */
    public static function transactionFlow(callable $resolve, callable $reject = null)
    {
        static::transactionStart();
        try {
            $r = $resolve();

            static::transactionCommit();

            return $r;
        } catch (Throwable $throwable) {
            if ($reject !== null) {
                $reject();
            }

            static::transactionRollback($throwable);
        }
    }

    /**
     * 通过ID，获取名称、或者其它属性 本质是方法 getValueByPk，区别在这里不用查询多次sql，而是一次查询，存储在内存堆中
     * 示例，Model::name(1,'title')，获取ID为1的title值
     *
     * @param      $id
     * @param null $property
     * @return mixed
     */
    public static function name($id, $property = null)
    {
        if (!$property) {
            $property = static::$nameKey;
        }

        $type = class_basename(static::class) . '-' . $property;
        if (!isset(static::$mapByPk[$type][$id])) {
            static::$mapByPk[$type][$id] = static::getValueByPk($id, $property);
        }

        return static::$mapByPk[$type][$id];
    }

    /**
     * 通过ID，获取名称、或者其它属性
     * 示例，Model::getValueByPk(1,'title')，获取ID为1的title值
     *
     * @param $id
     * @param $fieldName
     * @return mixed
     */
    public static function getValueByPk($id, $fieldName)
    {
        return self::find($id, [$fieldName])->{$fieldName};
    }

    /**
     * 分页查询
     * @param static|null $query
     * @param int $page
     * @param int $limit
     * @param bool $isReturnArray
     * @return array
     */
    public static function pagination(self $query = null, int $page = 1, int $limit = 25, bool $isReturnArray = false): array
    {
        $offset = ($page - 1) * $limit;

        if (empty($query)) {
            $primaryKey = static::getKeyName();
            $query = static::orderBy($primaryKey, 'desc');
        }

        //获取总数
        $total = $query->count();

        $ret = [
            'list' => [],
            'pagenation' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total
            ]
        ];

        if (empty($total)) {
            return $ret;
        }


        $rs = $query->offset($offset)->limit($limit)->get();

        $ret['list'] = ($isReturnArray ? $rs->toArray() : $rs);


        return $ret;
    }


    /**
     * 必需赋值的字段
     * 使用add方法的时候专用，子类对改属性进行重写
     *
     * @return array
     */
    public static function getNeededAttributes(): array
    {
        return [];
    }

    /**
     * 默认字段数据
     * 使用add方法的时候专用，子类对改属性进行重写
     *
     * @return array
     */
    protected static function getDefaultAttributes(): array
    {
        return [];
    }

    /**
     * 录入表时，校验字段的数据类型不能是数组或者对象类型
     * 使用add方法的时候专用
     *
     * @param string $field
     * @param $value
     * @throws \Exception
     */
    protected static function checkFieldValue(string $field, $value)
    {
        if (is_object($value) || is_array($value)) {
            throw new \Exception($field . '的值不能为对象或者数组类型', -1);
        }
    }

    /**
     * 判断是否输入忽略的字段
     *
     * @param string $field
     * @return bool
     */
    protected static function isIgnoreField(string $field): bool
    {
        if (empty(static::$ignoreFields)) {
            return false;
        }

        return !in_array($field, static::$ignoreFields);
    }

    /**
     * 过滤 数据，将数据类型设置为基本数据类型
     *
     * @param $value
     * @return false|int|string
     */
    public static function setValue($value)
    {
        if (is_string($value)) {
            return trim($value);
        }
        if (is_bool($value)) {
            return (int)$value;
        }

        if (is_null($value)) {
            return '';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    /**
     * 添加记录
     *
     * @param array $data
     * @return static
     * @throws \Exception
     */
    public static function add(array $data): self
    {
        //必要数据
        foreach (static::getNeededAttributes() as $field) {
            if (!isset($data[$field]) || !$data[$field]) {
                throw new \Exception('缺少必要字段[' . $field . ']数据', -1);
            }
        }

        //默认数据merge
        $data = array_merge(static::getDefaultAttributes(), $data);

        $model = new static();
        foreach ($data as $field => $value) {
            //判断字段数据类型的合法性，不能是array和object
            static::checkFieldValue($field, $value);
            //忽略的字段不给予录入
            if (static::isIgnoreField($field)) continue;

            $model->{$field} = static::setValue($value);
        }

        if (!$model->save()) {
            throw new \Exception('程序异常', -1);
        }

        return $model;
    }

    /**
     * 检查数据唯一，并且录入
     *
     * @param array $data
     * @param string|array $uniqueFields 需要做唯一校验的字段
     * @return static
     * @throws \Exception
     */
    public static function checkUniqueAndAdd(array $data, $uniqueFields): self
    {
        if (is_string($uniqueFields)) {
            $uniqueFields = explode(',', $uniqueFields);
        }

        foreach ($uniqueFields as $uniqueField) {
            $num = static::where($uniqueField, $data[$uniqueField])->count();
            if ($num) {
                Logger::error('重复数据', $data, $uniqueField);
                throw new \Exception('[' . $data[$uniqueField] . ']记录已经存在，录入失败', -1);
            }
        }

        return static::add($data);
    }

    /**
     * 录入或者更新
     *
     * @param array $condition 更新的数组条件
     * @param array $insertData 录入的数据
     * @param array $updateExceptKeys 做更新的时候，要排除更新的字段
     * @return bool|static
     * @throws \Exception
     */
    public static function addOrUpdate(array $condition, array $insertData, array $updateExceptKeys = []): self
    {
        /**
         * @var static $model
         */
        $model = static::where($condition)->first();
        if (empty($model)) {
            return static::add($insertData);
        }

        foreach ($updateExceptKeys as $updateExceptKey) {
            unset($insertData[$updateExceptKey]);
        }

        foreach ($insertData as $field => $value) {
            //判断字段数据类型的合法性，不能是array和object
            static::checkFieldValue($field, $value);
            //忽略的字段不给予录入
            if (static::isIgnoreField($field)) continue;
            $model->{$field} = static::setValue($value);
        }

        $model->save();

        return $model;
    }


    /**
     * 根据主键更新数据
     * @param array $data
     * @return $this
     */
    public function modify(array $data): self
    {
        foreach ($data as $key => $val) {
            $this->{$key} = trim($val);
        }
        $this->save();

        return $this;
    }

    /**
     * 检查数据唯一，并且修改
     *
     * @param array $data 变更的数据数组
     * @param array $uniqueFields 需要做唯一校验的字段
     * @return static
     * @throws \Exception
     */
    public function checkUniqueAndModify(array $data, array $uniqueFields): self
    {

        foreach ($uniqueFields as $uniqueField) {
            if (!isset($data[$uniqueField])) {
                throw new \Exception('数据中缺少必须的字段', -1);
            }
        }
        $cond = Arr::only($data, $uniqueFields);

        $num = static::where($cond)->where('id', '<>', $this->getKey())->count();
        if ($num) {
            Logger::error('重复数据', $data, $uniqueFields);
            throw new \Exception('重复的记录，修改失败', -1);
        }

        return $this->modify($data);
    }

    /**
     * 设置树形结构的ID路径 返回根式为0,1,2,3这样的值
     *
     * @param array $opts 选项 parentField-表示上下级的字段名称(默认：parent_id)，keyField-表示主键的字段名(默认：id)，pathField-表示存储路径的字段名(默认：id_path)
     * @return bool
     */
    public function setTreePath(array $opts = []): bool
    {
        $defaultOpts = [
            'parentField' => 'parent_id',
            'keyField' => 'id',
            'pathField' => 'id_path'
        ];
        $defaultOpts = array_merge($defaultOpts, $opts);

        if (empty($this->{$defaultOpts['parentField']})) {
            $this->{$defaultOpts['pathField']} = '0,' . $this->{$defaultOpts['keyField']};
        } else {
            $parentIdPath = static::where($defaultOpts['keyField'], $this->{$defaultOpts['parentField']})->value($defaultOpts['pathField']);
            $this->{$defaultOpts['pathField']} = $parentIdPath . ',' . $this->{$defaultOpts['keyField']};
        }
        return $this->save();
    }

    /**
     * 获取一条数据，转化为自定义数组结果
     *
     * @param array $opts
     * @return array
     */
    public function toCustomArray(array $opts = []): array
    {
        $row = $this->toArray();

        $defaultOpts = [
            'isCastFileUrl' => true,   //结果是否要对文件相关的字段进行转化为绝对路径
            'castFileUrlMode' => 1,     //转化为绝对路径的方式 1-新增一个字段，以absolute_作为前缀(合适后台的程序) 2-覆盖原字段值（合适展示的前端程序）
            'fileFields' => [           //表示文件的字段
                'thumb',
                'img_url',
                'image',
                'imgUrl',
                'ImgUrl',
                'Image',
                'Thumb',
                'file_url',
                'file',
                'avatar'
            ],
            'isExceptTime' => true,     //是否排除系统自带的表示时间的字段
            'isCastSwitch' => false,    //要把开关性值的值转化为boolean值
            'switchFields' => 'status', //表示开关的字段
            'exceptFields' => null      //要排除的字段值
        ];
        $defaultOpts = array_merge($defaultOpts, $opts);

        //字符串转成数组
        foreach (['fileFields', 'switchFields', 'exceptFields'] as $opt) {
            if (!is_string($defaultOpts[$opt])) continue;
            $defaultOpts[$opt] = explode(',', $defaultOpts[$opt]);
        }

        //排除系统自带的表示时间的字段
        if ($defaultOpts['isExceptTime']) {
            $row = Arr::except($row, (new static())->getDates());
        }

        //排除自定义的字段
        if ($defaultOpts['exceptFields']) {
            $row = Arr::except($row, $defaultOpts['exceptFields']);
        }

        foreach ($row as $key => &$value) {
            //文件URL处理
            if ($defaultOpts['isCastFileUrl'] && in_array($key, $defaultOpts['fileFields'])) {
                $absolute = Url::imgUrl($value);
                //以添加新字段方式
                if ($defaultOpts['castFileUrlMode'] === 1) {
                    $key = 'full_' . $key;
                    $row[$key] = $absolute;
                    continue;
                }

                //覆盖的方式转化
                if ($defaultOpts['castFileUrlMode'] === 2) {
                    $value = $absolute;
                    continue;
                }
            }

            //将开关属性值转化为boolean
            if ($defaultOpts['isCastSwitch'] && in_array($key, $defaultOpts['switchFields'])) {
                $value = boolval($value);
            }
        }

        return $row;
    }

    /**
     * 开关切换
     *
     * @param string $attr 开关字段名称
     * @return bool
     */
    public function trigger(string $attr): bool
    {
        $this->{$attr} = intval(!$this->{$attr});

        return $this->save();
    }
}
