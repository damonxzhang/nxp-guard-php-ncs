<?php

namespace backend\models;

use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "nxp_menu".
 *
 * @property string $id 菜单表
 * @property string $name 菜单名
 * @property string $icon 图标
 * @property int $is_show 是否显示 1:显示 2:隐藏
 * @property int $sort 排序
 * @property string $create_time 创建时间
 */
class Menu extends Base
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_menu';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_show', 'sort','create_time'], 'integer'],
            [['name','icon'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'name'        => '菜单名',
            'icon'        => '图标',
            'is_show'     => '是否显示',
            'sort'        => '排序',
            'create_time' => '创建时间',
        ];
    }

    public function create($data)
    {
        $this->name = $data['name'];
        $this->icon = $data['icon'];
        if (!$data['sort']) {
            $data['sort'] = 10;
        }
        $this->sort        = $data['sort'];
        $this->is_show     = $data['is_show'];
        $this->create_time = time();
        if (!$this->save()) {
            $error = array_values($this->getFirstErrors())[0];
            return $this->arrData(100, $error);
        }
        return $this->arrData(200, '添加成功');
    }

    public function edit($data)
    {
        $model          = self::findOne($data['id']);
        $model->name    = $data['name'];
        $model->icon    = $data['icon'];
        $model->is_show = $data['is_show'];
        if (!$data['sort']) {
            $data['sort'] = 10;
        }
        $model->sort = $data['sort'];
        if (!$model->save()) {
            $error = array_values($model->getFirstErrors())[0];
            return $this->arrData(100, $error);
        }
        return $this->arrData(200, '更新成功');
    }

    public function getSubmenu()
    {
        $permission = Role::getUserRolePermission();

        $userSubmenuIdArr = $this->getUserSubmenuIdArr($permission);
        return $this->hasMany(Submenu::className(), ['menu_id' => 'id'])
            ->where(['nxp_submenu.is_show' => 1])
            ->andWhere(['in', 'nxp_submenu.id', $userSubmenuIdArr])
            ->orderBy('nxp_submenu.sort desc');
    }

    public function getUserSubmenuIdArr($permission)
    {
        $routes       = Route::find()->select('submenu_id')->where(['in', 'id', $permission])->column();
        return array_unique($routes);
    }

}
