<?php
namespace backend\grids;

use yii\helpers\Html;
use yii\grid\CheckboxColumn as Checkbox;

class LayuiCheckBoxColumn extends Checkbox{

    public $checkboxOptions = ['class' =>'layui-unselect layui-form-checkbox','lay-skin' => "primary"];

    protected function renderHeaderCellContent()
    {
        if ($this->header !== null || !$this->multiple) {
            return parent::renderHeaderCellContent();
        }

        return '<div class="layui-unselect header layui-form-checkbox" lay-skin="primary"><i class="layui-icon"></i></div>';
    }
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return parent::renderDataCellContent($model, $key, $index);
        }

        if ($this->checkboxOptions instanceof Closure) {
            $options = call_user_func($this->checkboxOptions, $model, $key, $index, $this);
        } else {
            $options = $this->checkboxOptions;
        }

        if (!isset($options['value'])) {
            $options['data-id'] = is_array($key) ? Json::encode($key) : $key;
            unset($options['value']);
        }

        if ($this->cssClass !== null) {
            Html::addCssClass($options, $this->cssClass);
        }

        return '<div '.Html::renderTagAttributes($options).'><i class="layui-icon"></i></div>';

    }

    /**
     * Registers the needed JavaScript.
     * @since 2.0.8
     */
    public function registerClientScript()
    {
       return true;
    }
}