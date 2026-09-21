<?php
/**
 * User: Colin
 * Time: 2019/3/4 15:13
 */

namespace backend\models;

use common\models\GlobalFun;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class Base extends ActiveRecord
{

    const SELECT_YES = 1;
    const SELECT_NO = 0;
    /**
     * @var array   替换使用的key
     */
    protected $replaceKeyList = [
        'deviceId'  => 'equipment_number',
        'boxId'     => 'warehouse_number',
        'userId'    => 'user_id',
        'tankId'    => 'tank_id',
        'wbiId'     => 'wbi_code'
    ];
    /**
     * 获取登陆ID
     * @return int|string
     */
    public function getUid()
    {
        $id= \Yii::$app->user->getId();
        $userModel = User::findOne($id);
        return $userModel ? $userModel ->user_id : '';
    }

    /**
     * 返回数组信息
     * @param $status
     * @param $msg
     * @return array
     */
    public function arrData($status, $msg)
    {
        return ['status' => $status, 'msg' => $msg];
    }

    /**
     * 封装规则检查, 应用此方法，需要new Model
     * @param $postData
     * @param string $scenario
     * @return array|null
     */
    public function chkAttributes( $postData, $scenario = '' ){
        //1 加入场景
        if ($scenario) {
            $this->setScenario($scenario);
        }
        $this -> attributes = $postData;

        //2 当提交无错误时
        if ($this->validate()) {
            return null;
        }

        //3 有错误时,只取第一个错误就ok了
        $errors = [];
        foreach($this->errors as $attribute => $es){
            $errors[$attribute] = $es[0];
        }

        return $errors;
    }

    /**
     * 替换字段名
     * @param $data
     * @return bool
     */
    public function replaceKey($data){

        if(!empty($data) && count($data) > 0){
            foreach ($data as $key => $value){
                if(!empty( $this->replaceKeyList[$key])){
                    $data[$this->replaceKeyList[$key]] = $value;
                }
            }
            return $data;
        }else{

            return false;
        }
    }

    /**
     * 不存在则创建model
     * @param $params //查询条件
     * @return Base|bool
     */
    public function existsSoCreate($params){
        $res = $this->find()->where($params)->exists();
        if($res == true){
            return false;
        }else{
            return new self;
        }
    }

    /**
     * 将培训课程的文件路径转换为数组
     * @param $params
     * @return array
     */
    public function decodeTrainCoursesFile($params){
        $result = [];
        foreach ($params as $key => $value){
            $pathArr = json_decode($value,1);
            if(is_array($pathArr) && count($pathArr) > 0){
                $result = array_merge($result,$pathArr);
            }
        }
        return $result;
    }

    /**
     * 批量插入
     * @param $tableName
     * @param $columns
     * @param $rows
     * @return int
     * @throws \yii\db\Exception
     */
    public function batchInsert($tableName, $columns, $rows){
        return \Yii::$app->db->createCommand()->batchInsert($tableName,$columns,$rows)->execute();
    }

    /**
     * 使用保存时间行为时的属性
     * @return array
     */
    public function getTimestampBehaviorArray(){
        return [
            'class' => TimestampBehavior::className(),
            'createdAtAttribute' =>'create_time',
            'updatedAtAttribute' =>'update_time',
            'value' => time()
        ];
    }
    /**
     * 将所有的错误信息转换为字符串
     * @param null $error
     * @return string
     */
    public function errorToString($error = null){
        if(empty($error)){
           $error = $this->getErrors();
        }
        return GlobalFun::errorToString($error);
    }
}