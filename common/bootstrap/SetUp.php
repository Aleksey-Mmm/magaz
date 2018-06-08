<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 03.06.2018
 * Time: 18:04
 */

namespace common\bootstrap;


//use frontend\services\auth\ContactService;
use frontend\services\auth\PasswordResetService;
use frontend\services\contact\ContactService;
use yii\base\BootstrapInterface;

class SetUp implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $container = \Yii::$container;

/*      вариант создания контейнера с помощью callback ф-ии
        $container->setSingleton(PasswordResetService::class, function () use ($app) {
            return new PasswordResetService([$app->params['supportEmail'] => $app->name. ' робот.']);
        });*/

        //более простой вариант с передачей параметров конструктору класса, для которого создаем контейнер
        //
        $container->setSingleton(PasswordResetService::class, [], [
            [$app->params['supportEmail'] => $app->name. ' робот.'],
            $app->mailer,
        ]);

        $container->setSingleton(ContactService::class, [], [
            $app->params['adminEmail'],
            $app->mailer,
        ]);


    }
}