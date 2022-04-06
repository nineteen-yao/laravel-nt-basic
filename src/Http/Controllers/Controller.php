<?php

namespace YLarNtBasic\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use YLarNtBasic\Utilities\Traits\JsonResponse;
use Ynineteen\Supports\Logger;
use function request;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, JsonResponse;

    /**
     * 统一异常捕捉
     * @param string $method
     * @param array $parameters
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        try {
            return parent::callAction($method, $parameters);
        } catch (\Throwable $throwable) {
            Logger::error(static::class . '::' . $method, $parameters, '错误信息:' . $throwable->getMessage());
            Logger::debug(static::class . '::' . $method, $throwable);

            return $throwable->getMessage();
        }
    }

    protected function pageParam()
    {
        return [
            'page' => request()->get('page', 1),
            'limit' => request()->get('limit', null)    //默认长度使用系统配置
        ];
    }
}
