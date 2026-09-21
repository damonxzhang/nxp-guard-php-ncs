<?php

namespace backend\models;

class LenelEmp  extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'EMP';
    }
    
    /**
     * @return object|\yii\db\Connection|null
     * @throws \yii\base\InvalidConfigException
     */
    public static function getDb()
    {
        return \Yii::$app->get('db2');
    }

    
    //获取人员信息
    public function getMidName($id)
    {
        $model = LenelEmp::findOne(['ID' => $id]);
        return $model ? $model->MIDNAME : '';
    }

    public function getLastName($id)
    {
        $model = LenelEmp::findOne(['ID' => $id]);
        return $model ? $model->LASTNAME : '';
    }


    //获取信息
    public function getSsno($id)
    {
        $model = LenelEmp::findOne(['ID' => $id]);
        return $model ? $model->SSNO : '';
    }
  /**
   * 获取临时卡
   * @return array|\yii\db\ActiveRecord[]
   * @date 2021/6/8 20:23
   */
  public function getSnapCardArr()
  {
    $query = LenelEmp::find();
    $query->alias('emp');
    $query->select([ 'emp.ID','emp.LASTNAME' ]);
    $query->leftJoin(LenelBadge::tableName() . ' lb', 'emp.ID = lb.EMPID');
    $query->andWhere(['in', 'lb.TYPE', [LenelBadge::TYPE_SNAP1,LenelBadge::TYPE_SNAP2,LenelBadge::TYPE_SNAP3,LenelBadge::TYPE_CAR ]]);
    $query->orderBy('emp.LASTNAME ASC');    
    // $query->orderBy('lb.TYPE ASC,emp.SSNO ASC');
    $query->asArray();
    return $query->all();
  }
// /**
//      * 获取临时卡
//      * @param     $empId
//      * @param int $card
//      * @return mixed|string|null
//      * @date 2021/7/13 15:36
//      */
//     public function getCardNoFirst($empId, $card = 1)
//     {
//         $typeArr = LenelBadge::find()->select([ 'TYPE' ])->where(['EMPID' => $empId])->column();
//         if(empty($typeArr)){
//             return '';
//         }
//         $uniqueType = array_unique($typeArr);
//         $array = array_merge($uniqueType);
//         if($card ==1 ){
//             $badgeId = $array[0];
//         }else{
//             if(!isset($array[1])){
//                 return '';
//             }
//             $badgeId = $array[1];
//         }
//         $model    = LenelBadgeTyp::findOne(['ID' => $badgeId]);
//         if($model){
//             return $model->NAME;
//         }
//         return '';
//     }

     /**
     * 获取临时卡
     * @param     $empId
     * @param int $card
     * @return mixed|string
     * @date 2021/7/14 9:34
     */
    public function getCardNoFirst($empId, $card = 1)
    {
        $idArr = LenelBadge::find()->select([ 'ID' ])->where(['EMPID' => $empId])->column();
        if(empty($idArr)){
            return '';
        }
        if($card ==1 ){
            return $idArr[0];
        }
        if(!isset($idArr[1])){
            return '';
        }
        return $idArr[1];
    }
}
