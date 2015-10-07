<?php

use \app\components\Form;
use \app\components\ActiveForm;
use app\components\Html;

$this->title = Yii::t('app', 'Create project');
?>

<div class="col-xs-12">
    <?php
    $form = ActiveForm::begin([
        'id' => 'create-project',
        'method' => 'POST',
        "type" => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => [
            'showLabels' => true,
            'defaultPlaceholder' => false
        ]
    ]);

    echo \app\components\Form::widget([
        'form' => $form,
        'model' => $model,
        'columns' => 1,
        "attributes" => [
            'title' => [
                'type' => Form::INPUT_TEXT,
            ],
            'description' => [
                'type' => Form::INPUT_TEXTAREA
            ],
            'owner_id' => [
                'label' => \Yii::t('app', 'Owner'),
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => \yii\helpers\ArrayHelper::map(\prime\models\User::find()->all(), 'id', 'name')
            ],
            'tool_id' => [
                'label' => \Yii::t('app', 'Tool'),
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => \yii\helpers\ArrayHelper::map(\prime\models\Tool::find()->all(), 'id', 'title')
            ],
            'data_survey_eid' => [
                'type' => Form::INPUT_HTML5,
                'html5type' => 'number'
            ],
            'actions' => [
                'type' => Form::INPUT_RAW,
                'value' =>
                    Html::submitButton(\Yii::t('app', 'Submit'), ['class' => 'btn btn-primary col-xs-12'])
            ]
        ]
    ]);

    $form->end();
    ?>
</div>