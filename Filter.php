<?php
/**
 * @package   ImpressPages
 */


/**
 * Created by PhpStorm.
 * User: mangirdas
 * Date: 14.11.14
 * Time: 19.44
 */
namespace Plugin\ConcatenateJsCss;



class Filter
{
    public static function ipCss($cssFiles)
    {
        if (ipGetOption('ConcatenateJsCss.disableInAdmin', 1) && ipAdminId()) {
            return $cssFiles;
        }

        $urls = array();
        foreach ($cssFiles as &$file) {
            $urls [] = $file['value'];
        }

        $concatenatedCss = Model::concatenateCss($urls);

        if (!$concatenatedCss) {
            //concatenation failed. Return original CSS files
            return $cssFiles;
        }

        return array(
            $concatenatedCss => array(
                'value' => $concatenatedCss,
                'attributes' => array(),
                'cacheFix' => true
            )
        );
    }


    public static function ipJs($jsFiles)
    {
        if (ipGetOption('ConcatenateJsCss.disableInAdmin', 1) && ipAdminId()) {
            return $jsFiles;
        }

        $tinymceUrl = ipFileUrl('Ip/Internal/Core/assets/js/tiny_mce');

        $answer = array(
            'concatenateJsCss_tinymce_fix' => array(
                'type' => 'content',
                'value' => "var tinyMCEPreInit = {
    suffix: '.min',
    base: '" . $tinymceUrl . "',
    query: ''
};",
                'attributes' => array(),
                'cacheFix' => false
            )
        );


        $chunk = array();


        foreach ($jsFiles as &$file) {
            if ($file['type'] == 'content') {
                //we have faced a piece of inline JS. It can't be concatenated. We have to split concatenated JS in to two parts.

                if (!empty($chunk)) {
                    $answer = array_merge($answer, self::concatenateChunk($chunk));
                }
                $chunk = array();
                //add current inline content JS
                $answer[] = $file;
            } else {
                $chunk[] = $file;
            }

        }

        if (!empty($chunk)) {
            $answer = array_merge($answer, self::concatenateChunk($chunk));
        }
        return $answer;
    }


    protected static function concatenateChunk($chunk)
    {
        $urls = array();
        foreach ($chunk as $item) {
            $urls[] = $item['value'];
        }
        //concatenate all JS we already faced
        $concatenatedJs = Model::concatenateJs($urls);
        if (!$concatenatedJs) {
            return $chunk;
        }

        $concatenatedChunk = array(
            $concatenatedJs => array(
                'type' => 'file',
                'value' => $concatenatedJs,
                'attributes' => array(),
                'cacheFix' => true
            )
        );

        return $concatenatedChunk;
    }
}
