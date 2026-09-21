<?php

namespace common\models;

use yii\helpers\Json;

header('Content-type:text/html; Charset=utf-8');
date_default_timezone_set('PRC');

class Haikang
{
    public    $pre_url      = "https://10.192.147.24:443";
    protected $app_key      = "24008499";
    protected $app_secret   = "5QfTKaImE2cRPLWbyvOp";
    public    $content_type = "application/json";//json类型
    public    $accept       = "*/*";
    public    $time;
    public    $charset;

    public $list_url = [

        'api/orgList' => "/artemis/api/resource/v1/org/orgList",      //获取组织列表
        'person/add'  => "/artemis/api/resource/v1/person/single/add",//添加人员
        'person/delete'  => "/artemis/api/resource/v1/person/batch/delete",//批量删除人员

        'person/check'  => "/artemis/api/resource/v2/person/advance/personList",//查询人员
        'single/update' => "/artemis/api/resource/v1/face/single/update",       //修改人脸
        'single/add'    => "/artemis/api/resource/v1/face/single/add",          //添加人脸
        'single/delete'    => "/artemis/api/resource/v1/face/single/delete",    //删除人脸

        'task/simpleDownload' => "/artemis/api/acps/v1/authDownload/task/simpleDownload",      //简单同步权限下载

        'task/addition'     => "/artemis/api/acps/v1/authDownload/task/addition",//创建下载任务_根据人员与设备通道指定下载
        'task/dataAddition' => "/artemis/api/acps/v1/authDownload/data/addition",//下载任务中添加数据_根据人员与设备通道指定下载
        'task/start'        => "/artemis/api/acps/v1/authDownload/task/start",   //开始下载任务
        'task/progress'     => "/artemis/api/acps/v1/authDownload/task/progress",//查询下载任务进度

        'auth/add' => "/artemis/api/acps/v1/auth_config/add",            //出入权限配置
        'shortcut' => "/api/acps/v1/authDownload/configuration/shortcut",//根据出入权限配置快捷下载
    ];

    public         $date  = null;
    private static $redis = null;
    const CACHE_ACCESS_TOKEN = "access_token";

    public function __construct($app_key = '', $app_secret = '')
    {
        if ($app_key != '')
            $this->app_key = $app_key;
        if ($app_secret != '')
            $this->app_secret = $app_secret;
        $this->charset = 'utf-8';
        list($msec, $sec) = explode(' ', microtime());
        $this->time = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }

    /**
     * 当前时间
     * @return false|string|null
     */
    public function getDate()
    {
        if (!$this->date)
            $this->date = date("Y-m-d H:i:s");
        return $this->date;
    }

    /**
     * Headers
     * @param $sign
     * @return array
     */
    public function getOptions($sign)
    {
        return [CURLOPT_HTTPHEADER => [
            "userId:admin",
            "HTTP METHOD:POST",
            "Accept:" . $this->accept,
            "Content-Type:" . $this->content_type,
            "Date:" . $this->getDate(),
            "x-Ca-Key:" . $this->app_key,
            "X-Ca-Signature:" . $sign,
            "X-Ca-Signature-Headers: x-ca-key"
        ]
        ];
    }

    /**
     * 以appSecret为密钥，使用HmacSHA256算法对签名字符串生成消息摘要，对消息摘要使用BASE64算法生成签名（签名过程中的编码方式全为UTF-8）
     * @param $url
     * @return string
     */
    function getSign($url)
    {
        $next     = "\n";
        $sign_str = "POST" . $next . $this->accept . $next . $this->content_type . $next . $this->getDate() . $next;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     //httpHeaders 
        $sign_str .= "x-ca-key:" . $this->app_key . $next;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               //customHeaders 
        $sign_str .= $url;
        $sign     = hash_hmac('sha256', $sign_str, $this->app_secret, true);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 //生成消息摘要
        $result   = base64_encode($sign);
        return $result;
    }


    /**
     * 发送Post请求
     * @param string $url
     * @param string $postData
     * @param array $options
     * @return bool|string
     */
    public function curlPost($url = '', $postData = '', $options = [])
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //至关重要，CURLINFO_HEADER_OUT选项可以拿到请求头信息
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $data = curl_exec($ch);
        //echo "<pre>";var_dump(curl_getinfo($ch));exit;
        curl_close($ch);
        return $data;
    }


    /**
     *
     *
     *
     *
     * 人员管理接口
     *
     *
     *
     **/

    /**
     * 添加人员
     * @param $data //人员信息
     * @return mixed
     */
    public function addPersion($data = [])
    {
        $postData = [
            "personName"      => $data['name'],
            "gender"          => "0",
            "orgIndexCode"    => "1adb0a6a-095e-42ab-88aa-be8c77635549",//person组织
            "birthday"        => "2000-01-01",
            "phoneNo"         => "11111111111",
            "email"           => "person@person.com",
            "certificateType" => "111",
            "certificateNo"   => $data['idCard'],
            "jobNo"           => "111111",
            "faces"           => [["faceData" => $data['photo']]],
        ];
        $url      = $this->list_url['person/add'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        return Json::decode($post);
    }

    /**
     * 查询人员
     * @param $data //人员信息
     * @return mixed
     */
    public function checkPerson($data = [])
    {
        $postData = [
            "personName"      => $data['name'],
            "orgIndexCode"    => "1adb0a6a-095e-42ab-88aa-be8c77635549",//person组织
            // "certificateType" => 111,
            // "certificateNo"   => $data['idCard'],
            "pageNo"          => "1",
            "pageSize"        => "10",
        ];
        $url      = $this->list_url['person/check'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        return Json::decode($post);
    }

    /**
     * 删除人员
     * @param $personIds //人员信息
     * @return mixed
     */
    public function deletePerson($personIds = [])
    {
        $postData = [
            "personIds"      => $personIds
        ];
        $url      = $this->list_url['person/delete'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        return Json::decode($post);
    }


    /**
     * 添加人脸
     * @param array $data
     * @return mixed|null
     */
    public function singleAdd($data = [])
    {
        $postData = [
            "personId" => $data['personId'],
            "faceData" => $data['photo']
        ];
        $url      = $this->list_url['single/add'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        return Json::decode($post);
    }

    /**
     * 修改人脸
     * @param array $data
     * @return mixed|null
     */
    public function singleUpdate($data = [])
    {
        $postData = [
            "faceId"   => $data['faceId'],
            "faceData" => $data['photo']
        ];
        $url      = $this->list_url['single/update'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        return Json::decode($post);
    }


    /**
     * 删除人脸
     * @param string $faceId
     * @return mixed|null
     */
    public function singleDelete($faceId)
    {
        $postData = [
            "faceId" => $faceId
        ];
        $url      = $this->list_url['single/delete'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        return Json::decode($post);
    }

    /**
     *
     *
     *
     *
     * 门禁下发接口
     *
     *
     *
     **/


    /**
     * 简单同步权限下载_根据人员与设备通道指定下载
     * @param $indexCode    //门禁的indexCode
     * @param $personId     //人员Id
     * @param $operatorType //操作类型，0新增；1修改；2删除
     * @param $startTime //开始时间
     * @param $endTime //截止时间
     * @return mixed
     */
    public function simpleDownload($indexCode, $personId, $operatorType, $startTime, $endTime)
    {
        $postData = [
            "taskType"     => "5",//卡片+人脸（组合）
            "resourceInfo" => [
                "channelNos"        => [1],
                "resourceIndexCode" => $indexCode,
                "resourceType"      => "door"
            ],
            "personInfo"   => [
                "startTime"    => $startTime,
		        "endTime"      => $endTime,
                "personId"     => $personId,
                "operatorType" => $operatorType,
                "personType"   => "1",
            ],
        ];
        $url      = $this->list_url['task/simpleDownload'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $result   = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        return Json::decode($result);
    }

    /**
     * 创建下载任务_根据人员与设备通道指定下载
     * @return false|mixed
     */
    public function taskAddition()
    {
        $postData = [
            "taskType" => "5",//卡片+人脸（组合）
        ];
        $url      = $this->list_url['task/addition'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        $result   = Json::decode($post);
        return $result['code'] == 0 ? $result['data']['taskId'] : false;
    }

    /**
     * 下载任务中添加数据_根据人员与设备通道指定下载
     * @param $taskId    //上一步获取的$taskId
     * @param $indexCode //门禁的indexCode
     * @param $personId  //人员Id
     * @return bool
     */
    public function dataAddition($taskId, $indexCode, $personId)
    {
        $postData = [
            "taskId"        => $taskId,
            "resourceInfos" => [
                "channelNos"        => [1],
                "resourceIndexCode" => $indexCode,
                "resourceType"      => "door"
            ],
            "personInfos"   => [
                "personId"     => $personId,
                "operatorType" => "0",
                "personType"   => "1",
            ],
        ];
        $url      = $this->list_url['task/dataAddition'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        $result   = Json::decode($post);
        return $result['code'] == 0 ? true : false;
    }

    /**
     * 开始下载任务
     * @param $taskId
     * @return bool
     */
    public function taskStart($taskId)
    {
        $postData = [
            "taskId" => $taskId,
        ];
        $url      = $this->list_url['task/start'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        $result   = Json::decode($post);
        return $result['code'] == 0 ? true : false;
    }

    /**
     * 查询下载任务进度
     * @param $taskId
     * @return bool
     */
    public function taskProgress($taskId)
    {
        $postData = [
            "taskId" => $taskId,
        ];
        $url      = $this->list_url['task/progress'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        return Json::decode($post);
    }


    /**
     * 出入权限配置
     * @param $orgIndexCode
     * @param $indexCode
     * @return false|mixed
     */
    public function authAdd($orgIndexCode, $indexCode)
    {
        $postData = [
            "personDatas"   => [
                "indexCodes"     => [$orgIndexCode],
                "personDataType" => "org"
            ],
            "resourceInfos" => [
                "channelNos"        => [1],
                "resourceIndexCode" => $indexCode,
                "resourceType"      => "door"
            ],
        ];
        $url      = $this->list_url['auth/add'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        $result   = Json::decode($post);
        return $result['code'] == 0 ? $result['data']['taskId'] : false;
    }

    /**
     * 根据出入权限配置快捷下载
     * @param $indexCode
     * @param $personId
     * @return mixed|null
     */
    public function shortcut($indexCode, $personId)
    {
        $postData = [
            "taskType"      => "5",//卡片+人脸（组合）
            "resourceInfos" => [
                "channelNos"        => [1],
                "resourceIndexCode" => $indexCode,
                "resourceType"      => "door"
            ]
        ];
        $url      = $this->list_url['shortcut'];
        $sign     = $this->getSign($url);
        $options  = $this->getOptions($sign);
        $post     = $this->curlPost($this->pre_url . $url, Json::encode($postData), $options);
        $result   = Json::decode($post);
        return $result['code'] == 0 ? $result['data']['taskId'] : false;
    }

}