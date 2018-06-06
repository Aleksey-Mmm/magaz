<?php
/**
 * User: malkov alexey
 * Date: 06.06.2018
 * Time: 11:14
 */

namespace common\tests\unit\entities\User;


use Codeception\Test\Unit;
use common\entities\User;

class SignapTest extends Unit
{
    /**
     * @throws \yii\base\Exception
     */
    public function testSuccess()
    {
        $user = User::signup(
            $username = 'username',
            $email = 'email@shop.ru',
            $password = 'user_password'
        );

        $this->assertEquals($username, $user->username);
        $this->assertEquals($email, $user->email);
        $this->assertNotEmpty($user->password_hash);
        $this->assertNotEquals($password, $user->password_hash);
        $this->assertNotEmpty($user->created_at);
        $this->assertNotEmpty($user->auth_key);
        //$this->assertEquals($user::STATUS_ACTIVE, $user->status);
        $this->assertTrue($user->isActive());
    }
}