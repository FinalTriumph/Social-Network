<?php

require_once('PHPMailer/PHPMailerAutoload.php');
require_once("Mailpass.php");

class Mail {
    
    // 99 messages per day
    public static function sendMail($subject, $body, $address) {
        
        $password = Mailpass::password();
        
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = '465';
        $mail->isHTML();
        $mail->Username = 'finaltriumph.es@gmail.com';
        $mail->Password = $password;
        $mail->SetFrom('no-reply@socialnetwork.org');
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AddAddress($address);
        
        $mail->Send();
    }
    
}

?>