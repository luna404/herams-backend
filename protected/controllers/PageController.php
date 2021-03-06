<?php


namespace prime\controllers;


use prime\actions\DeleteAction;
use prime\components\Controller;
use prime\controllers\page\Create;
use prime\controllers\page\Update;
use prime\models\ar\Page;
use prime\models\permissions\Permission;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\User;

class PageController extends Controller
{
    public $layout = 'admin';

    public function actions()
    {
        return [
            'update' => Update::class,
            'create' => Create::class,
            'delete' => [
                'class' => DeleteAction::class,
                'permission' => function(User $user, Page $page) {
                    return $user->can(Permission::PERMISSION_ADMIN, $page->project);
                },
                'query' => Page::find(),
                'redirect' => function(Page $page) {
                    return ['project/update', 'id' => $page->project->id];
                }
            ]
        ];
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),
            [
                'access' => [
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ]
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['delete']
                    ]
                ],
            ]
        );
    }
}