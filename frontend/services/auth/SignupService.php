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
        // вместо валидации в форме (SignupForm.php):
        // ['username', 'unique', 'targetClass' => '\common\entities\User', 'message' => 'This username has already been taken.'],
        // используем собственную, с выбросом ошибки
        if (User::find()->where(['username' => $form->username])) {
            throw new \DomainException('Пользователь с таким именем уже существует!');
        }
        if (User::find()->where(['email' => $form->email])) {
            throw new \DomainException('Пользователь с такой почтой уже существует!');
        }

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