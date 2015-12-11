<?php

namespace prime\models\mapLayers;

use Befound\Components\DateTime;
use Carbon\Carbon;
use prime\interfaces\ResponseCollectionInterface;
use prime\models\Country;
use prime\models\MapLayer;
use SamIT\LimeSurvey\Interfaces\ResponseInterface;
use yii\web\Controller;
use yii\web\JsExpression;
use yii\web\View;

class CountryGrades extends MapLayer
{
    public $states = [
        'hover' => [
            'borderColor' => 'rgba(100, 100, 100, 1)',
            'borderWidth' => 2
        ]
    ];

    protected $colorScale = [
        'A00' => 'rgba(100, 100, 100, 0.8)',
        'A0' => 'rgba(255, 255, 255, 0)',
        'A1' => 'rgba(255, 255, 0, 1)',
        'A2' => 'rgba(255, 127, 0, 1)',
        'A3' => 'rgba(255, 0, 0, 1)'
    ];

    /** @var ResponseCollectionInterface */
    protected $responses;

    protected function addColorsToData()
    {
        foreach($this->data as &$data) {
            if(!isset($data['color'])) {
                $data['color'] = $this->colorScale[$data['value']];
            }
        }
    }

    public function __construct(ResponseCollectionInterface $responses, $config = [])
    {
        $this->responses = $responses;
        parent::__construct($config);
    }

    public function init()
    {
        $this->allowPointSelect = true;
        $this->joinBy = ['ISO_3_CODE', 'id'];
        $this->name = \Yii::t('app', 'Country Grades');
        $this->showInLegend = true;
        $this->addPointEventHandler('select', new JsExpression("function(e){select(this, 'countryGrades'); return false;}"));
        parent::init();
    }

    protected function prepareData(Carbon $date = null)
    {
        if(!isset($date)) {
            $date = new Carbon();
        }

        //$tempData will be of shape $tempData[country_iso_3]['value' => ..., 'date' => ...]
        $tempData = [];
        /** @var ResponseInterface $response */
        foreach($this->responses as $response) {
            $responseData = $response->getData();
            if($responseData['PRIMEID'] != '' && isset($responseData['GM02'])) {
                $responseDate = new Carbon($responseData['GM01']);
                if (!isset($tempData[$responseData['PRIMEID']]) && $responseDate->lte($date)) {
                    $tempData[$responseData['PRIMEID']] = ['date' => $responseDate, 'value' => $responseData['GM02']];
                } else {
                    if($responseDate->lte($date) && $responseDate->gt($tempData[$responseData['PRIMEID']]['date'])) {
                        $tempData[$responseData['PRIMEID']] = ['date' => $responseDate, 'value' => $responseData['GM02']];
                    }
                }
            }
        }

        $this->data = [];
        foreach($tempData as $id => $data) {
            $this->data[] = [
                'id' => $id,
                'value' => $data['value']
            ];
        }

        $this->addColorsToData();
    }

    public function renderLegend(View $view)
    {
        return "<table>" .
            "<tr><th style='padding: 5px; border-bottom: 1px solid black;'>" . \Yii::t('app', 'Country Grades') . "</th></tr>" .
            "<tr><td style='padding: 5px; font-weight: bold; background-color: " . $this->colorScale['A00'] . "'>" . \Yii::t('app', 'Preparedness') . "</td></tr>" .
            "<tr><td style='padding: 5px; font-weight: bold; background-color: " . $this->colorScale['A0'] . "'>" . \Yii::t('app', 'Ungraded') . "</td></tr>" .
            "<tr><td style='padding: 5px; font-weight: bold; background-color: " . $this->colorScale['A1'] . "'>" . \Yii::t('app', 'Grade 1') . "</td></tr>" .
            "<tr><td style='padding: 5px; font-weight: bold; color: white; background-color: " . $this->colorScale['A2'] . "'>" . \Yii::t('app', 'Grade 2') . "</td></tr>" .
            "<tr><td style='padding: 5px; font-weight: bold; color: white; background-color: " . $this->colorScale['A3'] . "'>" . \Yii::t('app', 'Grade 3') . "</td></tr>" .
        "</table>";
    }


    public function renderSummary(View $view, $id)
    {
        $country = Country::findOne($id);
        return $view->render('summaries/reports', [
            'country' => $country
        ], $this);
    }


}