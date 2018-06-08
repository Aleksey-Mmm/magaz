<?php
/**
 * User: malkov alexey
 * Date: 08.06.2018
 * Time: 12:57
 */

namespace frontend\services\contact;


use frontend\forms\ContactForm;
use yii\mail\MailerInterface;

class ContactService
{
    private $adminEmail;
    private $mailer;

    /**
     * ContactService constructor.
     *
     * @param $adminEmail
     * @param MailerInterface $mailer
     */
    public function __construct($adminEmail, MailerInterface $mailer)
    {
        $this->adminEmail = $adminEmail;
        $this->mailer = $mailer;
    }

    /**
     * отправка из формы контактов
     *
     * @param ContactForm $form
     */
    public function sendEmail(ContactForm $form): void
    {
        $send = $this->mailer->compose()
            ->setTo($this->adminEmail)
            ->setFrom([$form->email => $form->name])
            ->setSubject($form->subject)
            ->setTextBody($form->body)
            ->send();

        if (!$send) {
            throw new \DomainException('Ошибка отправки контактной формы.');
        }

    }
}