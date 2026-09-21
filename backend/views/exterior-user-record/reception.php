<?php

use yii\helpers\Html;
use backend\widgets\LayuiGridView;
use backend\models\ExteriorUser;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\ExteriorUserRecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="x-body">
    <p>
        <?= Html::a('手机号登记',Url::to([$this->context->id . '/agreement']), ['class' => 'layui-btn layui-btn-normal']) ?>
    </p>
    <?= LayuiGridView::widget([
        'dataProvider' => $data,
        'columns'      => [
            [
                'attribute' => 'exterior_user',
                'value'     => function ($row) {
                    return ExteriorUser::getName($row->exterior_user);
                }
            ],
            [
                'attribute' => 'create_user',
                'value'     => function ($row) {
                    return $row->createUser->name;
                }
            ],
            [
                'label' => '接待人部门',
                'value' => function ($row) {
                    return $row->createUser->department_number;
                }
            ],
            [
                'attribute' => 'real_entrance_time',
                'value'     => function ($row) {
                    if ($row->real_entrance_time) {
                        return date('Y年m月d日 H时i分s秒', $row->real_entrance_time);
                    } else {
                        return '未设置';
                    }
                }
            ],
            [
                'label'  => '是否有车辆',
                'format' => 'raw',
                'value'  => function ($row) {
                    if (!empty($row->card_id)) {
                        return $row->exteriorUser->vehicle;
                    } else {
                        return '<i class="layui-icon">ဇ</i>';
                    }
                }
            ],
            [
                'class'    => 'backend\grids\LayuiActionColumn',
                'template' => '{update}',
                'buttons'  => [
                    'update' => function ($url, $row) {
                        return Html::a('更新', '#', ['class' => 'layui-btn layui-btn-xs layui-btn-normal', 'onclick' => "x_admin_show('登记','" . $url . "')"]);
                    },
                ],
            ],
        ],
    ]); ?>
</div>
