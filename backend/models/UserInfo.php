<?php

namespace backend\models;

use yii\helpers\ArrayHelper;
use common\models\Utility;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "nxp_user_info".
 *
 * @property int $id
 * @property int $pid
 * @property int $user_id
 * @property int $emp_id
 * @property int $status
 * @property int $tel
 * @property string $name
 * @property string $ename
 * @property string $device
 * @property string $department_number
 */
class UserInfo extends Base implements IdentityInterface
{
    const STATUS_OPEN  = 1;
    const STATUS_CLOSE = 2;

    //启用状态
    public static $statusArr = [
        self::STATUS_OPEN  => '启用',
        self::STATUS_CLOSE => '禁用',
    ];

    public static $typeArr = [
        1 => 'Employee',
        3 => 'Temporary',
        4 => 'Contractor',
        5 => 'Temp.CON',
        6 => 'Temp.EE',
        7 => 'Temp.Visitor',
        9 => 'Authorized CON',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'nxp_user_info';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'user_id','card_true'], 'integer'],
            [['pid', 'emp_id','name', 'ename', 'device', 'department_number', 'file_id'], 'string'],
            [['pid', 'emp_id'], 'unique'],
            [[ 'type'], 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                => 'ID',
            'pid'               => 'PID',
            'user_id'           => 'userId',
            'emp_id'            => 'EMPID',
            'card_true'         => '临时卡账号筛选标识',
            'status'            => '状态',
            'file_id'           => '图片',
            'type'              => '员工标识',
            'name'              => '中文名称',
            'device'            => '卡机位置',
            'ename'             => '英文名称',
            'department_number' => '部门号',
        ];
    }

    /**
     * @param $id
     * @return string
     */
    public static function getName($id)
    {
        $model = self::findOne($id);
        return $model ? $model->name : '';
    }

    /**
     * 添加
     * @param $data
     * @return array
     */
    public function create($data)
    {
        $this->keyValue($data);
        $this->create_time = time();
        $error             = $this->chkAttributes($data);
        if ($error) {
            return $this->arrData(100, Utility::arrayToString($error));
        }
        if (!$this->save()) {
            $error = array_values($this->getFirstErrors())[0];
            return $this->arrData(100, $error);
        }
        return $this->arrData(200, $this->attributes['id']);
    }

    /**
     * 修改
     * @param $data
     * @return array
     */
    public function edit($data)
    {
        $model                    = self::findOne($data['id']);
        $model->pid               = ArrayHelper::getValue($data, 'pid');
        $model->status            = ArrayHelper::getValue($data, 'status');
        $model->file_id           = ArrayHelper::getValue($data, 'file_id');
        $model->name              = ArrayHelper::getValue($data, 'name');
        $model->department_number = ArrayHelper::getValue($data, 'department_number');
        if (!$model->save()) {
            $error = array_values($model->getFirstErrors())[0];
            return $this->arrData(100, $error);
        }
        return $this->arrData(200, '更新成功');
    }

    /**
     * 获取提交的数据
     * @param $data
     */
    public function keyValue($data)
    {
        $this->pid               = ArrayHelper::getValue($data, 'pid');
        $this->status            = ArrayHelper::getValue($data, 'status');
        $this->name              = ArrayHelper::getValue($data, 'name');
        $this->type              = ArrayHelper::getValue($data, 'type');
        $this->ename             = ArrayHelper::getValue($data, 'ename');
        $this->department_number = ArrayHelper::getValue($data, 'department_number');

    }

    /**
     * 根据用户ID获取用户名称
     * @param $id
     * @return string
     */
    public function getUserName($id)
    {
        if (empty($id)) {
            return '';
        }
        $query = self::findOne(['id' => $id]);
        return empty($query) ? '' : $query->name;
    }

    /**
     * 查询用户分部
     * @param $id
     * @return bool
     */
    public function getUserDepartment($id)
    {
        $section = self::find()->select('department_number')->where(['id' => $id])->scalar();
        if ($section) {
            return $section;
        }
        return false;
    }

    /**
     * 根据id查询用户名称
     * @param $ids
     * @return array|bool
     */
    public static function getUserNameByIdArray($ids)
    {
        if (empty($ids)) {
            return false;
        }
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        $userNames = self::find()->select('name')->where(['id' => $ids])->column();
        if ($userNames) {
            return $userNames;
        }
        return false;
    }

    /**
     * 获取人员信息
     * @param array $param
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getConnectUserList($param = [])
    {
        $query = self::find();
        $query->select(['id', 'name']);
        $query->where(['status' => self::STATUS_OPEN]);
        $query->andWhere(['>', 'id', 1]);
        $query->andFilterWhere(['in', 'position', $param]);
        $query->asArray();
        return $query->all();
    }

    /**
     * 下拉搜索用户列表
     * @param $section
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function UserDropDownList($section = null)
    {
        $query = self::find();
        $query->select(['id', 'name']);
        $query->where(['status' => self::STATUS_OPEN]);
        $query->andWhere(['>', 'id', 1]);
        $query->andFilterWhere(['section' => $section]);
        $query->asArray();
        return $query->all();
    }

    /**
     * 获取日志交接人员信息
     * @param $area
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getBlogUserList($area = null)
    {
        $query = self::find();
        $query->select(['id', 'name']);
        $query->where(['status' => self::STATUS_OPEN]);
        $query->andWhere(['>', 'id', 1]);
        $query->andFilterWhere(['area' => $area]);
        $query->asArray();
        return $query->all();
    }

    /**
     * 根据识别号查询用户
     * @param $badge_code
     * @return false|null|string
     */
    public function findUserByBadgeCode($badge_code)
    {
        return self::find()->select('id')->where(['badge_code' => $badge_code])->one();
    }

    /**
     * 默认搜索的用户信息
     */
    public function getSearchDefaultUser($section)
    {
        $query = self::find();
        $query->select(['id', 'name']);
        $query->where(['status' => self::STATUS_OPEN]);
        $query->andWhere(['>', 'id', 1]);
        $query->andWhere(['section' => $section]);
        $query->asArray();
        return $query->all();
    }

    /**
     * 根据分组筛选交接人
     * @param $params
     * @return array|\yii\db\ActiveRecord[]
     */
    public function BySearchGetBlogUserList($params)
    {

        $query = self::find();
        $query->select(['id', 'name as text']);
        $query->where(['status' => self::STATUS_OPEN]);
        $query->andWhere(['>', 'id', 1]);
        !empty($params['section']) && $query->andWhere(['section' => $params['section']]);
        !empty($params['area']) && $query->andWhere(['area' => $params['area']]);
        !empty($params['position']) && $query->andWhere(['position' => $params['position']]);
        !empty($params['shift']) && $query->andWhere(['shift' => $params['shift']]);
        !empty($params['user-stations']) && $query->andWhere(['station_id' => $params['user-stations']]);
        $query->asArray();
        return $query->all();
    }

    /**
     * 获取需要发送人邮箱地址
     * @param array $userIdList
     * @return array|bool
     */
    public function getUserEmail($userIdList = [])
    {
        if (empty($userIdList)) {
            return false;
        }
        $model = self::find();
        $model->select(['name', 'email']);
        $model->where(['in', 'id', $userIdList]);
        $model->asArray();
        return $model->all();
    }

    /**
     * 使用识别号查询用户信息，没有返回空对象
     * @param $wbi_code
     * @return $this|UserInfo|bool|null
     */
    public function getUserByWbiCode($wbi_code)
    {

        if (empty($wbi_code)) {
            return false;
        }
        $data = $this->findOne(['wbi_code' => $wbi_code]);
        if (!empty($data)) {
            return $data;
        } else {
            return $this;
        }
    }

    /**
     * 使用手机号或识别卡号查询用户
     * @param $key
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getUserByPhoneOrCode($key)
    {
        return self::find()->select('id,department_number,tel,pid,name')->where(['tel' => $key])->orWhere(['pid' => $key])->one();
    }

    /**
     * 查询用户直属领导的id
     * @param $id
     * @return array
     */
    public function getLeaderUids($id)
    {
        return self::find()->select('leader_uid')
            ->where(['id' => $id])
            ->column();
    }

    /**
     * 根据id获取用户班别
     * @param $ids
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getUserShiftById($ids)
    {
        return $this->find()->select('id,shift')->where(['id' => $ids])->all();
    }


    public static function findIdentity($id)
    {
        return static::findOne($id);
    }


    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }

    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }
   
    /**
   * 获取临时卡资格人员
   * @return array|\yii\db\ActiveRecord[]
   * @date 2021/6/10 22:09
   */
  public function getUserCardList()
  {
    $query = self::find();
    $query->select(['id', 'name']);
    $query->where(['card_true' => 1]);
    $query->asArray();
    return $query->all();
  }

}
