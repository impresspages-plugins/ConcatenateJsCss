<?php
/**
 * @package   ImpressPages
 */


/**
 * Created by PhpStorm.
 * User: mangirdas
 * Date: 14.11.21
 * Time: 15.58
 */

namespace Plugin\ConcatenateJsCss;


class Event {
    public static function ipCacheClear()
    {
        $dir = ipFile('file/concatenate');
        $cacheFiles = scandir($dir);

        foreach($cacheFiles as $file) {
            if (in_array($file, array('.', '..'))) {
                continue;
            }

            if (is_writable($dir . '/' . $file)) {
                unlink($dir . '/' . $file);
            }
        }
    }

}
