<?php
namespace backend\widgets;

use yii\grid\GridView;

class LayuiGridView extends GridView{

    public $tableOptions = ['class' => 'layui-table'];

    public $layout = '{items}<div class="page">{pager}</div>';

}