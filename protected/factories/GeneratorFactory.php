<?php

namespace prime\factories;

use app\components\InlineView;
use yii\helpers\ArrayHelper;
use yii\web\View;


/**
 * Class GeneratorFactory
 * @package prime\factories
 */
class GeneratorFactory
{
    /**
     * Returns a list of all known generator classes.
     *
     * @return array
     */
    public static function classes()
    {
        return [
            'ccpm' => \prime\reportGenerators\ccpm\Generator::class,
            'cd' => \prime\reportGenerators\cd\Generator::class,
            'cdProgress' => \prime\reportGenerators\cdProgress\Generator::class,
            'ccpmPercentage' => \prime\reportGenerators\ccpmProgressPercentage\Generator::class,
            'oscar' => \prime\reportGenerators\oscar\Generator::class,
            'oscarProgress' => \prime\reportGenerators\oscarProgress\Generator::class,
            'hc' => \prime\reportGenerators\healthClusterDashboard\Generator::class,
            'empty' => \prime\reportGenerators\emptyReport\Generator::class,
            'progress' => \prime\reportGenerators\progress\Generator::class
        ];

    }

    /**
     * Returns a map of generator name => title
     * @return array
     */
    public static function options() {
        return array_map(function($className) {
            return $className::title();
        }, GeneratorFactory::classes());
    }

    public static function get($name, View $view = null)
    {
        $before = ArrayHelper::getValue(\Yii::$container->getDefinitions(), View::class);
        if (!isset($view)) {
            \Yii::$container->set(View::class, InlineView::class);
        } else {
            \Yii::$container->setSingleton(View::class, $view);
        }

        $result = \Yii::$container->get(ArrayHelper::getValue(self::classes(), $name, $name));

        /**
         * Restore previous definition.
         */
        \Yii::$container->set(View::class, $before);

        return $result;
    }
}