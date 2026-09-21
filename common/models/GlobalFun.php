<?php
namespace common\models;

class GlobalFun{

    /**
     * 将$model->getErrors得到的错误信息转换
     * @param $errors
     * @return string
     */
    public static function errorToString($errors){

        if(empty($errors)){
            return '未定义错误！';
        }
        $errorStr = '';
        foreach ($errors as $value){

            $errorStr .=  implode("\r\n",$value)."<br />";
        }

        return trim($errorStr,',');
    }


    /**
     * 检查远程文件会否存在
     * @param $url
     * @return bool
     */
    public static function file_url_exists($url){

        if(empty($url) || !is_string($url)){
            return false;
        }

        if(strpos($url,'http') === false){
            $url = \Yii::$app->urlManager->getHostInfo().$url;
        }
        $header = @get_headers($url);
        if(isset($header[0]) && (strpos($header[0], '200') || strpos($header[0], '304'))){
            return true;
        }
        return false;
    }


}
