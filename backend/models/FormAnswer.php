<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "nxp_form_answer".
 * @property int         $id
 * @property int|null    $form_id     表单
 * @property int|null    $exterior_id 关联id
 * @property string|null $answer      答案
 * @property int|null    $file        签字文件
 * @property int|null    $create_user 反馈用户
 * @property int|null    $create_time 反馈用户
 */
class FormAnswer extends \backend\models\Base
{
    const ONE_FORM = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_form_answer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [ [ 'form_id', 'exterior_id', 'file', 'create_user', 'create_time' ], 'integer' ],
            [ [ 'answer' ], 'string' ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'form_id'     => 'Form ID',
            'exterior_id' => 'Exterior ID',
            'answer'      => 'Answer',
            'file'        => 'File',
            'create_user' => 'Create User',
            'create_time' => 'Create Time',
        ];
    }
}
