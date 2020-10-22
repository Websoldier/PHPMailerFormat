<?php

namespace Websolider;

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';

// defined('API_START') or die(header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403));
// define('EMAIL_DEBUG_ADDRESS', '');

$msg = 'Перезвоните %1$s <%3$s> по номеру телефона %2$s';
$fields = [
    new FormField('name', 'Имя', 'required|min:2|max:50'),
    new FormField('phone', 'Номер_телефона', 'required|min:4|max:50'),
    new FormField('email', 'Электронный_адрес', 'email|min:4|max:50'),
];

$mail = new PHPMailerFormat(new PHPMailer(), $fields);
$mail->setFrom('Nick');
$mail->parseValues($_GET);

if ($mail->validate()) {
    $mail->setLetter('Заявка с сайта', $msg);
    $mail->send('example@yandex.ru');
}

$mail->response();
die();
