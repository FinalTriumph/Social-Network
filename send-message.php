<?php

session_start();
$cstrong = true;
$token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = $token;
}


include('./classes/DB.php');
include('./classes/Login.php');

if (Login::isLoggedIn()) {
    $userid = Login::isLoggedIn();
} else {
    die('Not logged in');
}

if (isset($_POST['send'])) {
    
    if (!isset($_POST['nocsrf'])) {
        die("Invalid token");
    }
    
    if ($_POST['nocsrf'] !== $_SESSION['token']) {
        die("Invalid token");
    }
    
    if (DB::query('SELECT id FROM users WHERE id=:receiver', array(':receiver'=>htmlspecialchars($_GET['receiver'])))) {
        
        DB::query('INSERT INTO messages VALUES(\'\', :body, :sender, :receiver, 0)', array(':body'=>$_POST['body'], ':sender'=>$userid, ':receiver'=>htmlspecialchars($_GET['receiver'])));
        echo "Message Sent!";
    } else {
        die("Note existing receiver!");
    }
    session_destroy();
}


?>

<h1>Send a Message</h1>
<form action="send-message.php?receiver=<?php echo htmlspecialchars($_GET['receiver']); ?>" method="post">
    <textarea name="body" rows="8" cols="80"></textarea>
    <input type="hidden" name="nocsrf" value="<?php echo $_SESSION['token']; ?>">
    <input type="submit" name="send" value="Send Message">
</form>