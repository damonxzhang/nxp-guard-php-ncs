<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/font.css',
        'css/xadmin.css',
        'css/common.css',
        'lib/layui/css/layui.css',
    ];
    public $js = [
        'lib/layui/layui.js',
        'lib/layui/layui.all.js',
        'js/xadmin.js',
        'js/common.js',
        'js/cookie.js',
        'js/yii.js',
        'lib/tableSelect.js',
        'js/EZView.js',
        'js/draggable.js',
        'js/excel.js',
        'js/exif.js',
        'js/xm-select.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
