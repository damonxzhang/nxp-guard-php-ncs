<?php

namespace backend\models;

use app\models\UserRole;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "nxp_role".
 *
 * @property string $id 用户角色表
 * @property string $name 角色名
 * @property string $description 角色描述
 * @property string $permission 权限
 * @property string $create_time 创建时间
 */
class Role extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_role';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['create_time'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 100],
            [['permission'], 'string', 'max' => 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'name'        => '角色名',
            'description' => '描述',
            'permission'  => '权限',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 添加权限
     * @param $data
     * @return array
     */
    public function create($data)
    {
        $this->name        = $data['name'];
        $this->description = $data['description'];
        $this->permission  = Json::encode($data['ids']);
        $this->create_time = date('Y-m-d H:i:s', time());
        if (!$this->save()) {
            $error = array_values($this->getFirstErrors())[0];
            return $this->arrData(100, $error);
        }
        return $this->arrData(200, '添加成功');
    }

    /**
     * 编辑权限
     * @param $data
     * @return array
     */
    public function edit($data)
    {
        $model              = self::findOne($data['id']);
        $model->name        = $data['name'];
        $model->description = $data['description'];
        $model->permission  = Json::encode($data['ids']);
        $model->create_time = date('Y-m-d H:i:s', time());
        if (!$model->save()) {
            $error = array_values($model->getFirstErrors())[0];
            return $this->arrData(100, $error);
        }
        return $this->arrData(200, '更新成功');
    }

    /**
     * 获得所以拥有的权限
     * @return array
     */
    public static function getUserRolePermission(){
        $data = self::find()->alias('r')
            ->innerJoin(['ur'=> UserRole::tableName()],'r.id = ur.role_id')
            ->where(['ur.user_id' => \Yii::$app->user->id])
            ->all();
        $permission = [];
        if ($data){
            foreach ($data as $v){
                if(!empty($v->permission)){
                    $permission = ArrayHelper::merge(Json::decode($v->permission),$permission);
                }
            }
        }
        return array_unique($permission);
    }

    /**
     * 获取权限名称
     * @param $id
     * @return string
     */
    public function getRoleName($id)
    {
        if(empty($id)){
            return '';
        }
        $query = self::findOne(['id' => $id]);
        return empty($query) ? '' : $query->name;
    }

    public static function getRoleList($id = false){
        $query = self::find()->select('id,name')->asArray();
        if($id){
            $query->where(['id' => $id ]);
        }

        $data = $query->all();

        if($data){
            return ArrayHelper::map($data,'id','name');
        }
        return [];

    }

    /**
     * 根据中文名称搜索id
     * @param $name
     * @return bool|mixed
     */
    public function getIdByName($name){
        $id = self::find()->select('id')->where(['name' => $name])->limit(1)->column();
        if(!empty($id)){
            return $id[0];
        }else{
            return false;
        }
    }
}
