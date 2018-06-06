<?php
/**
 * User: malkov alexey
 * Date: 06.06.2018
 * Time: 14:22
 */

namespace frontend\services\auth;


use common\entities\User;
use frontend\forms\SignupForm;

class SignupService
{
    /**
     * сервисный слой.
     * этот класс создан, чтобы убрать из SignupForm.php метод signup и остаить там только саму форму
     * (поля и rules). короче разделяем слои формы и модели. Этму методу место в сервисном слое.
     *
     * @param SignupForm $form
     * @return User
     * @throws \yii\base\Exception
     */
    public function signup(SignupForm $form): User
    {
        $user = User::signup(
            $form->username,
            $form->email,
            $form->password
        );

        if (!$user->save()) {
            throw new \RuntimeException('Saving error!');
        }

        return $user;
    }
}