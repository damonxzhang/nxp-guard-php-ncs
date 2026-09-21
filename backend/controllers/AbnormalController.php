<?php
namespace backend\controllers;

use backend\models\LenelBadge;
use backend\models\LenelEvents;
use backend\models\LenelReader;

use backend\models\ExteriorUserRecord;
use yii\data\Pagination;
use Yii;
use yii\helpers\ArrayHelper;
use backend\models\AbnormalCard;
use backend\models\LenelEmp;

class AbnormalController extends BaseController
{
   
    /**
     * @return string
     */
    public function actionIndex()
    {
        $request    = Yii::$app->request->queryParams;
        $query      = $this->search($request);
        $queryData  = clone $query;
        $pagination = new Pagination(['totalCount' =>  $queryData->count()]);
        $data       = $query->offset($pagination->offset)
                            ->limit($pagination->limit)
                            ->asArray()
                            ->all();
        return $this->render('index', [
            'data'       => $data,
            'request'    => $request,
            'totalCount' => $queryData->count(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @param $params
     * @return \yii\db\ActiveQuery
     * @date 2021/7/13 13:40
     */
    public function search($params)
    {
        try {
            $startTime = date('Y-m-d H:i:s', time());
            $query = LenelEvents::find();
            $query->alias('evn');
            $query->select(['evn.EMPID','evn.MACHINE', 'evn.DEVID','evn.EVENT_TIME_UTC' ]);
            $query->leftJoin(LenelBadge::tableName() . ' lb', 'evn.EMPID = lb.EMPID');
            $query->andWhere(['in', 'lb.TYPE', [LenelBadge::TYPE_SNAP1,LenelBadge::TYPE_SNAP2,LenelBadge::TYPE_SNAP3 ]]);
            $query->andWhere([ '>', 'evn.EMPID', 0 ]);
            $query->andWhere([ '>', 'EVENT_TIME_UTC', date('Y-m-d H:i:s',  strtotime("$startTime -28803second")) ]);
            $query->andWhere([ '<=', 'EVENT_TIME_UTC', date('Y-m-d H:i:s',  strtotime("$startTime -28800second")) ]);
            $query->asArray();
            $data = $query->all();
        }catch (\Exception $e) {
            $data = [];
        }
        
        $recordQuery = ExteriorUserRecord::find();
        $recordQuery->select(['user_card','real_get_out_time']);
        $recordQuery->where(['status' => ExteriorUserRecord::STATUS_GO_OUT ]);
        $recordQuery->andWhere(['>=','real_get_out_time' ,strtotime("-3 second")]);
        $recordQuery->asArray();
        $recordData = $recordQuery->all();
        if (!empty($data) && !empty($recordData)) {
            $userCard      = ArrayHelper::getColumn($recordData, 'user_card');
            $userCardMap   = ArrayHelper::map($recordData, 'user_card', 'real_get_out_time');
             foreach ($data as $key => $value) {
                if (In_array($value['EMPID'],$userCard)) {
                    $time = $value['EVENT_TIME_UTC'];
                    if (strtotime("$time +28800second") > $userCardMap[$value['EMPID']]) {
                       $model              = new AbnormalCard();
                       $model->empid       = $value['EMPID'];
                       $model->machine     = $value['MACHINE'];
                       $model->devid       = $value['DEVID'];
                       $eventTime          = $value['EVENT_TIME_UTC']; 
                       $model->event_time  = date('Y-m-d H:i:s',  strtotime("$eventTime +28800second"));
                       $model->create_time = date('Y-m-d H:i:s', time());
                       $empModel           = LenelEmp::findOne(['ID' => $value['EMPID']]);
                       $model->card_name   = $empModel ? $empModel->LASTNAME : '';
                       $reader             = LenelReader::findOne(['READERID'=>$value['DEVID'],'PANELID'=>$value['MACHINE']]);
                       $model->reader      = $reader ? $reader->READERDESC : '';
                       $model->save(false);
                    }
                }
             }
        }
        // $query=AbnormalCard::find();
        // $query->alias('e');
        // $query->select(['e.*','r.READERDESC']);
        // $query->leftJoin(['r' => LenelReader::tableName()], 'e.devid = r.READERID and e.machine = r.PANELID');
        return AbnormalCard::find();
    }
    
}