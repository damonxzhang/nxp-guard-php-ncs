<?php

namespace backend\models;

/**
 * This is the model class for table "nxp_one_time_card".
 *
 * @property int      $id
 * @property int|null $empid
 * @property int|null $status      状态
 * @property int|null $create_time 创建时间
 * @property int|null $update_time 更新时间
 * @property int|null $delete_time 删除时间
 */
class OneTimeCard extends \backend\models\Base
{
  const DOOR_EAST  = 1;
  const DOOR_WEAT  = 2;
  const DOOR_SOUTH = 3;
  const DOOR_NORTH = 4;

  public static $doorList = [
    self::DOOR_EAST  => '东门',
    self::DOOR_WEAT  => '西门',
    self::DOOR_SOUTH => '南门',
    self::DOOR_NORTH => '北门'
  ];

  const STATUS_YES = 3;
  const STATUS_NO  = 4;

  public static $statusList = [
    self::STATUS_YES => '启用',
    self::STATUS_NO  => '禁用'
  ];
  const STATUS_LOAN = 2;


  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'nxp_one_time_card';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['status', 'door', 'factory', 'create_time', 'update_time', 'delete_time'], 'integer'],
      [['empid'], 'string', 'max' => 100],
      ['empid', 'unique']
    ];
  }


  public function behaviors()
  {
    return [$this->getTimestampBehaviorArray()];
  }

  /**
   * 查询卡是否被借出
   * @param     $number
   * @param int $status
   * @return array|\yii\db\ActiveRecord|null
   */
  public static function getCardId($number, $status = self::SELECT_YES)
  {
    return self::find()->select('id')->where(['empid' => $number, 'status' => $status])->limit(1)->one();
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id'          => 'ID',
      'empid'       => '临时卡id',
      'status'      => '状态',
      'door'        => '门',
      'factory'     => '厂区',
      'create_time' => '创建时间',
      'update_time' => '更新时间',
      'delete_time' => 'Delete Time',
    ];
  }

  /**
   * @param $data
   * @return array
   * @date 2021/6/8 21:16
   */
  public function create($data)
  {
    $model             = self::findOne(['empid' => $data['empid']]);
    if($model){
      return $this->arrData(100, '此临时卡已经存在');
    }
    $this->empid       = $data['empid'];
    $this->status      = $data['status'];
    $this->door        = $data['door'];
    $this->factory     = $data['factory'];
    $this->create_time = time();
    $this->update_time = time();
    if(!$this->save(false)) {
      $error = array_values($this->getFirstErrors())[0];
      return $this->arrData(100, $error);
    }
    return $this->arrData(200, '添加成功');
  }

  /**
   * @param $data
   * @return array
   * @date 2021/6/8 21:16
   */
  public function edit($data)
  {
    $model              = self::findOne($data['id']);
    $model->status      = $data['status'];
    $model->door        = $data['door'];
    $model->factory     = $data['factory'];
    $model->update_time = time();
    if(!$model->save(false)) {
      $error = array_values($model->getFirstErrors())[0];
      return $this->arrData(100, $error);
    }
    return $this->arrData(200, '更新成功');
  }
}
