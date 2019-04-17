<?php

namespace prime\controllers;

use prime\actions\DeleteAction;
use prime\components\Controller;
use prime\controllers\project\Create;
use prime\controllers\project\Index;
use prime\controllers\project\Pages;
use prime\controllers\project\Share;
use prime\controllers\project\Summary;
use prime\controllers\project\Update;
use prime\controllers\project\View;
use prime\controllers\project\Workspaces;
use prime\factories\GeneratorFactory;
use prime\models\ar\Project;
use yii\filters\PageCache;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class ProjectController extends Controller
{
    public $layout = 'admin';

    public function actions()
    {
        return [
            'create' => Create::class,
            'update' => Update::class,
            'index' => Index::class,
            'delete' => [
                'class' => DeleteAction::class,
                'query' => Project::find(),
                'redirect' => ['/project']
            ],
            'view' => View::class,
            'summary' => Summary::class,
            'share' => Share::class,
            'workspaces' => Workspaces::class
        ];
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['delete']
                    ]
                ],

                'pageCache' => [
                    'class' => PageCache::class,
                    'enabled' => !YII_DEBUG,
                    'only' => ['summary'],
                    'variations' => [
                        \Yii::$app->request->getQueryParam('id')
                    ],
                    'duration' => 120,
                ],
                'access' => [
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => [
                                'share',
                                'view',
                                'summary',
                                'index',
                                'update',
                                'workspaces',
                                'delete'
                            ],
                            'roles' => ['@'],
                        ],
                    ]
                ]
            ]
        );

    }
}