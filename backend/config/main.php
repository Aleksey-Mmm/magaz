<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'backendUrlManager' => require __DIR__ .'/urlManager.php',  // настройки урлМанагера вынесли в отдельный файл
        'frontendUrlManager' => require __DIR__ .'/../../frontend/config/urlManager.php',  //чтобы иметь доступ к фронтэнд урлМанагеру
        'urlManager' => function () {  //получили тот же backendUrlManager
            return Yii::$app->get('backendUrlManager');
        }
    ],
            //глобально для бэкэнда прикрепили поведение: "доступ только для зарегистрированных"
    'as access' => [
        'class' => 'yii\filters\AccessControl',
        'except' => ['site/login', 'site/error'],   //кроме этих 2-х маршрутов
        'rules' => [
            [
            'allow' => true,
            'roles' => ['@'],
            ]
        ],
    ],
    'params' => $params,
];
