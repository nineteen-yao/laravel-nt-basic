<?php
/**
 * FetcherBase.php
 *
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2021/9/30 11:33
 */

namespace YLarNtBasic\Utilities;


use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use YLarNtBasic\Utilities\Assistants\Arr;
use Ynineteen\Supports\DTime;
use Ynineteen\Supports\Logger;

abstract class FetcherBase
{
    /**
     * @var string|null $headerStr 个性化header，继承类根据需要调整
     */
    protected $headerStr = null;
    /**
     * @var string|null $individualHeaderStr 独立的个性化header，此变量有值，将会忽略headerstr参数
     */
    protected $individualHeaderStr = null;

    /**
     * @var Client $httpClient
     */
    protected $httpClient;

    protected $config = [
        //请求超时时间
        'timeout' => 5,
        //请求的时候，是否需要上锁
        'lock' => false
    ];

    /**
     *
     * @var string $appKey 应用标志ID，cookie依据此ID进行保存,分布式锁也将采用这个属性
     */
    protected $appKey;

    protected function __construct($config = [])
    {
        if (empty($this->appKey)) {
            $this->appKey = strtolower(Str::snake(class_basename(get_called_class())));
        }

        $this->setConfig($config);

        $options = Arr::only($this->config, ['timeout', 'base_uri']);
        $this->httpClient = new Client($options);
    }

    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function login()
    {
        //继承重写实现,这只是打个桩而已
    }

    /**
     * 统一抓取数据方法
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function fetch(string $method, string $uri, array $options = []): ResponseInterface
    {
        $response = $this->httpClient->request($method, $uri, $options);
        $this->headerStr = null;
        $this->individualHeaderStr = null;
        if ($response->getStatusCode() !== 200) {
            Logger::error($response->getReasonPhrase(), $uri, $options);
            throw new \Exception('接口抓取失败', -1);
        }

        return $response;
    }

    /**
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpGet(string $uri, array $options = []): ResponseInterface
    {
        return $this->fetch('GET', $uri, $options);
    }

    /**
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPost(string $uri, array $options = []): ResponseInterface
    {
        return $this->fetch('POST', $uri, $options);
    }

    /**
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function httpPut(string $uri, array $options = []): ResponseInterface
    {
        return $this->fetch('PUT', $uri, $options);
    }


    public function httpOptions(array $options): array
    {
        $res = [];
        foreach ($options as $key => $option) {
            if (!empty($option)) {
                $res[$key] = $option;
            }
        }

        return $res;
    }

    /**
     * cookie的缓存与从缓存读取
     *
     * @param array $items
     * @return array|mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function cookies(array $items = [])
    {
        $cacheKey = 'fetch:' . $this->appKey;

        //cookie解析
        $cookies = [];
        $expires = [];
        //当$items不为空的时候，设置有效时间存储
        foreach ($items as $k => $cookieStr) {
            //已经切割好的，原值返回
            if (!is_string($cookieStr)) {
                $cookies[$k] = $cookieStr;
                continue;
            }

            $map = [];
            //切割cookie
            $cookieKey = '';
            foreach (explode(';', $cookieStr) as $i => $item) {
                $item = trim($item);
                //当为会话时候
                if (strtolower($item) === 'httponly') {
                    $map[$item] = true;
                    $map['expires'] = DTime::time() + 86400;
                    continue;
                }

                list($key, $value) = explode('=', $item);
                $key = trim($key);
                if ($i === 0) {
                    $cookieKey = $key;
                    $map['value'] = $value;
                    continue;
                }
                //有效期处理
                if ($key === 'expires') {
                    $value = empty($value) ? 0 : strtotime($value);
                }
                $map[$key] = $value;
            }
            //补全有效期
            if (empty($map['expires'])) {
                $map['expires'] = 0;
            }

            $expires[] = $map['expires'];

            $cookies[$cookieKey] = $map;
        }

        //设置缓存
        if (!empty($cookies)) {
            //最小有效时间
            sort($expires, SORT_NUMERIC);
            $minExpires = $expires[0];

            Cache::set($cacheKey, json_encode([
                'data' => $cookies,
                'expires' => $minExpires,
                'time' => DTime::getTimestamp(DTime::tomorrow())
            ], JSON_UNESCAPED_UNICODE));

            return $cookies;
        }


        /////////// 获取缓存cookie

        $cacheStr = Cache::get($cacheKey);
        if (empty($cacheStr)) {
            return $items;
        }
        $cacheData = json_decode($cacheStr, true);
        //不是当日生成的cookie，直接失效
        if (DTime::time() > ($cacheData['time'] ?? 0)) {
            return $items;
        }

        //cookie差30分钟就过期，直接失败
        if ((DTime::time() + 1800) > $cacheData['expires']) {
            return $items;
        }

        return $cacheData['data'];
    }

    /**
     * 获取当个cookie值
     * @param string $key
     * @return mixed|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getCookie(string $key)
    {
        $cookies = $this->cookies();
        if (empty($cookies[$key])) {
            return null;
        }

        return $cookies[$key]['value'];
    }

    /**
     * 获取cookiejar
     *
     * @param string $domain
     * @return false|CookieJar
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function cookieJar(string $domain)
    {
        $cookies = $this->cookies();
        if (empty($cookies)) {
            $this->login();
        }
        $cookies = $this->cookies();
        if (empty($cookies)) {
            throw new \Exception('cookie已失效', -1);
        }

        $jars = [];
        foreach ($cookies as $key => $cookie) {
            $jars[$key] = $cookie['value'];
        }

        return CookieJar::fromArray($jars, $domain);
    }

    /**
     * 设置头信息
     *
     * @param array $options
     * @return array
     */
    public function headers(array $options = []): array
    {
        $headerStr = '
Accept: */*
Accept-Encoding: gzip, deflate
Accept-Language: zh-CN,zh;q=0.9,en;q=0.8
Cache-Control: max-age=0
Connection: keep-alive
Upgrade-Insecure-Requests: 1
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4606.61 Safari/537.36
        ';

        if (!empty($this->individualHeaderStr)) {
            $headerStr = $this->individualHeaderStr;
        } else {
            if (!empty($this->headerStr)) {
                $headerStr .= trim($this->headerStr);
            }
        }

        $headers = static::strToMap($headerStr);

        return array_merge($headers, $options);
    }

    /**
     * 从给出的指定URL中，解析URL的HOST，并且返回
     *
     * @param string $api
     * @param string $baseUri
     * @return string
     * @throws \Exception
     */
    public static function parseHost(string $api, string $baseUri = ''): string
    {
        foreach ([$api, $baseUri] as $url) {
            $values = parse_url($url);
            if (!empty($values['host'])) {
                return $values['host'];
            }
        }
        throw new \Exception('为解析到URL的HOST', -1);
    }

    /**
     * 获取默认HOST值
     *
     * @param string $api
     * @return string
     * @throws \Exception
     */
    public function getHost(string $api): string
    {
        $baseUri = !empty($this->config['base_uri']) ? $this->config['base_uri'] : '';
        return static::parseHost($api, $baseUri);
    }

    /**
     * 将字符串的键值对形式，转化为MAP键值对数组
     *
     * @param string $str
     * @return array
     */
    public static function strToMap(string $str): array
    {
        $map = [];
        foreach (explode(PHP_EOL, trim($str)) as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (Str::startsWith($line, ':')) {
                list($key, $value) = explode(':', substr($line, 1), 2);
                $map[':' . trim($key)] = trim($value);
            } else {
                list($key, $value) = explode(':', $line, 2);
                $map[trim($key)] = trim($value);
            }
        }

        return $map;
    }

    /**
     * 给指定文件名自动编号，如果文件名已经存在，那么自动加上编号
     *
     * @param string $basename 基本名称
     * @param bool $autoIncrease 文件名若存在，是否自动加上编号
     * @return string
     */
    public static function numberFileName(string $basename, bool $autoIncrease = false): string
    {
        $ext = static::parseExt($basename);

        $getName = function ($n) use ($basename, $ext, $autoIncrease) {
            $prefix = trim(strstr($basename, '.', true), '.') . '-' . DTime::today('');
            $suffix = '.' . $ext;
            if (!$autoIncrease) {
                return $prefix . $suffix;
            }

            return ($prefix . '-' . $n . $suffix);
        };

        if (!$autoIncrease) {
            return $getName(0);
        }

        $n = 1;

        while (Storage::exists($filename = $getName($n))) {
            $n++;
        }

        return $filename;
    }

    /**
     * 查找匹配的内容
     * @param string $contents
     * @param string $pattern
     * @param string $startIdentifier
     * @param string $endIdentitifier
     * @return array
     */
    public static function matchAll(string $contents, string $pattern, string $startIdentifier = '', string $endIdentitifier = ''): array
    {
        $areaContents = static::between($contents, $startIdentifier, $endIdentitifier);

        if (preg_match_all($pattern, $areaContents, $parentMatches)) {
            return $parentMatches;
        }
        return [];
    }

    /**
     * 选取匹配的区间字符串
     * @param string $contents
     * @param string $startIdentifier
     * @param string $endIdentitifier
     * @return string
     * @throws \Exception
     */
    public static function between(string $contents, string $startIdentifier, string $endIdentitifier): string
    {
        $startIdentifier = trim($startIdentifier);
        $endIdentitifier = trim($endIdentitifier);

        if (empty($startIdentifier) || empty($endIdentitifier)) {
            throw new \Exception('开始结束标记符不能为空，start:' . $startIdentifier . '，end:' . $endIdentitifier, -1);
        }

        $startIndex = strpos($contents, $startIdentifier);
        if ($startIndex === false) {
            return '';
        }

        $endIndex = strpos($contents, $endIdentitifier, $startIndex);
        if ($endIndex === false) {
            return '';
        }

        return substr($contents, $startIndex, $endIndex - $startIndex);
    }

    /**
     * 获取一个文件后缀名
     *
     * @param string $filename
     * @return string
     */
    public static function extension(string $filename): string
    {
        return trim(strrchr($filename, '.'), '.');
    }

    /**
     * 将格式化的数字字符串，转化为数字
     * demo：12,000 --> 12000
     * @param string $formatNumber
     * @return int
     */
    public static function toNumber(string $formatNumber): int
    {
        return intval(str_replace([',', '，'], '', $formatNumber));
    }

    /**
     * 报错处理
     *
     * @param array|string $return 内容
     * @param bool $jsonToArray 是否将数据转化为数组
     */
    public function error($return, bool $jsonToArray = false)
    {
        //继承重写实现,这只是打个桩而已
    }
}
