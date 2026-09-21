<?php

namespace backend\controllers;

use app\models\UserRole;
use backend\models\FileInfo;
use backend\models\LenelBadge;
use backend\models\LenelEmp;
use backend\models\LenelUdfemp;
use backend\models\LevelDept;
use backend\models\Role;
use backend\models\search\UserInfoSearch;
use backend\models\User;
use backend\models\UserInfo;
use PhpOffice\PhpSpreadsheet\Calculation\Exception;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class UserInfoController extends BaseController
{
    /**
     * 列表
     * @return string
     */
    public function actionIndex()
    {
        $request      = Yii::$app->request->queryParams;
        $searchModel  = new UserInfoSearch();
        $data         = $searchModel->search($request);
        $totalCount   = $data->totalCount;
        $dataProvider = $data->getModels();
        $pagination   = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => 20,
        ]);
        return $this->render('index', [
            'uid'          => Yii::$app->user->id,
            'request'      => $request,
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
            'pagination'   => $pagination,
        ]);
    }

    /**
     * 详情
     * @return string
     */
    public function actionDetail()
    {
        $id    = Yii::$app->request->get('id');
        $model = UserInfo::findOne($id);
        return $this->render('detail', [
            'model' => $model,
        ]);
    }

    /**
     * 添加
     * @return string
     */
    public function actionCreate()
    {
        if (Yii::$app->request->isPost) {
            $post  = Yii::$app->request->post();
            $model = new UserInfo();
            $res   = $model->create($post);
            if ($res['status'] != 200) {
                return $this->json(100, $res['msg']);
            }
            return $this->json(200, '添加成功!');
        }
        $roles = Role::find()->where(['>', 'id', 1])->orderBy('id asc')->all();
        return $this->render('create', [
            'roles' => $roles
        ]);
    }

    /**
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionUpdate()
    {
        if (Yii::$app->request->isPost) {
            $post        = Yii::$app->request->post();
            $fileDataArr = ArrayHelper::getValue($post, 'file_data', []);
            if (count($fileDataArr) >= 2) {
                return $this->json(100, '只能上传一张图片!');
            }
            $post['file_id'] = Json::encode($fileDataArr);
            $MemberModel     = new UserInfo();
            $res             = $MemberModel->edit($post);
            if ($res['status'] != 200) {
                return $this->json(100, $res['msg']);
            }
            return $this->json(200, $res['msg']);
        }

        $id    = Yii::$app->request->get('id');
        $model = UserInfo::findOne($id);
        $roles = Role::find()->where(['>', 'id', 1])->orderBy('id asc')->all();
        //照片
        $files = [];
        if (!empty($model->file_id)) {
            $files = (new FileInfo())->getPathByIds(Json::decode($model->file_id));
        }
        return $this->render('update', [
            'roles' => $roles,
            'model' => $model,
            'files' => $files
        ]);
    }

    /**
     * 上传文件
     * @return string
     */
    public function actionUpload()
    {
        $fileInfo  = $_FILES['file'];
        $fileError = $fileInfo['error'];
        if ($fileError != 0) {
            return $this->json(100, '文件上传失败');
        }
        $path     = 'uploads/' . date('Ymd') . '/camera/';
        $fileName = time() . '.png';
        $filePath = $path . $fileName;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);//递归方式创建数组
        }
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $filePath)) {
            $fileModel = new FileInfo();
            $fileModel->setAttributes([
                'name' => $fileName,
                'type' => '.png',
                'size' => '',
                'path' => '/' . $filePath
            ]);
            if (!$fileModel->save(false)) {
                return $this->json(100, '图片上传失败!');
            }
            return $this->json(200, '上传完成！', ['file_id' => $fileModel->id, 'img_url' => '/' . $filePath]);
        }
        return $this->json(100, '文件上传失败');
    }

    /**
     * 批量更新数据库数据
     * @return int
     * @throws \yii\base\Exception
     */
    public function updateUserInfo()
    {
        $query = LenelEmp::find();
        $i     = 0;
        foreach ($query->each(30) as $value) {
            $i++;
            $userInfoCheck = UserInfo::findOne(['emp_id' => $value['ID']]);
            if ($userInfoCheck) {
                $badgeCheck = LenelBadge::findOne(['EMPID' => $value['ID']]);
                if ($badgeCheck) {
                    $userInfoCheck->type = $badgeCheck->TYPE;
                    $userInfoCheck->save(false);
                }
            } else {
                $user            = new User();
                $userAdd['name'] = $userAdd['password'] = $value['SSNO'];
                $uid             = $user->create($userAdd);
                if ($uid) {
                    $badge             = LenelBadge::findOne(['EMPID' => $value['ID']]);
                    $userInfo          = new UserInfo();
                    $userInfo->user_id = $uid;
                    $userInfo->emp_id  = $value['ID'];
                    $userInfo->pid     = $value['SSNO'];
                    $userInfo->type    = $badge->TYPE;
                    $userInfo->status  = UserInfo::STATUS_OPEN;
                    $userInfo->name    = $value['MIDNAME'];
                    $userInfo->ename   = $value['FIRSTNAME'] . $value['LASTNAME'];
                    $udfemp            = LenelUdfemp::findOne(['ID' => $value['ID']]);
                    if ($udfemp) {
                        $dept = LevelDept::findOne(['ID' => $udfemp->DEPT]);
                        if ($dept) {
                            $userInfo->department_number = $dept->NAME;
                        }
                    }
                    if (!$userInfo->save(false)) {
                        continue;
                    }
                    $userRole          = new UserRole();
                    $userRole->user_id = $uid;
                    $userRole->role_id = 2;
                    if (!$userRole->save(false)) {
                        continue;
                    }
                }
            }
        }
        return $i;
    }

    /**
     * 添加数据
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionInsert()
    {
        $query = LenelEmp::find();
        $i     = 0;
        foreach ($query->each(30) as $value) {
            $i++;
            $user            = new User();
            $userAdd['name'] = $userAdd['password'] = $value['SSNO'];
            $uid             = $user->create($userAdd);
            if ($uid) {
                $userInfo          = new UserInfo();
                $userInfo->user_id = $uid;
                $userInfo->emp_id  = $value['ID'];
                $userInfo->pid     = $value['SSNO'];
                $userInfo->status  = UserInfo::STATUS_OPEN;
                $userInfo->name    = $value['MIDNAME'];
                $userInfo->ename   = $value['FIRSTNAME'] . $value['LASTNAME'];
                $udfemp            = LenelUdfemp::findOne(['ID' => $value['ID']]);
                if ($udfemp) {
                    $dept = LevelDept::findOne(['ID' => $udfemp->DEPT]);
                    if ($dept) {
                        $userInfo->department_number = $dept->NAME;
                    }
                }
                if (!$userInfo->save(false)) {
                    continue;
                }
                $userRole          = new UserRole();
                $userRole->user_id = $uid;
                $userRole->role_id = 2;
                if (!$userRole->save(false)) {
                    continue;
                }
            }
        }
        return $i;
    }


    /**
     * 创建密码
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionCreatePassword()
    {
        if (Yii::$app->request->isPost) {
            $post     = Yii::$app->request->post();
            $userInfo = UserInfo::findOne($post['user_id']);

            $user = new User();
            $tran = Yii::$app->db->beginTransaction();
            try {
                if ($user->setAttrs($post) && $user->save()) {
                    $userInfo->user_id = $user->id;
                    if (!$userInfo->save(false)) {
                        throw new Exception($userInfo->errorToString());
                    }
                } else {
                    throw new Exception($user->errorToString());
                }
                $tran->commit();
                return $this->json(200, '保存成功！');
            } catch (\Exception $e) {
                $tran->rollBack();
                return $this->json(100, $e->getMessage());
            }
        }
        $id = Yii::$app->request->get('id');
        return $this->render('create-password', ['id' => $id]);
    }

    /**
     * 编辑密码
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionUpdatePassword()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();
            if ($post['user_id'] == 1) {
                return $this->json(100, 'admin禁止修改');
            }
            $model       = User::findOne(['id' => $post['user_id']]);
            $model->name = $post['name'];
            if ($post['password']) {
                if ($post['password'] === $post['rePassword']) {
                    $model->password = Yii::$app->security->generatePasswordHash($post['password']);
                } else {
                    return $this->json(100, '两次输入的密码不一致!');
                }
            }
            if (!$model->save()) {
                return $this->json(100, '修改密码失败!');
            }
            return $this->json(200, '修改密码成功!');
        }
        $userId   = Yii::$app->request->get('id');
        $userInfo = UserInfo::findOne(['id' => $userId]);
        $user     = User::findOne(['id' => $userInfo->user_id]);
        return $this->render('update-password', ['userId' => $userInfo->user_id, 'user' => $user]);
    }

    /**
     * 修改状态
     * @return string
     */
    public function actionChangeStatus()
    {
        $post           = Yii::$app->request->post();
        $member         = UserInfo::findOne($post['id']);
        $status         = $post['status'] == 1 ? 2 : 1;
        $member->status = $status;
        if (!$member->save(false)) {
            return $this->json(100, '操作失败');
        }
        return $this->json(200, '操作成功');
    }


    /**
     * 保存用户角色
     * @param $uid
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionChangeRole($uid)
    {
        $roleList = Role::getRoleList();
        $userRole = UserRole::getRoleList($uid);
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post();

            if (empty($post['ids'])) {
                return $this->json(100, '请选择角色');
            }
            $params = [];
            foreach ($post['ids'] as $va) {
                if (in_array($va, $userRole)) {
                    continue;
                }
                $params[] = [
                    'user_id'     => $uid,
                    'role_id'     => $va,
                    'create_time' => time(),
                    'update_time' => time(),
                ];
            }

            $tran = Yii::$app->db->beginTransaction();
            try {

                //保存管理数据
                $res = (new UserRole)->batchInsert(UserRole::tableName(), ['user_id', 'role_id', 'create_time', 'update_time'], $params);

                if ($res === false) {
                    throw new Exception('保存失败！');
                }
                $callIds = [];

                if (!empty($userRole) && count($userRole) > 0) {

                    //被取消选中的数据
                    foreach ($userRole as $roleId) {
                        if (in_array($roleId, $post['ids'])) {
                            continue;
                        }
                        $callIds[] = $roleId;
                    }
                    if (count($callIds) > 0) {
                        //删除
                        $result = UserRole::deleteAll(['user_id' => $uid, 'role_id' => $callIds]);
                        if ($result === false) {
                            throw new Exception('保存失败！');
                        }
                    }
                }
            } catch (\Exception $e) {
                $tran->rollBack();
                return $this->json(100, $e->getMessage());
            }
            if (RBAC_UPDATE) {
                Yii::$app->cache->set('menu', null);
            }
            $tran->commit();
            return $this->json(200, '操作成功！');
        }
        return $this->render('change_role', [
            'roleList' => $roleList,
            'userRole' => $userRole
        ]);
    }


    /**
     * 导出数据
     */
    public function actionExport()
    {
        $search = Yii::$app->request->get('search');
        $query  = UserInfo::find();
        $data   = $query->all();
        $header = ['ID', '昵称','邮箱', '创建时间', '状态'];
        $list[] = implode(',', $header);
        if ($data) {
            foreach ($data as $val) {
                $item   = [
                    $val->id,
                    $val->nickname,
                    $val->email,
                    date('Y-m-d H:i:s', $val->create_time),
                    $val->status == 1 ? '启用' : '禁用',
                ];
                $list[] = implode(',', $item);
            }
        }
        $fileName = '用户列表_' . date('Y-m-d H:i');
        return $this->csvExport($list, $fileName);
    }

    /**
     * 根据用户名称搜索用户
     * @param null $area
     * @return false|string
     */
    public function actionSearchByName($area = null)
    {
        $name   = Yii::$app->request->get('keyword');
        $result = [
            'code'    => 1,
            'msg'     => '没有数据',
            'results' => []
        ];

        $query = UserInfo::find()->select('id,name as text');
        if ($name) {
            $query->where(['like', 'name', $name]);
        }
        if ($area) {
            $query->andWhere(['area' => $area]);
        }
        $query->andWhere(['>', 'role_id', 1]);
        $params = $query->limit(20)->asArray()->all();
        if ($params) {
            $result['code']    = 0;
            $result['msg']     = 'success';
            $result['results'] = $params;
        }
        return Json::encode($result);
    }

}