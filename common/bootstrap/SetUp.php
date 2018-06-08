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
use yii\di\Instance;
use yii\mail\MailerInterface;

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
            //$app->mailer,
            Instance::of(MailerInterface::class),
        ]);

        $container->setSingleton(ContactService::class, [], [
            $app->params['adminEmail'],
            //$app->mailer,
            //при таком способе вызова (майлера в данном случае) объект создастся сразу, при инициализации класса
            // а если он нам не понадобится??
            //можно рядом зарегистрировать в контейнере вызов того же майлера с помощью анонимной функции,
            //а здесь использовать след. конструкцию (Instance::of)
            //тогда объект будет создан только в том случае, если кто то дёрнет этот контейнер.
            //ПРИЧЕМ! т.к. мы отдельно уже зарегистрировали контейнер под MailerInterface, то
            //вызов Instance::of можно не делать, т.к. Yii при вызове этого контейнера если не найдет
            //параметр с майлером, то будет его искать здесь, в этом методе. И найдет, так как мы
            //MailerInterface зарегистрировали как самостоятельный контейнер (urok2 03:10)
            Instance::of(MailerInterface::class),
        ]);

        //зарегистрировали в отдельном контейнере, а не внутри других контейнеров, для того,
        //чтобы можно было его использовать в нескольких контейнрах.
        $container->setSingleton(MailerInterface::class, function () use ($app) {
            return $app->mailer;
        });
    }
}