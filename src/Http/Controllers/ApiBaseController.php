<?php
/**
 * API基础核心类
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021-03-10 10:08
 */


namespace YLarNtBasic\Http\Controllers;


use Illuminate\Http\Request;
use YLarNtBasic\Model\BaseModel;
use Ynineteen\Supports\Logger;

abstract class ApiBaseController extends Controller
{
    public function callAction($method, $parameters)
    {
        return $this->success(parent::callAction($method, $parameters));
    }
}
