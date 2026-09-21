<?php
namespace backend\grids;

use yii\grid\ActionColumn;
use Yii;
use yii\helpers\Html;

class LayuiActionColumn extends  ActionColumn{
    public $header = '操作';
    public $template = '{view} {update}';
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                $style = '';
                switch ($name) {
                    case 'view':
                        $title = Yii::t('yii', 'View');
                        break;
                    case 'update':
                        $title = Yii::t('yii', 'Update');
                        $style = ' layui-btn-normal';
                        break;
                    case 'delete':
                        $title = Yii::t('yii', 'Delete');
                        $style = ' layui-btn-danger';
                        break;
                    default:
                        $title = ucfirst($name);
                }

                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                    'class'=>"layui-btn layui-btn-xs".$style
                ], $additionalOptions, $this->buttonOptions);
                $icon = Html::tag('span', $title, ['class' => "glyphicon glyphicon-$iconName"]);

                return Html::a($title, $url, $options);
            };
        }
    }


}