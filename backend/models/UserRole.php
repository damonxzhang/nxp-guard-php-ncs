<?php

namespace app\models;

use backend\models\Role;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "nxp_user_role".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $role_id
 */
class UserRole extends \backend\models\Base
{

    const GUARD_ROLE = 18;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_user_role';
    }

    static public $isAdmin;

    public static function getRoleList($user_id){
        $role = self::find()->select('role_id')
            ->where(['user_id' => $user_id])
            ->column();
        if($role && is_array($role)){
            return $role;
        }else{
            return [];
        }
    }

    /**
     * @param int $adminRole
     * @return bool
     */
    public static function isAdmin($adminRole = 1){

        if(!self::$isAdmin){
            $exists = self::find()->where(['role_id' => $adminRole,'user_id' => Yii::$app->user->id])->exists();
            if($exists){
                self::$isAdmin = true;
            }else{
                self::$isAdmin = false;
            }
        }

        return self::$isAdmin;
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'role_id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'role_id' => 'Role ID',
        ];
    }
}
