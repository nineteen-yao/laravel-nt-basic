<?php
/**
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2020/12/29 17:23
 */


namespace YLarNtBasic\Utilities\Assistants;


use Illuminate\Support\Str;
use YLarNtBasic\Utilities\Oss\Oss;

class Url
{
    /**
     * 根据一个相抵url返回一个绝对url
     * @param $url
     * @param $host
     * @return string
     */
    public static function absolute($url, $host): string
    {
        if (empty($url)) {
            return '';
        }

        if (Str::contains($url, '://')) {
            return $url;
        }

        $imgHost = trim(env($host, ''));

        //判断是否云存储路径
        if (Str::startsWith($url, 'oss=')) {
            $ossConnect = mb_substr(strstr($url, '/', true), 4);
            $ossDomain = Oss::getDomain($ossConnect);
            if ($ossDomain) {
                $imgHost = $ossDomain;
            }

            $url = strstr($url, '/');
        }

        if (!$imgHost) {
            return $url;
        }

        return rtrim($imgHost, '/') . Str::start($url, '/');
    }

    /**
     * 返回图片的绝对地址
     * @param $url
     * @return string
     */
    public static function imgUrl($url): string
    {
        return static::absolute($url, 'IMG_HOST');
    }

    /**
     * 返回图片的绝对地址
     * @param $url
     * @return string
     */
    public static function staticUrl($url): string
    {
        return static::absolute($url, 'STATIC_HOST');
    }

    /**
     * 重建url的QueryString
     *
     * @param string $url
     * @param array $set
     * @return string
     */
    public static function rebuildQeury(string $url, array $set): string
    {
        $url = urldecode($url);

        $info = parse_url($url);

        $data = [];
        if (!empty($info['query'])) {
            foreach (explode('&', $info['query']) as $map) {
                list($key, $value) = explode('=', $map);
                $data[$key] = $set[$key] ?? $value;
            }
        }
        $data = array_merge($data, $set);

        if (!empty($data)) {
            $items = [];
            foreach ($data as $key => $value) {
                $items[] = $key . '=' . $value;
            }

            $info['query'] = implode('&', $items);
        }

        $newUrl = '';
        if (!empty($info['scheme'])) {
            $newUrl .= $info['scheme'] . '://';
            if (!empty($info['user'])) {
                $newUrl .= $info['user'];
                if (!empty($info['pass'])) {
                    $newUrl .= ':' . $info['pass'];
                }
                $newUrl .= '@';
            }
            $newUrl .= $info['host'];

            if (!empty($info['port'])) {
                $newUrl .= ':' . $info['port'];
            }
        }
        if (!empty($info['path'])) {
            $newUrl .= $info['path'];
        }
        if (!empty($info['query'])) {
            $newUrl .= '?' . $info['query'];
        }

        if (!empty($info['fragment'])) {
            $newUrl .= '#' . $info['fragment'];
        }


        return $newUrl;
    }
}
