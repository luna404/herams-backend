<?php

namespace prime\controllers;

use Befound\Components\DateTime;
use prime\components\Controller;
use prime\models\forms\projects\Share;
use prime\models\permissions\Permission;
use prime\models\Project;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class ProjectsController extends Controller
{
    public $defaultAction = 'list';

    public function actionClose($id)
    {
        $model = Project::loadOne($id, Permission::PERMISSION_WRITE);
        $model->scenario = 'close';
        if(app()->request->isDelete) {
            $model->closed = (new DateTime())->format(DateTime::MYSQL_DATETIME);
            if($model->save()) {
                app()->session->setFlash(
                    'projectClosed',
                    [
                        'type' => \kartik\widgets\Growl::TYPE_SUCCESS,
                        'text' => \Yii::t('app', "Project <strong>{modelName}</strong> has been closed.", ['modelName' => $model->title]),
                        'icon' => 'glyphicon glyphicon-trash'
                    ]
                );
                return $this->redirect(['/projects/list']);
            }
        }
        if(isset($model)) {
            return $this->redirect(['/projects/read', 'id' => $model->id]);
        } else {
            return $this->redirect(['/projects/list']);
        }
    }

    public function actionCreate()
    {
        $model = new Project();
        $model->scenario = 'create';

        if (app()->request->isPost) {
            if($model->load(app()->request->data()) && $model->save()) {
                app()->session->setFlash(
                    'projectCreated',
                    [
                        'type' => \kartik\widgets\Growl::TYPE_SUCCESS,
                        'text' => \Yii::t('app', "Project <strong>{modelName}</strong> has been created.", ['modelName' => $model->title]),
                        'icon' => 'glyphicon glyphicon-ok'
                    ]
                );
                return $this->redirect(['projects/read', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' =>  $model
        ]);
    }

    public function actionList()
    {
        $projectsDataProvider = new ActiveDataProvider([
            'query' => app()->user->identity->getProjects()
        ]);

        return $this->render('list', [
            'projectsDataProvider' => $projectsDataProvider
        ]);
    }

    public function actionProgress($id)
    {
        $project = Project::loadOne($id);
        $report = $project->getProgressReport();

        app()->response->setContentType($report->getMimeType());
        app()->response->content = $report->getStream();
        return app()->response;
    }

    public function actionRead($id)
    {
        $project = Project::loadOne($id);

        return $this->render('read', [
            'model' => $project,
        ]);
    }

    public function actionShare($id)
    {
        $project = Project::loadOne($id, Permission::PERMISSION_SHARE);

        $model = new Share([
            'projectId' => $project->id
        ]);

        if(app()->request->isPost) {
            if($model->load(app()->request->data()) && $model->createRecords()) {
                app()->session->setFlash(
                    'projectShared',
                    [
                        'type' => \kartik\widgets\Growl::TYPE_SUCCESS,
                        'text' => \Yii::t('app',
                            "Project <strong>{modelName}</strong> has been shared with: <strong>{users}</strong>",
                            [
                                'modelName' => $model->project->title,
                                'users' => implode(', ', array_map(function($model){return $model->name;}, $model->getUsers()->all()))
                            ]),
                        'icon' => 'glyphicon glyphicon-ok'
                    ]
                );
                return $this->redirect(['projects/read', 'id' => $model->project->id]);
            }
        }

        return $this->render('share', [
            'model' => $model
        ]);
    }

    public function actionUpdate($id)
    {
        $model = Project::loadOne($id, Permission::PERMISSION_WRITE);
        $model->scenario = 'update';

        if(app()->request->isPost) {
            if($model->load(app()->request->data()) && $model->save()) {
                app()->session->setFlash(
                    'projectUpdated',
                    [
                        'type' => \kartik\widgets\Growl::TYPE_SUCCESS,
                        'text' => \Yii::t('app', "Project <strong>{modelName}</strong> has been updated.", ['modelName' => $model->title]),
                        'icon' => 'glyphicon glyphicon-ok'
                    ]
                );
                return $this->redirect(['projects/read', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model
        ]);
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),
            [
                'access' => [
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@']
                        ],
                    ]
                ]
            ]
        );
    }
}