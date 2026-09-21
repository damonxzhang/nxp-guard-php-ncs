<?php

namespace backend\models;

use Yii;

class UserCard extends Base
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'nxp_user_card';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['uid', 'empid'], 'integer']
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id'    => 'ID',
      'uid'   => 'UserInfo 表id',
      'empid' => 'EMPID'
    ];
  }

  /**
   * @param $data
   * @return array
   * @date 2021/6/10 21:47
   */
  public function create($data)
  {
    $check        = self::findOne(['uid' => $data['uid'], 'empid' => $data['empid']]);
    if($check){
      return $this->arrData(100, '此卡已经绑定过此账号!');
    }
    $this->uid   = $data['uid'];
    $this->empid = $data['empid'];
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

  /*
   * 检查是否有对应关系
   */
  public function checkEmpidCard($param = [])
  {
    $query = self::findOne(['empid' => $param['empid'],'uid' => $param['uid']]);
    return $query ? true : false ;
  }


}

