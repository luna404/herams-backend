<?php
/** @var \prime\models\ar\Project $model */

use app\components\Form;
use kartik\widgets\ActiveForm;
use yii\bootstrap\Html;

$this->params['breadcrumbs'][] = [
    'label' => \Yii::t('app', 'Admin dashboard'),
    'url' => ['/admin']
];
$this->params['breadcrumbs'][] = [
    'label' => \Yii::t('app', 'Projects'),
    'url' => ['/project']
];
$this->title = Yii::t('app', 'Create project');

$this->params['breadcrumbs'][] = [
    'label' => $this->title
];

?>
<div class="col-xs-12">
    <?php
    $form = ActiveForm::begin([
        "type" => ActiveForm::TYPE_HORIZONTAL,
        'formConfig' => [
            'showLabels' => true,
            'defaultPlaceholder' => false
        ]
    ]);
    echo \app\components\Form::widget([
        'form' => $form,
        'model' => $model,
        "attributes" => [
            'title' => [
                'type' => Form::INPUT_TEXT,
            ],
            'base_survey_eid' => [
                'type' => Form::INPUT_DROPDOWN_LIST,
                'items' => $model->dataSurveyOptions(),
                'options' => [
                    'prompt' => ''
                ]
            ],
            [
                'type' => Form::INPUT_RAW,
                'value' => \yii\bootstrap\ButtonGroup::widget([
                    'buttons' => [
                        Html::submitButton(\Yii::t('app', 'Create project'), ['class' => 'btn btn-primary'])
                    ]
                ])
            ]
        ],
    ]);
    $form->end();
    ?>
</div>