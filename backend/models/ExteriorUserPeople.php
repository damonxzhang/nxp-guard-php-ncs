<?php

namespace backend\models;

use Yii;

class ExteriorUserPeople extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_exterior_user_people';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['empid'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'    => 'ID',
            'empid' => 'EMPID'
        ];
    }

    /**
     * @param $data
     * @return array
     * @date 2021/6/10 21:47
     */
    public function create($empid)
    {
        $check        = self::findOne(['empid' => $empid]);
        if($check){
            return $this->arrData(100, '已经绑定过此账号!');
        }
        $this->empid = $empid;
        if(!$this->save(false)) {
            $error = array_values($this->getFirstErrors())[0];
            return $this->arrData(100, $error);
        }
        return $this->arrData(200, '添加成功');
    }

    /**
     * @param $data
     * @return array
     * @date 2021/6/10 21:47
     */
    public function edit($data)
    {
        $model        = self::findOne($data['id']);
        $model->empid = $data['empid'];
        if(!$model->save(false)) {
            $error = array_values($model->getFirstErrors())[0];
            return $this->arrData(100, $error);
        }
        return $this->arrData(200, '更新成功');
    }

    

}


