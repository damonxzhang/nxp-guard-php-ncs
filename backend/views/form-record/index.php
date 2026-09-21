<?php

use yii\helpers\Html;
use backend\widgets\LayuiGridView;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\FormRecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="x-nav">
      <span class="layui-breadcrumb">
        <a href="javascript:;">首页</a>
        <a href="javascript:;">表单管理</a>
        <a href="javascript:;">
          <cite>表单列表</cite>
        </a>
      </span>
</div>
<div class="x-body">
    <p>
        <?= Html::a('新增', ['create'], ['class' => 'layui-btn ']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= LayuiGridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'backend\grids\LayuiCheckBoxColumn'],

            'id',
            'title',

            [
                'attribute' => 'create_user',
                'value' => function($row){
                    return \backend\models\UserInfo::getName($row->create_user);
                }
            ],
            [
                'attribute' => 'create_time',
                'value' => function($row){
                    return date('Y-m-d H:i:s',$row->create_time);
                }
            ],
            [
                'attribute' => 'status',
                'value' => function($row){
                    return  \backend\models\FormRecord::$selectStatus[$row->status];
                }
            ],

            ['class' => 'backend\grids\LayuiActionColumn'],
        ],
    ]); ?>


</div>
