<?php

namespace backend\models;

/**
 * This is the model class for table "nxp_card_record".
 *
 * @property int $id
 * @property int|null $userinfo_id
 * @property int|null $mark
 * @property int|null $type
 * @property int|null $create_time 创建时间
 */
class ExteriorMark extends Base
{
    
    const MARK_INSERT = 1; //标记进厂
    const MARK_OUTPUT = 2; //标记出厂
    
    const TYPE_CARD = 1; //卡片扫卡
    const TYPE_PID  = 2; //pid 扫卡
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_exterior_mark';
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userinfo_id', 'mark', 'type', 'create_time'], 'integer'],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'userinfo_id' => '用户id',
            'mark'        => '标记进厂出厂',
            'type'        => '标记扫卡类型',
            'create_time' => '创建时间',
        ];
    }
    
    /**
     * 添加进厂
     * @param $data
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function insertData($data)
    {
        $model = self::findOne(['userinfo_id' => $data['id']]);
        if ($model){
            $model->delete();//先删除数据
        }
        $this->userinfo_id = $data['id'];
        $this->mark        = ExteriorMark::MARK_INSERT;
        $this->type        = $data['type'];
        $this->create_time = date('Y-m-d H:i:s', time());
        if (!$this->save(false)) {
            return false;
        }
        return true;
    }
   
}

