<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "nxp_form_record".
 *
 * @property int $id
 * @property int|null $create_user 创建人
 * @property int|null $file_id 文件
 * @property int|null $status 状态
 * @property int|null $create_time 创建时间
 * @property int|null $update_time 更新时间
 * @property int|null $delete_time 删除时间
 */
class FormRecord extends \backend\models\Base
{

    public static $selectStatus = [
        self::SELECT_YES => '开启',
        self::SELECT_NO  => '关闭'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_form_record';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_id','status','title'],'required'],
            [['create_user', 'status', 'create_time', 'update_time', 'delete_time'], 'integer'],
            [ 'file_id','string']
        ];
    }

    public function behaviors()
    {
        return [$this->getTimestampBehaviorArray()];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '标题',
            'create_user' => '创建人',
            'file_id' => '文件',
            'status' => '状态',
            'create_time' => '创建时间',
            'update_time' => 'Update Time',
            'delete_time' => 'Delete Time',
        ];
    }
}
