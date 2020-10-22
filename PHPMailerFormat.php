<?php

namespace Websolider;

use Illuminate\Validation;
use Illuminate\Filesystem;
use Illuminate\Translation;

/**
 * PHPMailer decorator class
 */
class PHPMailerFormat
{
    /**
     * @var array Response value.
     */
    private $status;

    /**
     * @var array<FormField> form values field data.
     */
    private $values;

    /**
     * @url https://laraveldaily.com/how-to-customize-error-messages-in-request-validation/
     * @var array Validate messages
     */
    private $messages = [
        'required' => 'Поле :attribute обязательно для заполнения.',
        'email' => 'Поле :attribute должно содержать электронный адрес.',
        'min' => [
            'string' => 'Поле :attribute не может содержать меньше :min символов',
        ],
        'max' => [
            'string' => 'Поле :attribute не может содержать больше :max символов',
        ],
    ];

    /**
     * @var PHPMailer $mail instance
     */
    private $mail;

    public function __construct($mail, $values = [])
    {
        $this->status = [
            'result' => 'OK',
            'errors' => [],
        ];

        $this->values = $values;

        $this->mail = $mail;
        $this->mail->CharSet = 'UTF-8';

        if (defined('EMAIL_DEBUG_ADDRESS') && EMAIL_DEBUG_ADDRESS) {
            $this->mail->addBCC(EMAIL_DEBUG_ADDRESS);
        }
    }

    public function setLetter($subject, $msg = '')
    {
        $values = array_column($this->values, 'value');

        $this->mail->Subject = $subject;
        $this->mail->Body = call_user_func_array('sprintf', array_merge((array) $msg, $values));
    }

    public function setFrom($fromName, $from = null)
    {
        if (!$from) {
            $from = 'no-reply@' . $_SERVER['SERVER_NAME'];
        }

        $this->mail->setFrom($from, $fromName);
        return $this;
    }

    public function addAddress($email, $name = null)
    {
        $this->mail->addAddress($email, !is_numeric($name) ? $name : null);
    }

    public function setTo($recipients)
    {
        $recipients = (array) $recipients;
        array_walk($recipients, [$this, 'addAddress']);
    }

    public function parseValues($data)
    {
        array_walk($this->values, function(&$field) use ($data) {
            $field->setValue(isset($data[ $field->key ]) ? $data[ $field->key ] : '');
        });
    }

    public function validate()
    {
        $filesystem = new Filesystem\Filesystem();
        $fileLoader = new Translation\FileLoader($filesystem, '');
        $translator = new Translation\Translator($fileLoader, 'ru_RU'); // en_US
        $factory = new Validation\Factory($translator);
        $validator = $factory->make(
            array_column($this->values, 'value', 'label'),
            array_column($this->values, 'rules', 'label'),
            $this->messages
        );

        if ($validator->fails()) {
            $keymap = array_column($this->values, 'key', 'label');
            foreach($validator->errors()->getMessages() as $key => $message) {
                $this->status['errors'][ $keymap[ $key ] ] = $message;
            }

            $this->status['result'] = 'NON_VALID';
            return false;
        }

        return true;
    }

    public function send($recipients)
    {
        $this->setTo($recipients);

        if (!empty($this->mail)) {
            try {
                $this->mail->send();
            } catch ( Exception $e ) {
                $this->status['result'] = 'MAILER_ERROR';
                // $this->status['errors'] = ["Message could not be sent. Mailer Error: {$mail->ErrorInfo}"];
            }
        }

        return $this;
    }

    public function response()
    {
        header('Content-Type: application/json');
        echo json_encode($this->status);
    }
}
