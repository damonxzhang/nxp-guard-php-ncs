<?php

namespace backend\controllers;

use backend\models\ExteriorUserRecord;
use backend\models\FormAnswer;
use yii\helpers\Json;
use yii\helpers\Url;
use Yii;

class FormAnswerController extends BaseController
{
    /**
     * 来访人员情况补充确认
     * @return string
     */
    public function actionForm()
    {
        $isPost = Yii::$app->request->isPost;
        if (!$isPost) {
            $eurid = Yii::$app->request->get('eurid' ,'');
            if (!$eurid) {
                return $this->json(100, '数据信息不完整!');
            }
            return $this->render('form', ['eurid' => $eurid, 'areaTabel' => $this->areaTabel, 'tabelTwo' => $this->tabelTwo ]);
        }
        //验证用户请求
        $post = Yii::$app->request->post();
        if (!$post['eurid']) {
            return $this->json(100, '数据信息不完整！');
        }
        $exteriorModel = ExteriorUserRecord::findOne($post['eurid']);
        $answer = [ 'answer1' => $post['answer1'], 'answer2' => $post['answer2'], 'answer3' => $post['answer3'], 'answer4' => $post['answer4'], 'answer5' => $post['answer5'], 'answer6' => $post['answer6'], 'answer7' => $post['answer7'], 'answer8' => $post['answer8'], 'answer9' => $post['answer9'], 'answer10' => $post['answer10'] ];
        $model = new FormAnswer();
        $model->setAttributes([
            'form_id'     => FormAnswer::ONE_FORM,
            'exterior_id' => $exteriorModel->id,
            'answer'      => Json::encode($answer),
            'file'        => $post['signatureFile'],
            'create_user' => $exteriorModel->create_user,
            'create_time' => time(),
        ]);
        if (!$model->save(false)) {
            return $this->json(100, $model->errorToString());
        }
        $redirectUrl = Url::to([ 'exterior-user-record/register-user', 'id' => $exteriorModel->id ]);
        return $this->json(200, '操作成功', $redirectUrl);
    }

    /**
     * @param $reception_id
     * @return string
     */
    public function actionPutFirstForm($reception_id)
    {
        $isPost = Yii::$app->request->isPost;
        if (!$isPost) {
            return $this->render('form', [ 'areaTabel' => $this->areaTabel, 'tabelTwo' => $this->tabelTwo ]);
        }
        $post = Yii::$app->request->post();
        $answer = [ 'answer1' => $post['answer1'], 'answer2' => $post['answer2'], 'answer3' => $post['answer3'], 'answer4' => $post['answer4'], 'answer5' => $post['answer5'], 'answer6' => $post['answer6'], 'answer7' => $post['answer7'], 'answer8' => $post['answer8'], 'answer9' => $post['answer9'], 'answer10' => $post['answer10'] ];
        $model = new FormAnswer();
        $model->setAttributes([
            'form_id'     => FormAnswer::ONE_FORM,
            'exterior_id' => 0,
            'answer'      => Json::encode($answer),
            'file'        => $post['signatureFile'],
            'create_user' => 0,
            'create_time' => time(),
        ]);
        if ($model->save()) {
            $redirectUrl = Url::to([ 'exterior-user-record/register-list-user', 'id' => $model->id, 'reception_id' => $reception_id ]);
            return $this->json(200, '操作成功', $redirectUrl);
        }
        return $this->json(100, $model->errorToString());
    }

    /**
     * @return string
     */
    public function actionRepectionForm()
    {
        $isPost = Yii::$app->request->isPost;
        if (!$isPost) {
            $eurid = Yii::$app->request->get('eurid' ,'');
            if (!$eurid) {
                return $this->json(100, '数据信息不完整!');
            }
            return $this->render('form', ['eurid' => $eurid, 'areaTabel' => $this->areaTabel, 'tabelTwo' => $this->tabelTwo ]);
        }
        $post = Yii::$app->request->post();
        $exteriorModel = ExteriorUserRecord::findOne($post['eurid']);
        $answer = [ 'answer1' => $post['answer1'], 'answer2' => $post['answer2'], 'answer3' => $post['answer3'], 'answer4' => $post['answer4'], 'answer5' => $post['answer5'], 'answer6' => $post['answer6'], 'answer7' => $post['answer7'], 'answer8' => $post['answer8'], 'answer9' => $post['answer9'], 'answer10' => $post['answer10'] ];
        $model = new FormAnswer();
        $model->setAttributes([
            'form_id'     => FormAnswer::ONE_FORM,
            'exterior_id' => $exteriorModel->id,
            'answer'      => Json::encode( $answer),
            'file'        => $post['signatureFile'],
            'create_user' => $exteriorModel->create_user,
            'create_time' => time(),
        ]);
        if (!$model->save(false)) {
            return $this->json(100, $model->errorToString());
        }
        $redirectUrl = Url::to([ 'exterior-user-record/reception-register-user', 'id' => $exteriorModel->id ]);
        return $this->json(200, '操作成功', $redirectUrl);
    }

    public $areaTabel = [
        [ 'area' => '西城区(2个)', 'name' => '月坛街道西便门东大街、永宁胡同' ],
        [ 'area' => '朝阳区(4个)', 'name' => '松榆东里市场，远洋光华国际，王四营粮油批发市场，北京民航机场巴士公司' ],
        [ 'area' => '东城区(1个)', 'name' => '东市口大街33号地下一层' ],
        [ 'area' => '海淀区(2个)', 'name' => '中建资产管理有限公司，玉泉东市场' ],
        [ 'area' => '丰台区(32个)', 'name' => '李记川菜、西罗园街道马家堡东路、西罗园街道角门北路、花乡首经贸育菲园东里1号院、太平桥街道万泉寺南里、银地家园A、B、C区、花乡乡宜兰园A、B、C区、花乡乡天伦锦城、新发地天骄俊园8号院、9号院、青塔秀园、花乡陈留村、潘家庙400号、新发地经营者乐园、新发她批发市场、风格与林、燕保银地、期煦百年' ],
        [ 'area' => '房山区(1个)', 'name' => '长阳镇九州溪雅曦' ],
        [ 'area' => '怀柔区(1个)', 'name' => '万星农副产品市场' ],
        [ 'area' => '平谷区(1个)', 'name' => '东寺渠农副产品市场' ],
        [ 'area' => '顺义区(1个)', 'name' => '石门市场' ],
        [ 'area' => '大兴区(1个)', 'name' => '高米店街道西斯菜公馆、林校路街道车站中里、西红门镇琳海家园' ],
        [ 'area' => '医院(12个)', 'name' => '石景山医院、天坛医院、友谊医院、宣武医院、丰台中西医结合医院、华坛中西医结合医院、她坛医院、朝阳医院、大兴区人民医院、人民医院、海淀医院、武警总医院' ],
        [ 'area' => '交通(1个)', 'name' => '6月2日G194青岛-北京列车' ],
    ];
    public $tabelTwo  = [
        [ 'id' => 0, 'name' => '北京市中高风险地区' ],
        [ 'id' => 1, 'name' => '西城区:月坛街道、金融街街道、白纸坊街道、展览路街道' ],
        [ 'id' => 2, 'name' => '丰台区:花乡(地区)乡、西罗园街道、卢沟桥街道、太平桥街道、新村街通、南苑街通、卢沟桥乡、丰台街道、大红门街通、南苑（地区）乡、长辛店街道' ],
        [ 'id' => 3, 'name' => '房山区:长阳镇' ],
        [ 'id' => 4, 'name' => '大兴区:林校路街道、高米店街道、西红门地区、天宫院街道、庞各庄镇、黄村镇、魏善庄镇、观音寺街道、青云店镇．清源街道' ],
        [ 'id' => 5, 'name' => '东城区：天坛街道、永定门外街道、和平里街道、北新桥街道' ],
        [ 'id' => 6, 'name' => '海淀区：永定路街道、青龙桥街通' ],
        [ 'id' => 7, 'name' => '朝阳区:十八里店' ],
        [ 'id' => 8, 'name' => '石景山区:八宝山街道' ],
        [ 'id' => 9, 'name' => '门头沟区:永定（地区）镇' ],
        [ 'id' => 0, 'name' => '北京市疫情相关菜市场' ],
        [ 'id' => 1, 'name' => '北京新发地批发市场' ],
        [ 'id' => 2, 'name' => '京深海鲜批发市场' ],
        [ 'id' => 3, 'name' => '顺鑫石门批发市场' ],
        [ 'id' => 4, 'name' => '怀柔区万星农副市场' ],
        [ 'id' => 5, 'name' => '平谷区东寺渠农副市场' ],
        [ 'id' => 6, 'name' => '朝阳区松榆东里市场' ],
        [ 'id' => 7, 'name' => '广外天桃红莲菜市场' ],
    ];

}