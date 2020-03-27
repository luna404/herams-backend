<?php
declare(strict_types=1);

namespace prime\actions;

use GuzzleHttp\Psr7\StreamWrapper;
use prime\helpers\CsvWriter;
use prime\helpers\XlsxWriter;
use prime\models\forms\Export;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\filters\ContentNegotiator;
use yii\helpers\FileHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\web\User;

class ExportAction extends Action
{
    /** @var \Closure */
    public $subject;
    /**
     * @var \Closure
     */
    public $checkAccess;
    /**
     * @var \Closure
     */
    public $responseQuery;
    /**
     * @var \Closure
     */
    public $surveyFinder;

    public $view = 'export';

    public function init()
    {
        parent::init();
        if (!$this->subject instanceof \Closure) {
            throw new InvalidConfigException('Subject must be a closure');
        }
        if (!$this->responseQuery instanceof \Closure) {
            throw new InvalidConfigException('Response iterator must be a closure');
        }
        if (!$this->surveyFinder instanceof \Closure) {
            throw new InvalidConfigException('Survey finder must be a closure');
        }
        if (!$this->checkAccess instanceof \Closure) {
            throw new InvalidConfigException('Checkaccess must be a closure');
        }
    }

    public function run(
        Request $request,
        Response $response,
        User $user
    ) {
        $this->controller->layout = 'form';
        $subject = ($this->subject)($request);
        if (!isset($subject)) {
            throw new NotFoundHttpException();
        } elseif (!($this->checkAccess)($subject, $user)) {
            throw new ForbiddenHttpException();
        }
        $survey = ($this->surveyFinder)($subject);

        $model = new Export($survey);
        if ($request->isPost && $model->load($request->bodyParams) && $model->validate()) {
            switch ($response->format) {
                case 'xlsx':
                    $writer = new XlsxWriter();
                    break;
                case 'csv':
                    $writer = new CsvWriter();
                    break;
                default:
                    die($response->format);
            }

            $model->run($writer, ($this->responseQuery)($subject));
            $stream = $writer->getStream();
            $extension = FileHelper::getExtensionsByMimeType($writer->getMimeType())[0] ?? 'unknown';
            return $response->sendStreamAsFile(StreamWrapper::getResource($stream), date('Ymd his') . ".$extension", [
                'mimeType' => $writer->getMimeType(),
                'fileSize' => $stream->getSize()
            ]);
        } else {
            return $this->controller->render($this->view, ['model' => $model, 'subject' => $subject]);
        }
    }
}