<?php

namespace backend\controllers;

use app\models\UserRole;
use backend\models\Menu;
use backend\models\Role;
use backend\models\Route;
use backend\models\Submenu;
use backend\models\Token;
use yii\helpers\ArrayHelper;
use Yii;

class IndexController extends BaseController
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $menu  = $this->getMenu();
        $token = Token::encode(Yii::$app->user->identity->id);
        return $this->render('index', ['menu' => $menu, 'name' => Yii::$app->user->identity->name]);
    }


    /**
     * 清除缓存
     * @return string
     */
    public function actionClearCache()
    {
        $cache = Yii::$app->cache;
        $res   = $cache->flush();
        if (!$res) {
            return $this->json(100, '刷新失败');
        }
        return $this->json(200, '刷新完成');
    }

    /**
     * 获取权限菜单（并存入缓存）
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public function getMenu()
    {
        $permission = Role::getUserRolePermission();
        $roleList   = UserRole::getRoleList(Yii::$app->user->id);
        $menu       = Yii::$app->cache->get('menu');
        $roleStr    = implode('', $roleList);
        if (!empty($menu[$roleStr]))
            return $menu[$roleStr];
        $model        = new Menu();
        $submenuIdArr = $model->getUserSubmenuIdArr($permission);
        $submenus     = Submenu::find()->where(['in', 'id', $submenuIdArr])->orderBy(['sort' => SORT_DESC])->asArray()->all();
        $menuIdArr    = ArrayHelper::getColumn($submenus, 'menu_id');
        $menuIdArr    = array_unique($menuIdArr);
        $menu         = Menu::find()->joinWith('submenu')
            ->where(['nxp_menu.is_show' => 1])
            ->andWhere(['in', 'nxp_menu.id', $menuIdArr])
            ->orderBy(['nxp_menu.sort' => SORT_DESC])
            ->asArray()->all();
        foreach ($menu as &$list) {
            foreach ($list['submenu'] as &$item) {
                $item['rule'] = Route::find()->where(['submenu_id' => $item['id']])
                    ->andWhere(['status' => 1])
                    ->andWhere(['in', 'id', $permission])
                    ->asArray()->all();
            }
        }
        Yii::$app->cache->set('menu', [$roleStr => $menu]);
        return $menu;
    }

}