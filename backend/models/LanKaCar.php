<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "nxp_lanka_car".
 * @property int         $id
 */
class LanKaCar extends \backend\models\Base
{
    

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_lanka_car';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [ [ 'create_time' ], 'integer' ],
            [ [ 'text','create_times' ], 'string' ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'text'        => 'Text',
            'create_time' => 'Create Time',
        ];
    }
}
