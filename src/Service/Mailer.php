<?php


use PHPMailer\PHPMailer\PHPMailer;

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Mailer extends AbstractController
{
    private $mailer;


    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
    }

    /*************************************************************** */
    public function sendMail($destinataire, $subject, $messages, $altBody, $redirect)
    {
        //------smtp settings
        $this->mailer->isSMTP();
        $this->mailer->Host = "127.0.0.1";
        $this->mailer->Port = 1025;
        $this->mailer->SMTPAutoTLS = false;
        //------email settings
        $this->mailer->isHTML(true);
        $this->mailer->SetFrom("mohamed.amiar@gmail.com", "amiar");
        $this->mailer->AddAddress($destinataire);
        $this->mailer->Subject  = $subject;
        $this->mailer->Body     = $messages;
        $this->mailer->AltBody = $altBody;

        if ($this->mailer->Send()) {
            echo 'Message was not sent.';
            echo 'Mailer error: ' . $this->mailer->ErrorInfo;
        } else {
            echo 'Message has been sent.';
            $this->redirectToRoute('app_user_login');
        }
    }
}
