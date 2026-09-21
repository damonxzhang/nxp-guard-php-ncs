<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "nxp_user".
 *
 * @property string $id
 * @property string $name 用户名
 * @property string $password 密码
 * @property int $create_time 创建时间
 */
class User extends Base
{
    const STATUS_OPEN  = 1;
    const STATUS_CLOSE = 2;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_user';
    }

    public function testInvoke($params,UserInfo $obj){

    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'password'], 'string'],
            ['create_time','integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'name'        => '用户名',
            'password'    => '密码',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @param $data
     * @return array
     * @throws \yii\base\Exception
     */
    public function setAttrs($data)
    {
        $exists = self::find()->where(['name' => trim($data['name'])])->exists();
        if($exists){
            $this->addError('name', '用户名已存在请重新输入!');
            return false;
        }

        $this->name        = trim($data['name']);
        $this->password    = Yii::$app->security->generatePasswordHash(trim($data['password']));
        $this->create_time = time();
        return true;
    }
    /**
     * @param array $data
     * @return false|mixed
     * @throws \yii\base\Exception
     */
    public function create($data = [])
    {
        if (empty($data['name'])) return false;
        $query = self::findOne(['name' => $data['name']]);
        if ($query) {
            return false;
        }
        $this->name        = trim($data['name']);
        $this->password       = Yii::$app->security->generatePasswordHash(trim($data['password']));
        $this->create_time = time();
        if (!$this->save(false)) {
            return false;
        }
        return $this->attributes['id'];
    }
    /**
     * @param $data
     * @return array
     * @throws \yii\base\Exception
     */
    public function edit($data)
    {
        $model       = self::findOne($data['id']);
        $name        = trim($data['name']);
        if($name != $model->name){
            $quest  = self::findOne(['name' => trim($data['name'])]);
            if($quest){
                return $this->arrData(100, '用户名已存在请重新输入!');
            }
        }
        $model->name = trim($data['name']);
        if ($data['password']) {
            $model->password = Yii::$app->security->generatePasswordHash($data['password']);
        }
        if (!$model->save()) {
            $error = array_values($model->getFirstErrors())[0];
            return $this->arrData(100, $error);
        }
        return $this->arrData(200, '更新成功');
    }

    public static function UserExists($id){
        return self::find()->where(['id' => $id])->exists();

    }

    /**
     * 根据用户ID获取用户名称
     * @param $id
     * @return string
     */
    public function getUserName($id)
    {
        $query = self::findOne(['id' => $id]);
        return empty($query) ? '' : $query->name;
    }

    /**
     * 获取交接人ID和名字
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getConnectUserList()
    {
        $query = self::find();
        $query->select(['id', 'name']);
        $query->where(['status' => self::STATUS_OPEN]);
        $query->andWhere(['>', 'id', 1]);
        $query->asArray();
        return $query->all();
    }

    /**
     * 获取用户及角色信息
     * @return array|yii\db\ActiveRecord[]
     */
    public function getUserRoleData()
    {
        $query = self::find();
        $query->select(['nxp_user.id', 'nxp_user.name', 'r.name as role_name']);
        $query->leftJoin(Role::tableName() . ' r', 'r.id = nxp_user.role_id');
        $query->where(['nxp_user.status' => self::STATUS_OPEN]);
        $query->asArray();
        return $query->all();
    }

    /**
     * 获取用户角色名称和角色名称
     * @param string $userId
     * @return array|yii\db\ActiveRecord[]
     */
    public function getUserRoleName($userId)
    {
        $query = self::find();
        $query->select(['nxp_user.name', 'r.name as role_name']);
        $query->leftJoin(Role::tableName() . ' r', 'r.id = nxp_user.role_id');
        $query->andWhere(['nxp_user.id' => $userId]);
        $query->asArray();
        return $query->one();
    }

    public function getUserInfo(){
        return $this->hasOne(UserInfo::className(),['user_id' => 'id']);
    }

    /**
     * 查询用户信息
     * @param bool $select
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getAllUserInfo(array $ids = [],$select = false){
        $query = self::find()->alias('U');
        if($select){
            $query->select($select);
        }
        $query->innerJoin(['UI' => UserInfo::tableName()],'U.user_id = UI.id');
        $query->filterWhere(['UI.id' => $ids]);
        return $query->asArray()->all();
    }
}
