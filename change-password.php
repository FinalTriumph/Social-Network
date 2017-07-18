<?php

include('./classes/DB.php');
include('./classes/Login.php');

$tokenIsValid = false;

if (Login::isLoggedIn()) {
    
    if (isset($_POST['changepassword'])) {
        
        $oldpassword = $_POST['oldpassword'];
        $newpassword = $_POST['newpassword'];
        $newpasswordrepeat = $_POST['newpasswordrepeat'];
        $user_id = Login::isLoggedIn();
        
        if (password_verify($oldpassword, DB::query('SELECT password FROM users WHERE id=:user_id', array(':user_id'=>$user_id))[0]['password'])) {
            
            if ($newpassword === $newpasswordrepeat) {
                
                if (strlen($newpassword) > 5 && strlen($newpassword) < 60) {
                    
                    DB::query('UPDATE users SET password=:newpassword WHERE id=:user_id', array(':newpassword'=>password_hash($newpassword, PASSWORD_BCRYPT), ':user_id'=>$user_id));
                    echo 'Password changed successfully';
                    
                } else {
                    echo 'Invalid new password';
                }
            } else {
                echo 'Passwords don\'t match';
            }
        } else {
            echo 'Incorrect old password';
        }
    }
} else {
    
    if (isset($_GET['token'])) {
    
    $token = $_GET['token'];
    
    if (DB::query('SELECT user_id FROM password_tokens WHERE token=:token', array(':token'=>sha1($token)))) {
    
        $user_id = DB::query('SELECT user_id FROM password_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
        
        $tokenIsValid = true;
        
        if (isset($_POST['changepassword'])) {
        
            $newpassword = $_POST['newpassword'];
            $newpasswordrepeat = $_POST['newpasswordrepeat'];
        
            if ($newpassword === $newpasswordrepeat) {
                
                if (strlen($newpassword) > 5 && strlen($newpassword) < 60) {
                    
                    DB::query('UPDATE users SET password=:newpassword WHERE id=:user_id', array(':newpassword'=>password_hash($newpassword, PASSWORD_BCRYPT), ':user_id'=>$user_id));
                    echo 'Password changed successfully';
                    DB::query('DELETE FROM password_tokens WHERE user_id=:user_id', array(':user_id'=>$user_id));
                    
                } else {
                    echo 'Invalid new password';
                }
            } else {
                echo 'Passwords don\'t match';
            }
        }
    } else {
        die('Token invalid');
    }
} else {
    die('Not logged in');
}
}

?>

<h1>Change your Password</h1>
<form action="<?php if (!$tokenIsValid) { echo 'change-password.php'; } else { echo 'change-password.php?token='.$token.''; } ?>" method="post">
    <?php if (!$tokenIsValid) { echo '<input type="password" name="oldpassword" value="" placeholder="Current Password ..."><p />'; } ?>
    <input type="password" name="newpassword" value="" placeholder="New Password ..."><p />
    <input type="password" name="newpasswordrepeat" value="" placeholder="Repeat New Password ..."><p />
    <input type="submit" name="changepassword" value="Change Password" ><p />
</form>
