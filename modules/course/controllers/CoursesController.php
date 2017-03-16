<?php
/*****************************************************************************************
 * EduSec  Open Source Edition is a School / College management system developed by
 * RUDRA SOFTECH. Copyright (C) 2010-2015 RUDRA SOFTECH.

 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses. 

 * You can contact RUDRA SOFTECH, 1st floor Geeta Ceramics, 
 * Opp. Thakkarnagar BRTS station, Ahmedbad - 382350, India or
 * at email address info@rudrasoftech.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * RUDRA SOFTECH" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by RUDRA SOFTECH".
 *****************************************************************************************/

/**
 * @package EduSec.modules.course.controllers 
 */

namespace app\modules\course\controllers;

use Yii;
use app\modules\course\models\Courses;
use app\modules\course\models\Batches;
use app\modules\course\models\Section;
use app\modules\course\models\CoursesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\GetUserId;
use yii\bootstrap\ActiveForm;
use yii\helpers\Json;
use yii\web\HttpException;
use pheme\grid\actions\ToggleAction;


class CoursesController extends Controller
{

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actions()
    {
	    return [
		'toggle' => [
		    'class' => ToggleAction::className(),
		    'modelClass' => 'app\modules\course\models\Courses',
		    'attribute' => 'is_status',
		    // Uncomment to enable flash messages
		    'setFlash' => true,
		],
	    ];
    }

    public function actionIndex()
    {
        $searchModel = new CoursesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new Courses();
        $batch = new Batches();
        $section = new Section();

        if ($model->load(Yii::$app->request->post())) {
            $model->attributes = $_POST['Courses'];
            $model->created_by = Yii::$app->getid->getId();
            $model->created_at = new \yii\db\Expression('NOW()');

            if ($model->save())
                // TODO-IB : add batches otomatis berdasarkan batch yang aktif beserta info klass yang baru di add
                return $this->redirect(['index']);
            else
                return $this->render('create', ['model' => $model]);
        }
        else {
            return $this->render('create', [
                'model' => $model, 'batch' => $batch, 'section' => $section,
            ]);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

	if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
              	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ActiveForm::validate($model);
         }
        if ($model->load(Yii::$app->request->post())) {

		$model->attributes = $_POST['Courses'];
		$model->updated_at = new \yii\db\Expression('NOW()');
		$model->updated_by = Yii::$app->getid->getId();
		$model->save();
		return $this->redirect(['view', 'id' => $model->course_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    public function actionDelete($id)
    {
	$batch = Batches::find()->where('batch_course_id='.$id.' AND is_status!=2')->exists();
	if($batch)
	{
		$err_msg = 'Batch';
	}
	else
	{
		$err_msg = '';
	}

	if(!empty($err_msg))
		throw new HttpException(400,'You can not delete this record because it is used in '.$err_msg.' table.');
	else  {
        	$model = Courses::findOne($id);
		$model->is_status = 2;
		$model->update();
	}

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Courses::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
