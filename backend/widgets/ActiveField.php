<?php
namespace backend\widgets;

use yii\helpers\Html;

class ActiveField extends  \yii\widgets\ActiveField{

    public $required = false;

    public $requireHtml = '<span class="x-red">*</span>';

    /**
     * Renders the whole field.
     * This method will generate the label, error tag, input tag and hint tag (if any), and
     * assemble them into HTML according to [[template]].
     * @param string|callable $content the content within the field container.
     * If `null` (not set), the default methods will be called to generate the label, error tag and input tag,
     * and use them as the content.
     * If a callable, it will be called to generate the content. The signature of the callable should be:
     *
     * ```php
     * function ($field) {
     *     return $html;
     * }
     * ```
     *
     * @return string the rendering result.
     */
    public function render($content = null)
    {
        if($this->required == true){

            $this->template = strtr($this->template,['{required}'=>$this->requireHtml]);
        }else{

            $this->template = strtr($this->template,['{required}'=>'']);
        }

        return parent::render();

    }

    public function label($label = null, $options = [])
    {
        if ($label === false) {
            $this->parts['{label}'] = '';
            return $this;
        }


        $this->parts['{label}'] = $this->model->getAttributeLabel($this->attribute);
        return $this;
    }



}