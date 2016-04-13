<?php

use \app\components\Html;
use prime\models\ar\Setting;

/**
 * @var \yii\data\ActiveDataProvider $projectsDataProvider
 * @var \prime\models\search\Project $projectSearch
 * @var int $closedCount
 * @var \yii\web\View $this
 */


$this->registerJs(<<<SCRIPT
$('.request-access').on('click', function(e){
    e.preventDefault();
    e.stopPropagation();
    var project = $(this).attr('data-project-name');
    var owner = $(this).attr('data-project-owner');
    bootbox.alert('This project can not be accessed. For further information please contact <strong>' + owner + '</strong>.');
});
SCRIPT
);

?>
<div class="col-xs-12">
    <?php

//    \yii\bootstrap\Button::class
    $header = Yii::t('app', 'Your projects')
        .
        \yii\bootstrap\ButtonGroup::widget([
            'options' => [
                'class' => 'pull-right'
            ],
            'buttons' => [
                [
                    'label' => 'New project',
                    'tagName' => 'a',
                    'options' => [
                        'href' => \yii\helpers\Url::to(['projects/new']),
                        'class' => 'btn-primary',
                    ]
                ],
                [
                    'label' => \Yii::t('app', 'Create'),
                    'tagName' => 'a',
                    'options' => [
                        'href' => \yii\helpers\Url::to(['projects/create']),
                        'class' => 'btn-default',
                    ],
                    'visible' => app()->user->can('admin')
                ],
            ]
        ])
        .
        \yii\bootstrap\ButtonGroup::widget([
            'options' => [
                'class' => 'pull-right',
                'style' => ['margin-right' => '10px']
            ],
            'buttons' => [
                [
                    'label' => \Yii::t('app', 'Show closed projects'),
                    'tagName' => 'a',
                    'options' => [
                        'href' => \yii\helpers\Url::to(['/projects/list-closed']),
                        'class' => 'btn-default',
                    ],
                    'visible' => $closedCount > 0
                ],
            ]
        ])
    ;
    echo \kartik\grid\GridView::widget([
        'caption' => $header,
        'pjax' => true,
        'pjaxSettings' => [
            'options' => [
                // Just links in the header.
                'linkSelector' => 'th a'
            ]
        ],
        'layout' => "{items}\n{pager}",
        'filterModel' => $projectSearch,
        'dataProvider' => $projectsDataProvider,
        'columns' => [
            [
                'attribute' => 'id'
            ],
            [
                'attribute' => 'tool_id',
                'value' => 'tool.acronym',
                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                'filter' => $projectSearch->toolsOptions(),
                'filterWidgetOptions' => [
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],

                ],
                'filterInputOptions' => [
                    'placeholder' => \Yii::t('app', 'Select tool')
                ]
            ],
            'title',
            [
                'attribute' => 'country_iso_3',
                'value' => 'country.name',
                'filterType' => \kartik\grid\GridView::FILTER_SELECT2,
                'filter' => $projectSearch->countriesOptions(),
                'filterWidgetOptions' => [
                    'pluginOptions' => [
                        'allowClear' => true,
                        'placeholder' => \Yii::t('app', 'Select country')
                    ]
                ]
            ],
            'locality_name',
            [
                'attribute' => 'created',
                'format' => 'date',
                'filterType' => \kartik\grid\GridView::FILTER_DATE_RANGE,
                'filterWidgetOptions' => [
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'YYYY-MM-DD',
                        ],
                        'allowClear'=>true,
                    ],
                    'pluginEvents' => [
                        "apply.daterangepicker" => "function() { $('.grid-view').yiiGridView('applyFilter'); }"
                    ]
                ],
            ],
            'actions' => include('list/actions.php')
        ]
    ]);
    ?>
</div>
