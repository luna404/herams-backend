<?php

use \app\components\Html;
use prime\models\ar\Setting;

/**
 * @var \yii\data\ActiveDataProvider $projectsDataProvider
 * @var \prime\models\search\Project $projectSearch
 */

?>
<div class="col-xs-12">
    <?php

  echo \kartik\grid\GridView::widget([
        'caption' => include('list/header.php'),
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