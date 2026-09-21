<?php
namespace backend\widgets;

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use Exception;
use yii\web\JsExpression;
use backend\models\UserInfo;
use yii\helpers\ArrayHelper;
use backend\models\UserStation;

class SelectSearch extends Select2{

    public $url;
    public $text;
    public $label;
    public $section;
    public $selectKeys;
    public $columnName;
    public $selectWidth = '800px';
    public $required = false;//是否必填
    public $selectList = [];//筛选条件下拉框
    public $addSelectList = [];//附加选择框 在不改变原有的基础上增加新的筛选
    public $pluginOptions = [];

    public function init()
    {
        if(empty($this->url)){
            $this->url = Url::to(['user-info/search-user']);
        }
        parent::init();
    }

    public function run()
    {
        $this->setLabel();
        $this->setSelectList();
        $this->renderSelectsFilter();
        $this->setValueAndText();
        $this->setPluginOptions();
        $this->registerJs();
        parent::run();

    }

    public function setPluginOptions(){
        $pluginOptions = [
            'closeOnSelect' => false,
            'allowClear' => true,
            'language' => [
                'errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),
            ],
            'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
            'templateResult' => new JsExpression('function(res) { return res.text; }'),
            'templateSelection' => new JsExpression('function (res) { return res.text; }'),
        ];
        $this->pluginOptions = array_merge($pluginOptions,$this->pluginOptions); 
    }

    public function setLabel(){

        if($this->required){
            $tag = Html::tag('span','*',['class' => 'x-red']);
        }else{
            $tag = '';
        }
        $this->label = $tag.$this->text;
    }

    /**
     * 下拉框
     * @throws \yii\base\InvalidConfigException
     */
    protected function renderInput()
    {
        if ($this->pluginLoading) {
            $this->_loadIndicator = '<div class="kv-plugin-loading loading-' . $this->options['id'] . '">&nbsp;</div>';
            Html::addCssStyle($this->options, ['width' => '1px', 'height' => '1px', 'visibility' => 'hidden']);
        }
        Html::addCssClass($this->options, 'form-control');
        $this->options['lay-ignore'] = '';
        $this->options['class'] .= " {$this->name}-select";
        $input = $this->getInput('dropDownList', true);

        $label = Html::tag('label', $this->label,['class' => 'layui-form-label','for' => 'pwd']);
        $inline = Html::tag('div',$input,['class' => 'layui-input-inline']);
        $div = Html::tag('div',$label.$inline,['class' => 'layui-form-item']);

        echo $this->_loadIndicator . $this->embedAddon($div);
    }

    /**
     * 筛选内容
     */
    public function setSelectList(){
        if(empty($this->selectList)){
            if(!empty($this->section)){
                $this->selectList['section'] = [ 'data' =>[$this->section['id'] => $this->section['value']] ];
            }

            $this->selectList = $this->selectList + [
                    'position' => [
                        'text' =>'请选择职务',
                        'data'=> UserInfo::$positionArr,
                    ],
                    'shift' => [
                        'text' =>'请选择班别',
                        'data'=> UserInfo::$shiftArr,
                    ],

                    'user-stations' => [
                        'text' =>'请选择站别',
                        'data'=> ArrayHelper::map((new UserStation())->getStation(),'id','station'),
                    ],
                ];
        }
        if(!empty($this->addSelectList)){
            $this->selectList = array_merge($this->selectList,$this->addSelectList);
        }

        if(!empty($this->selectList)){
            $selectKeys = [];
            foreach (array_keys($this->selectList) as $key => $val){
                $selectKeys[$val] = $val."-".'select';
            }
            if(!empty($this->section)){
                $selectKeys['section'] = 'section-select';
            }
            $this->selectKeys = json_encode($selectKeys);
        }

    }

    /**
     * 筛选下拉
     */
    public function renderSelectsFilter(){

        $label = Html::tag('label',$this->label.'筛选',[ 'for'=>"pwd",'class'=>"layui-form-label"]);

        $div = '';
        foreach ($this->selectList as $key => $value){
            $select = '';
            $option = '';
            if(!empty($value['text'])){
                $option .= Html::tag('option',$value['text'],['value' => '']);
            }
            foreach ($value['data'] as $k => $v){
                $option .= Html::tag('option',$v,['value'=>$k,'class' => 'layui-col-md1']);
            }
            $select .= Html::tag('select',$option,[ 'class'=>'layui-col-md1','name'=>$key]);
            $div .= Html::tag('div',$select,['class' => 'layui-col-md1 '.$key.'-select']);
        }
        $re = Html::button('重置条件',['style' => 'margin-left: 10px;','class' => 'layui-col-md1 layui-btn layui-btn-primary '.$this->name.'-reload-btn']);
        $search = Html::button('搜索',['style' => 'margin-left: 10px;','class' => 'layui-col-md1 layui-btn layui-btn-primary '.$this->name.'-search-btn']);
        if(!empty($this->value) && is_array($this->value)){

            $valueString = implode(',',$this->value);
        }else{

            $valueString = $this->value;
        }

        $hidden = Html::hiddenInput($this->columnName, $valueString);
        echo Html::tag('div',$label.$div.$re.$search.$hidden,['class' => 'layui-form-item '.$this->name.'-div']);

    }

    /**
     * 注册js
     */
    public function registerJs(){
        $testdata = json_encode($this->value);
        $js = <<<JS
            $(function(){
                function {$this->name}GetData(params){
                    let selectId = new Array();
                    let selectData = new Array(); 
                      
                    $(".{$this->name}-select option:checked").each(function(i){
                        selectData[i] = $(this).text();
                        selectId[i] = $(this).val();
                    }); 
                  
                    $('input[name="{$this->columnName}"]').val(selectId.join(','));

                    $.post({
                        url:'/user-info/search-user',
                        data:params,
                        dataType:'json',
                        success:function(result){
                             
                            if(result.code == 0){
                                
                                $('.'+'{$this->name}-select').select2('destroy').empty();  
                                let content = ''; 
                                let isReload = true;
                                $.each(result.results, function (i, val) { 
                                     $.each(selectId, function (i) {
                                         if(selectId[i] == val.id){
                                              
                                             isReload = false;
                                         } 
                                    });
                                    if(isReload){ 
                                        content += '<option value="' + val.id + '">' + val.text + '</option>';
                                    }
                                }); 
                                if(selectData){ 
                                     
                                    $.each(selectData, function (i) {
                                     
                                        content += '<option value="' +selectId[i] + '" selected>' + selectData[i] + '</option>';
                                    });
                                }  
                                $('.'+'{$this->name}-select').append(content); 
                                $('.'+'{$this->name}-select').select2({
                                    data: result.results ,
                                    width:'{$this->selectWidth}',
                                    closeOnSelect:false,   
                                }); 
                            }else{ 
                                layer.msg(result.msg)
                            }
                        }
                    })
                }
                
                //重置搜索
                $(".{$this->name}-reload-btn").click(function(){
                    let obj = '';
                    let params = new Array();
                    let select = "dd[lay-value='']"; 
                    let selectKeys = {$this->selectKeys}; 
                    $.each(selectKeys,function(i,v){     
                        obj = $('.'+v[i]); 
                        $('.'+v).find('dl').find(select).click();  
                        params.push({ name:i,value:obj.find('.layui-this').attr('lay-value')});
                    }); 
                  
                    {$this->name}GetData(params,$(this)); 
                }); 
                
                //搜索
                $(".{$this->name}-search-btn").click(function () {  
                  
                    let obj = '';
                    let params = new Array();
                    let selectKeys = {$this->selectKeys};  
                    $.each(selectKeys,function(i,v){     
                        obj = $('.'+'{$this->name}-div').find('.'+v); 
                    
                        params.push({ name:i,value:obj.find('.layui-this').attr('lay-value')});
                    });  
                    {$this->name}GetData(params,$(this)); 
                }); 
                
                $('.'+'{$this->name}-select').on("change",function(){
                    let data = $(this).val();
                    $('input[name="{$this->columnName}"]').val(data);
                }); 
            });
            
            $(function(){
                let obj = '';
                let params = new Array();
                let select = "dd[lay-value='']"; 
                let selectKeys = {$this->selectKeys}; 
                $.each(selectKeys,function(i,v){     
                    obj = $('.'+v[i]); 
                    $('.'+v).find('dl').find(select).click();  
                    params.push({ name:i,value:obj.find('.layui-this').attr('lay-value')});
                }); 
            
                $.post('/user-info/search-user',params,function(res) { 
                   
                    $('.'+'{$this->name}-select').select2({
                        data: res.results ,
                        width:'{$this->selectWidth}',
                        placeholder: "请输入{$this->text}",
                        closeOnSelect:false 
                    });
                },'json');
            })
JS;
        $this->view->registerJs($js);
    }

    public function setValueAndText(){

        if(!empty($this->value) && is_string($this->value)){
            $this->value = array_filter( explode(',',$this->value));
        }

        if(!empty($this->value) && is_array($this->value)){
            $this->initValueText = (new UserInfo())->getNameById($this->value);
        }

    }

}