<?php
/**
 * @package   ImpressPages
 */


/**
 * Created by PhpStorm.
 * User: mangirdas
 * Date: 14.11.13
 * Time: 23.13
 */

namespace Plugin\ConcatenateJsCss;


class Model
{

    public static function concatenateCss($urls)
    {
        return self::concatenate($urls, 'css');
    }

    public static function concatenateJs($urls)
    {
        return self::concatenate($urls, 'js');
    }

    protected static function concatenate($urls, $ext)
    {
        $key = md5(json_encode($urls));
        $path = 'file/concatenate/' . $key . '.' . $ext;

        if (!is_file(ipFile($path))) {
            $concatenated = '';

            foreach ($urls as $url) {
                $urlContent = self::fetchContent($url);
                if ($urlContent === false) {
                    //break if at least one of the assets can't be downloaded
                    return false;
                }

                if ($ext == 'css') {
                    $urlContent = self::replaceUrls($url, $urlContent);
                }

                if ($ext == 'js') {
                    $urlContent .= ';';
                }

                $concatenated .= "\n" . $urlContent;
            }

            if (!is_dir(ipFile('file/concatenate'))) {
                mkdir(ipFile('file/concatenate'));
            }
            file_put_contents(ipFile($path), $concatenated);
        }

        return ipFileUrl($path);

    }

    protected static function replaceUrls($originalUrl, $css)
    {
        $pathInfo = pathinfo($originalUrl);
//        $search = ''#url\((?!\s*([\'"]?(((?:https?:)?//)|(?:data\:?:))))\s*([\'"])?#'';
        $path = $pathInfo['dirname'] . '/';
        $absoluteUrl = '((?:https?:)?//)';
        $rawData = '(?:data\:?:)';
        $relativeUrl = '\s*([\'"]?((' . $absoluteUrl . ')|(' . $rawData . ')))';
        $search = '#url\((?!' . $relativeUrl . ')\s*([\'"])?#';
        $replace = "url($6{$path}";
        return preg_replace($search, $replace, $css);
    }


    protected static function fetchContent($url)
    {
        if (preg_match('%^\/\/%', $url)) {
            $url = 'http:' . $url;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION,3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $content = curl_exec ($ch);
        $error = curl_error($ch);
        if ($error) {
            ipLog()->debug('ConcatenateJsCss: download asset error', array('url' => $url, 'error' => $error));
            return false;
        }
        curl_close ($ch);

        return $content;
    }
}
