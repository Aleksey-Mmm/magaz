<?php
/**
 * User: malkov alexey
 * Date: 07.06.2018
 * Time: 11:15
 */

namespace frontend\services\auth;


use common\entities\User;
use frontend\forms\PasswordResetRequestForm;
use frontend\forms\ResetPasswordForm;
use InvalidArgumentException;

class PasswordResetService
{
    /**
     * обработка формы запроса сброса пароля.
     *
     * @param PasswordResetRequestForm $form
     * @throws \yii\base\Exception
     */
    public function request(PasswordResetRequestForm $form): void
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $form->email,
        ]);

        if (!$user) {
            //return false;
            throw new \DomainException('Пользователь с такой почтой не найден.');
        }

            $user->requestPasswordReset();

            if (!$user->save()) {
                //return false;
                throw new \RuntimeException('Ошибка сохранения токена пользователя.');
            }

       /* return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();*/

       $mess = \Yii::$app
           ->mailer
           ->compose(
               ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
               ['user' => $user]
           )
           ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name. ' робот.'])
           ->setTo($form->email)
           ->setSubject('Сброс пароля сайта '. \Yii::$app->name)
           ->send();

        if (!$mess) {
            throw new \RuntimeException('Ошибка отправки сообщения.');
        }

    }

    /**
     * проверка полученного от пользователя токена сброса пароля на валидность
     *
     * @param $token
     */
    public function validateToken($token)
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Password reset token cannot be blank.');
        }
        $user = User::findByPasswordResetToken($token);
        if (!$user) {
            throw new InvalidArgumentException('Wrong password reset token.');
        }
    }

    /**
     * @param string $token
     * @param ResetPasswordForm $form
     * @return void
     * @throws \yii\base\Exception
     */
    public function reset(string $token, ResetPasswordForm $form)
    {
        $user = User::findByPasswordResetToken($token);
        if (!$user) {
            throw new \DomainException('Пользователь с таким токеном не найден.');
        }

        $user->resetPassword($form->password);

        if (!$user->save(false)) {
            throw new \DomainException('Ошибка сохранения нового пароля.');
        }
    }
}