<?php

use backend\models\LevelDept;
use yii\helpers\Html;
use backend\widgets\LayuiGridView;
use backend\models\LenelEmp;
use backend\widgets\SearchActiveForm;
use backend\models\ExteriorUser;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\ExteriorUserRecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="x-body">
	<?php if (!in_array(Yii::$app->user->getId(), [7031])):?>
    <div class="layui-row">
        <?php $form = SearchActiveForm::begin([
            'action' => [ 'index' ],
            'method' => 'get',
        ]); ?>
        <?= $form->field($searchModel, 'txterior_user')->input('text', [ 'placeholder' => "外部人员姓名" ]) ?>
        <?= $form->field($searchModel, 'start_time')->input('text', [ 'class' => 'start_time layui-input', 'placeholder' => "请选择开始时间" ]) ?>
        <?= $form->field($searchModel, 'end_time')->input('text', [ 'class' => 'end_time layui-input', 'placeholder' => "请选择结束时间" ]) ?>
        <?= $form->field($searchModel, 'user_card_name')->input('text', [ 'placeholder' => "临时卡号" ]) ?>
        <?= Html::submitButton('<i class="layui-icon"></i>查询', [ 'class' => 'layui-btn' ]) ?>
        <?= Html::a('<i class="layui-icon layui-icon-refresh" style="line-height:30px"></i>刷新', 'index', [ 'class' => 'layui-btn' ]) ?>
        <?php SearchActiveForm::end(); ?>
    </div>
    <?php endif;?>
    <p>
    <?php  $id = \Yii::$app->user->id;
     if (in_array($id,ExteriorUser::$guardArr)): ?>
        <?= Html::a('出厂登记', 'javascript:;', [ 'class' => 'layui-btn layui-bg-cyan',  'style' => 'height:50px;width:150px;font-size:25px;line-height: 50px;','onclick' => "x_admin_show('出厂登记','snap-register',600,400)" ]) ?>
        <button style="height:50px;width:150px;font-size:25px;line-height: 50px;" class="layui-btn layui-btn-danger" onclick="x_admin_show('车辆出厂','<?=  Url::to(['exterior-user-car/snap-car']) ?>',600,400)">车辆出厂</button>
        <button style="height:50px;width:150px;font-size:25px;line-height: 50px;" class="layui-btn layui-btn-normal" onclick="x_admin_show('车辆登记','<?=  Url::to(['exterior-user-car/index']) ?>',1300,500)">车辆登记</button>
     <?php else: ?>
        <?= Html::a('手机号登记', 'javascript:;', [ 'class' => 'layui-btn layui-btn-normal pop-up',  'style' => 'height:50px;width:150px;font-size:25px;line-height: 50px;', 'url' => 'agreement', 'title' => '手机号登记' ]) ?>
        <?= Html::a('接待人登记', 'javascript:;', [ 'class' => 'layui-btn ',  'style' => 'height:50px;width:150px;font-size:25px;line-height: 50px;', 'onclick' => "x_admin_show('接待人登记','reception-register',600,500)" ]) ?>
        <?= Html::a('现场登记', 'javascript:;', [ 'class' => 'layui-btn layui-btn-normal',  'style' => 'height:50px;width:150px;font-size:25px;line-height: 50px;', 'onclick' => "x_admin_show('现场登记','scene-register',1000,600)" ]) ?>
        <?= Html::a('员工登记', 'javascript:;', [ 'class' => 'layui-btn layui-btn-danger',  'style' => 'height:50px;width:150px;font-size:25px;line-height: 50px;', 'onclick' => "x_admin_show('员工登记','personnel-pid',600,500)" ]) ?>
        <button style="height:50px;width:150px;font-size:25px;line-height: 50px;" class="layui-btn layui-btn-normal" onclick="x_admin_show('车辆登记','<?=  Url::to(['exterior-user-car/index']) ?>',1000,500)">车辆登记</button>
    <?php endif; ?>
    </p>
    <?= LayuiGridView::widget([
        'dataProvider' => $dataProvider,
        'columns'      => [
            [ 'class' => 'yii\grid\SerialColumn' ],
            [
                'attribute' => 'exterior_user',
                'value'     => function($row) {
                    $model = ExteriorUser::findOne($row->exterior_user);
                    return $model ? $model->name : '';
                }
            ],
//            [
//                'label' => '申请人',
//                'value' => function($row) {
//                   $model = LenelEmp::findOne($row->create_user);
//                   return $model ? $model->MIDNAME : '';
//                }
//            ],
//            [
//                'label' => '接待人',
//                'value' => function($row) {
//                   $model = LenelEmp::findOne($row->reception_user);
//                   return $model ? $model->MIDNAME : '';
//                }
//            ],
//            [
//                'label' => '接待人部门',
//                'value' => function($row) {
//                   $department = (new LevelDept())->departmentByEmpId($row->reception_user);
//                   return $department ? $department : '';
//                }
//            ],
            [
                'attribute' => 'real_entrance_time',
                'value'     => function ($row) {
                    return $row->real_entrance_time ? date('Y年m月d日 H时i分', $row->real_entrance_time) : '';
                }
            ],
            [
               'label' => '临时卡号',
               'value' => function($row) {
                  $model = LenelEmp::findOne($row->user_card);
                  return $model ? $model->LASTNAME : '';
               }
            ],
            [
                'label'  => '车牌号',
                'format' => 'raw',
                'value'  => function($row) {
                    return $row->vehicle;
                }
            ],
            [
                'label' => '厂区',
                'format' => 'raw',
                'value' => function($row){
                    return ArrayHelper::getValue(ExteriorUser::$factoryList, $row->factory,'');
                }
            ],
            [
                'class'    => 'backend\grids\LayuiActionColumn',
                'template' => '{view} {update}  {entrance} {delete}  {remark} ',
                'buttons'  => [
                    'view'           => function($url, $row) {
                        return Html::a('登记详情', '#', [ 'class' => 'layui-btn layui-btn-xs layui-btn-green', 'onclick' => "x_admin_show('详情','" . $url . "')" ]);
                    },
                    'update'           => function($url, $row) {
                        if ($row->status == $row::STATUS_AWAIT && $row->create_user == \Yii::$app->user->id) {
                            return Html::a('编辑', '#', [ 'class' => 'layui-btn layui-btn-xs layui-btn-normal', 'onclick' => "x_admin_show('登记','" . $url . "')" ]);
                        }
                        return '';
                    },
                    'entrance'         => function($url, $row) {
                        $id = \Yii::$app->user->id;
                        if (in_array($id,ExteriorUser::$guardArr)) {
                            if ($row->status == $row::STATUS_GREEN_LIGHT) {
                                return Html::a('警卫确认', '#', [ 'class' => 'layui-btn layui-btn-xs layui-bg-cyan', 'onclick' => "x_admin_show('警卫确认','" . $url . "')" ]);
                            }
                        }
                        return '';
                    },
                    'delete'           => function($url, $row) {
                        $id = \Yii::$app->user->id;
                        if (in_array($id,ExteriorUser::$guardArr)) {
                            if ($row->status == $row::STATUS_AWAIT || $row->status == $row::STATUS_GREEN_LIGHT) {
                                return Html::a('删除', '#', [ 'class' => 'layui-btn layui-btn-xs layui-btn-danger', 'onclick' => "del(this,'".$row->id."','" . Url::to(['exterior-user-record/delete']) . "')" ]);
                            }
                        }
                        return '';
                    },
                    'remark'         => function($url, $row) {
                        if (7032 == \Yii::$app->user->id) {
                            return Html::a('异常备注', '#', [ 'class' => 'layui-btn layui-btn-xs', 'onclick' => "x_admin_show('异常备注','" . $url . "')" ]);
                        }
                        return '';
                    }
                ],
            ],
        ],
    ]); ?>
</div>
