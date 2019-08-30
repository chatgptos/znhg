<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2017/12/20
 * Time: 14:06
 */

namespace app\extensions;


class GrafikaHelper
{
    /**
     * 获取支持的图片处理库
     * @return array
     */
    public static function getSupportEditorLib()
    {
        if (class_exists('\Imagick')) {
            $tmp_imagick = new \Imagick();
            if (method_exists($tmp_imagick, 'setImageOpacity')) {
                $editor_lib = ['Imagick'];
            } else {
                $editor_lib = ['Gd'];
            }
        } else {
            $editor_lib = ['Gd'];
        }
        return $editor_lib;
    }
}