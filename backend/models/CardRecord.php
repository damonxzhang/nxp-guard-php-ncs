<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "nxp_card_record".
 *
 * @property int $id
 * @property int|null $exterior_id 登记记录
 * @property int|null $exterior_user 外部人员
 * @property int|null $status 状态
 * @property int|null $card_id 临时卡
 * @property int|null $create_time 创建时间
 * @property int|null $update_time 更新时间
 * @property int|null $delete_time 删除时间
 */
class CardRecord extends \backend\models\Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_card_record';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['exterior_id', 'exterior_user', 'status', 'card_id', 'create_time', 'update_time', 'delete_time'], 'integer'],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'exterior_id'   => '登记记录',
            'exterior_user' => '外部人员',
            'status'        => '状态',
            'card_id'       => '临时卡',
            'create_time'   => '创建时间',
            'update_time'   => 'Update Time',
            'delete_time'   => 'Delete Time',
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExteriorUser()
    {
        return $this->hasOne(ExteriorUser::className(), ['id' => 'exterior_user']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExteriorUserRecord()
    {
        return $this->hasOne(ExteriorUserRecord::className(), ['id' => 'exterior_id']);
    }
}
