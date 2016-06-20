<?php

use app\components\Html;

/**
 * @var string $renderUrl
 * @var \yii\web\View $this
 * @var \prime\interfaces\ReportGeneratorInterface $generator
 * @var string $reportGenerator
 * @var int $projectId
 */

$this->params['subMenu']['items'] = [
    [
        'label' => Html::icon(\prime\models\ar\Setting::get('icons.configure', 'edit')),
        'options' => [
            'class' => 'icon',
            'title' => \Yii::t('app', 'Configure report'),
        ],
        'url' => [
            'reports/configure',
            'reportGenerator' => $reportGenerator,
            'projectId' => $projectId
        ],
        'visible' => $generator instanceof \prime\interfaces\ConfigurableGeneratorInterface
    ],
    [
        'options' => [
            'class' => 'icon',
            'title' => \Yii::t('app', 'Print report'),
        ],

        'label' => Html::icon(\prime\models\ar\Setting::get('icons.print', 'print')),
        'linkOptions' => [
            'onclick' => "$('iframe')[0].contentWindow.print();"
        ]
    ],
    [
    'label' => Html::icon(\prime\models\ar\Setting::get('icons.publish', 'cloud-upload')),
    'options' => [
        'class' => 'icon',
        'title' => \Yii::t('app', 'Publish report'),
    ],
    'url' => [
        'reports/publish',
        'reportGenerator' => $reportGenerator,
        'projectId' => $projectId
    ],
    'linkOptions' => [
        'data-method' => 'post',
        'data-confirm' => \Yii::t('app', 'Are you sure you want to publish this report and save it to the marketplace?')
    ]
],


];
// Dynamically resize iframe.
$this->registerAssetBundle(\prime\assets\ResizeAsset::class);
$this->registerJs('
        var $iframe = $("iframe");
        var resizer = function(e) {
            console.log("resizer");
            console.log(e);
            $iframe.height($iframe.contents().find("body").height());
            $iframe.width($iframe.contents().find("body").width());
        };

        $iframe.on("load", function() {
            var $body = $iframe.contents().find("body");
            $body.on("mresize", resizer);
            console.log($body);
            $body.trigger("mresize");
        });

    ', $this::POS_READY);
echo Html::tag('iframe', '', ['src' => $finalUrl, 'style' => ['width' => '100%', 'height' => '500px']]);
?>
<style>
    body {
        background-color: grey;
    }

    iframe {
        width: 100%;
        height: 500px;
        margin-bottom: 30px;
        border: 0px;
        overflow-y: hidden;
    }
</style>