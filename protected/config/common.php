<?php
include __DIR__ . '/../helpers/functions.php';
return [
    'id' => 'prime',
    'name' => 'Prime',
    'basePath' => realpath(__DIR__ . '/../'),
    'timeZone' => 'UTC',
    'sourceLanguage' => 'en',
    'aliases' => [
        '@prime' => '@app'
    ],
    'bootstrap' => ['log'],
    'components' => [
        'authClientCollection' => [
            'class' => \yii\authclient\Collection::class,
            'clients' => [
                'facebook' => [
                    'class' => \dektrium\user\clients\Facebook::class,
                    'viewOptions' => [
                        'widget' => [
                            'class' => \prime\widgets\SocialAuthItem::class,
                        ]
                    ],
                    'clientId' => '1646368368981068',
                    'clientSecret' => '616885b84a81d5abc203cfc7d462ea58'
                ],
                'google' => [
                    'class' => \dektrium\user\clients\Google::class,
                    'clientId' => '550362619218-7eng5d4jjs9esfo4ddggkdd2jl31nt3u.apps.googleusercontent.com',
                    'clientSecret' => 'Yo-fvZZ3b8D5VyzSI7VQ0TyF',
                    'viewOptions' => [
                        'widget' => [
                            'class' => \prime\widgets\SocialAuthItem::class,
                        ]
                    ],
                ],
                'linkedin' => [
                    'class' => \dektrium\user\clients\LinkedIn::class,
                    'clientId' => '77li9jqu82f1tx',
                    'clientSecret' => 'jxeT5c6EcSlf7d8w',
                    'viewOptions' => [
                        'widget' => [
                            'class' => \prime\widgets\SocialAuthItem::class,
                        ]
                    ],
                ]
            ]
        ],
        'authManager' => [
            'class' => \dektrium\rbac\components\DbManager::class
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class
        ],
        'limeSurvey' => function (){
            $json = new \SamIT\LimeSurvey\JsonRpc\JsonRpcClient(\prime\models\ar\Setting::get('limeSurvey.host'));
            return new \SamIT\LimeSurvey\JsonRpc\Client($json, \prime\models\ar\Setting::get('limeSurvey.username'), \prime\models\ar\Setting::get('limeSurvey.password'));
        },
        'log' => [
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => '@runtime/logs/error.log'
                ]
            ]
        ],
        'user' => [
            'class' => \yii\web\User::class,
            'identityClass' => \prime\models\ar\User::class
        ],
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => \yii\i18n\DbMessageSource::class
                ]
            ]
        ],
        'mailer' => [
            'class' => \yii\swiftmailer\Mailer::class,
            'transport' => [
                'class' => Swift_SmtpTransport::class,
                'constructArgs' => ['localhost', 25]
            ]
        ]
    ],
    'modules' => [
        'user' => [
            'class' => \dektrium\user\Module::class,
            'modelMap' => [
                'User' => \prime\models\ar\User::class,
                'Profile' => \prime\models\ar\Profile::class,
                'RegistrationForm' => \prime\models\forms\user\Registration::class,
                'RecoveryForm' => \prime\models\forms\user\Recovery::class,
                'SettingsForm' => \prime\models\forms\user\Settings::class
            ],
            'admins' => [
                'joey_claessen@hotmail.com',
                'sam@mousa.nl',
                'petragallos@who.int'
            ],
            'mailer' => [
                'sender' => 'prime_support@who.int', //[new \prime\objects\Deferred(function() {return \prime\models\ar\Setting::get('systemEmail', 'default-sender@befound.nl');})],
                'confirmationSubject' => new \prime\objects\Deferred(function() {return \Yii::t('user', '{0}: Your account has successfully been activated!', ['0' => app()->name]);}),
                'recoverySubject' => new \prime\objects\Deferred(function() {return \Yii::t('user', '{0}: Password reset', ['0' => app()->name]);}),
                'welcomeSubject' => new \prime\objects\Deferred(function() {return \Yii::t('user', 'Welcome to {0}, the Public Health Risks Information Marketplace!', ['0' => app()->name]);}),
            ]
        ],
        'rbac' => [
            'class' => dektrium\rbac\Module::class,
        ],
    ],
    'params' => [
        'defaultSettings' => [
            'icons.globalMonitor' => 'globe',
            'icons.projects' => 'tasks',
            'icons.reports' => 'file',
            'icons.userLists' => 'bullhorn',
            'icons.user' => 'user',
            'icons.configuration' => 'wrench',
            'icons.logIn' => 'log-in',
            'icons.logOut' => 'log-out',
            'icons.search' => 'search',
            'icons.read' => 'eye-open',
            'icons.update' => 'pencil',
            'icons.share' => 'share',
            'icons.close' => 'stop',
            'icons.open' => 'play',
            'icons.remove' => 'trash',
            'icons.limeSurveyUpdate' => 'cog'
        ]
    ]
];
