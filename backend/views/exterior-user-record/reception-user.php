<?php
 use backend\models\ExteriorUserRecord;
 use backend\models\ExteriorUser;
use yii\helpers\Html;
use backend\widgets\LayuiGridView;
use backend\models\LenelEmp;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\ExteriorUserRecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="x-body">

    <?= LayuiGridView::widget([
        'dataProvider' => $data,
        'columns'      => [
            [
                'attribute' => 'name',
                'value'     => function ($row) {
                    return $row->name;
                }
            ],
            [
                'attribute' => 'phone',
                'value'     => function ($row) {
                    return $row->phone;
                }
            ],
            [
                'label' => '申请人',
                'value' => function($row) {
                    $model = LenelEmp::findOne($row->apply_id);
                    return $model ? $model->MIDNAME : '';
                }
            ],
            [
                'label' => 'company',
                'value' => function ($row) {
                    return $row->company;
                }
            ],
            [
                'attribute' => 'get_out_time',
                'value'     => function ($row) {
                    return $row->get_out_time;
                }
            ],
            [
                'class'    => 'backend\grids\LayuiActionColumn',
                'template' => '{update}',
                'buttons'  => [
                    'update' => function ($url, $row) {
                         $getEnterTime = $getOutTime ='';
                        if($row->get_enter_time){
                            $getEnterTime = strtotime($row->get_enter_time);
                        }
                         if($row->get_out_time){
                            $getOutTime   = strtotime($row->get_out_time);
                        }
                        $checkModel = ExteriorUserRecord::find();
                        $checkModel->andWhere(['exterior_user' => $row->id]);
                        $checkModel->andWhere(['in','status',[2,3]]);
                        $checkModel->andWhere(['>=','get_out_time',$getEnterTime]);
                        $checkModel->andWhere(['<=','get_out_time',$getOutTime]);
                        $check = $checkModel->one();
                       
                        if(!$check){
                            return Html::a('确认',Url::to([$this->context->id . '/agreement-user', 'id' => $row->id]), ['class' => 'layui-btn layui-btn-xs layui-btn-normal']);
                        }else{
                            return Html::a('已确认','#', ['class' => 'layui-btn layui-btn-xs layui-btn-disabled']);
                        }
                    },
                ],
            ],
        ],
    ]); ?>
</div>

