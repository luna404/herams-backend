<?php

use prime\helpers\Icon;
use prime\models\ar\Project;
use yii\helpers\Html;

echo Html::beginTag('div', ['class' => 'menu']);
    echo Html::img("/img/HeRAMS.png");
    echo Html::tag('hr');
    echo Html::beginTag('nav');
        $projects = [];
        echo Html::a('Projects', ['/project/index']);
        if (\Yii::$app->user->can('admin')) {
            echo Html::a('Users', ['/user/admin/index']);
        }
        echo Html::a('Limesurvey', ['/admin/limesurvey']);
    echo Html::endTag('nav');
    echo $this->render('//footer', ['projects' => Project::find()->all()]);

echo Html::endTag('div');