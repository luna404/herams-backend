<?php

define('YII_DEBUG', file_exists(__DIR__ . '/config/debug'));
defined('CONSOLE') or define('CONSOLE', true);

defined('YII_ENV') or define('YII_ENV', require(__DIR__ . '/config/env.php'));
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL', file_exists(__DIR__ . '/config/debug') ? 3 : 0);

require_once __DIR__ . '/vendor/autoload.php';

// Define Yii class.
class Yii extends \app\components\Yii {}

spl_autoload_register(['Yii', 'autoload'], true, true);
Yii::$classMap = include(__DIR__ . '/vendor/yiisoft/yii2/classes.php');
Yii::$container = new yii\di\Container;

$config = require __DIR__ . '/config/console.php';

(new \app\components\ConsoleApplication($config))->run();