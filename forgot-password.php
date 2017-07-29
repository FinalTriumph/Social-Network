<?php

include('./classes/DB.php');
include('./classes/Mail.php');

if (isset($_POST['resetpassword'])) {
    // send mail function will be added later
    $cstrong = true;
    $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
    $email = $_POST['email'];
    $user_id = DB::query('SELECT id FROM users WHERE email=:email', array(':email'=>$email))[0]['id'];
    
    DB::query('INSERT INTO password_tokens VALUES (\'\', :token, :user_id)', array(':token'=>sha1($token), ':user_id'=>$user_id));
    Mail::sendMail('Forgot Password!', "Reset your password!<br /><a href='https://social-network-example-finaltriumph.c9users.io/change-password.php?token=$token'>https://social-network-example-finaltriumph.c9users.io/change-password.php?token=$token</a>", $email);
    echo 'Email sent!';
}

?>

<h1>Forgot Password</h1>
<form action="forgot-password.php" method="post">
    <input type="email" name="email" value="" placeholder="Email ..."><p />
    <input type="submit" name="resetpassword" value="Reset Password">
</form>