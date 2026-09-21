<?php

namespace backend\models;

use Yii;

class ExteriorUserLog extends Base
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_exterior_user_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['create_time', 'add_user'], 'integer'],
            [['error_param'], 'string'],
        ];
    }

//    /**
//     * 添加导入日志
//     * @param $data
//     * @return array
//     * @date 2021/6/28 13:51
//     */
//    public function create($data)
//    {
//        $addUser = \Yii::$app->user->id;
//        $result  = self::deleteAll(['add_user' => $addUser]);
//        if($result === false) {
//            return $this->arrData(100, '删除失败');
//        }
//        $this->add_user    = $addUser;
//        $this->error_param = $data['error_param'];
//        $this->create_time = time();
//        if(!$this->save(false)) {
//            $error = array_values($this->getFirstErrors())[0];
//            return $this->arrData(100, $error);
//        }
//        return $this->arrData(200, '添加成功');
//    }
}

