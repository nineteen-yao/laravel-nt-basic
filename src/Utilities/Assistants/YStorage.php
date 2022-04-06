<?php
/**
 * @author: YaoFei<nineteen.yao@qq.com>
 * Datetime: 2020/11/23 9:41
 */

namespace YLarNtBasic\Utilities\Assistants;

use Illuminate\Support\Facades\Storage;
use function abort;
use function app;
use function env;

class YStorage
{


    /**
     * 文件下载
     * @param string $filepath
     * @param string $outname
     * @return void
     */
    public static function download(string $filepath, string $outname = '')
    {
        if (empty($filepath) || !file_exists($filepath)) {
            abort(404);
            return;
        }

        $destPath = $abDestPath = $filepath;

        if (!Storage::exists($filepath)) {
            $destDir = 'temp/';
            Storage::makeDirectory($destDir);
            $destPath = $destDir . uniqid() . '-' . basename($filepath);
            $abDestPath = Storage::path($destPath);
            @copy($filepath, $abDestPath);
        }

        $outname = $outname ?? basename($abDestPath);

        //编码后
        $encodedFilename = urlencode($outname);
        $encodedFilename = str_replace("+", "%20", $encodedFilename);


        //识别浏览器
        $userAgent = app()->request->userAgent();
        $headers = [];

        //兼容IE11
        if (preg_match("/MSIE/", $userAgent) || preg_match("/Trident\/7.0/", $userAgent)) {
            $headers['Content-Disposition'] = 'attachment; filename="' . $encodedFilename . '"';
            $headers['filename'] = $encodedFilename;
        } elseif (preg_match("/Firefox/", $userAgent)) {
            $headers['Content-Disposition'] = 'attachment; filename*="utf8\'\'' . $outname . '"';
            $headers['filename'] = $outname;
        } else {
            $headers['Content-Disposition'] = 'attachment; filename="' . $outname . '"';
            $headers['filename'] = $outname;
        }

        //中文名有乱码
        unset($headers['filename']);


        return Storage::download($destPath, null, $headers);
    }
}
