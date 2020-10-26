<?php

use Websolider\FormField;
use Websolider\PHPMailerFormat;
use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';

// define('EMAIL_DEBUG_ADDRESS', '');

$fields = [
    new FormField('name', 'Имя', 'required|min:2|max:50'),
    new FormField('phone', 'Номер_телефона', 'required|min:4|max:50'),
    new FormField('email', 'Электронный_адрес', 'email|min:4|max:50'),
    new FormField('message', 'Сообщение', 'max:500'),
];

$msg = 'Перезвоните %1$s <%3$s> по номеру телефона %2$s';

$mail = new PHPMailerFormat(new PHPMailer(), $fields);
$mail->setFrom('Nick');
$mail->parseValues($_REQUEST);

if ($mail->validate()) {
    $mail->setLetter('Заявка с сайта', $msg);
    $mail->send('example@yandex.ru');
}

$mail->response();
die();
