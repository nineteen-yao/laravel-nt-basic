<?php
/**
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2020/6/5 17:46
 */

namespace YLarNtBasic\Utilities\Traits;


use function auth;
use function response;

trait JsonResponse
{
    protected $errMsg = '';

    protected function jsonResponse($code = 200, $msg = 'ok', $data = null)
    {
        $ret = [
            'status' => $code,
            'msg' => $msg,
        ];

        if ($data !== null) {
            $ret['data'] = $data;
        }

        return response()->json($ret);
    }

    public function success($data = null)
    {
        return $this->jsonResponse(0, 'ok', $data);
    }

    public function fail($msg = 'Error', $code = -1, $data = null)
    {
        if ($code == 0) {
            $code = -1;
        }
        return $this->jsonResponse($code, $msg, $data);
    }

    /**
     * 输出JWT的token数据
     * @param string $token
     * @return array
     */
    public function respondJwtToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }

    /**
     * 返回guard对象
     * @param $guardName
     * @return \Illuminate\Contracts\Auth\Factory|\Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    public function jwtGuard($guardName)
    {
        if (empty($guardName)) {
            return auth();
        }

        $prefix = 'jwt-';
        if (substr(strtolower($guardName), 0, 4) !== $prefix) {
            $guardName = $prefix . $guardName;
        }

        return auth($guardName);
    }
}
