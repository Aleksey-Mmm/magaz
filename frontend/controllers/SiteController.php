<?php

namespace frontend\controllers;

use frontend\services\auth\PasswordResetService;
use frontend\services\auth\SignupService;
use Yii;
//use yii\base\InvalidParamException;
use yii\base\Exception;
use yii\base\Module;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\forms\LoginForm;
use frontend\forms\PasswordResetRequestForm;
use frontend\forms\ResetPasswordForm;
use frontend\forms\SignupForm;
use frontend\forms\ContactForm;

/**
 * Site controller
 */
class SiteController extends Controller
{

    private $passwordResetService;

    /**
     * SiteController constructor.
     * используем то, что в Yii2 контроллеры тоже создаются через контейнеры зависимости, и могут
     * подхватывать зависимые сервисы через параметры конструктора. В данном случае подключаем к этому
     * контроллеру сервис PasswordResetService. Настройки контейнера PasswordResetService находятся
     * в common/bootstrap/SetUp.php
     *
     * @param string $id
     * @param Module $module
     * @param PasswordResetService $passwordResetService
     * @param array $config
     */
    public function __construct(string $id, Module $module, PasswordResetService $passwordResetService, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function actionSignup()
    {
        $form = new SignupForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try { //отлавливаем ошибки которые сами же могли вызвать в SignupService.php
                $user = (new SignupService())->signup($form);
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            } catch (\DomainException $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }

        return $this->render('signup', [
            'model' => $form,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $form = new PasswordResetRequestForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
          //  if ($form->sendEmail()) {
            try {
                $this->passwordResetService->request($form);
                    Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                    return $this->goHome();

            } catch (Exception $e) {
                Yii::$app->session->setFlash('error', $e->getMessage());

            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $form,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function actionResetPassword($token)
    {
        //$service = new PasswordResetService();
        $service = $this->passwordResetService; //Yii::$container->get(PasswordResetService::class);

        try {
            $service->validateToken($token);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $form = new ResetPasswordForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            try {
                $service->reset($token, $form);
                Yii::$app->session->setFlash('success', 'New password saved.');
                return $this->goHome();

            } catch (\DomainException $e) {
                Yii::$app->errorHandler->logException($e);
                Yii::$app->session->setFlash('error', $e->getMessage());
            }
        }
        return $this->render('resetPassword', [
            'model' => $form,
        ]);
    }
}
