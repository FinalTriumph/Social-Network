<?php

include('./classes/DB.php');
include('./classes/Login.php');

if (Login::isLoggedIn()) {
    $userid = Login::isLoggedIn();
} else {
    die('Not logged in');
}

/*Little something wrong with displaying and opening messages, later it all will be different and fixed*/


if (isset($_GET['mid'])) {
    //$message = DB::query('SELECT * FROM messages WHERE id=:mid AND receiver=:receiver OR id=:mid AND sender=:sender', array(':mid'=>$_GET['mid'], ':receiver'=>$userid, ':sender'=>$userid))[0];
    $message = DB::query('SELECT * FROM messages WHERE id=:mid AND (receiver=:receiver OR sender=:sender)', array(':mid'=>$_GET['mid'], ':receiver'=>$userid, ':sender'=>$userid))[0];
    echo "<h1>View Message</h1>";
    echo htmlspecialchars($message['body']);
    echo '<hr />';
    
    if ($message['sender'] == $userid) {
        $id = $message['receiver'];
    } else {
        $id = $message['sender'];
    }
    
    DB::query('UPDATE messages SET open=1 WHERE id=:mid', array(':mid'=>$_GET['mid']));
    ?>
    
    
    <form action="send-message.php?receiver=<?php echo $id; ?>" method="post">
        <textarea name="body" rows="8" cols="80"></textarea>
        <input type="submit" name="send" value="Send Message">
    </form>
    
    <?php
} else {


?>

<h1>My Messages</h1>

<?php

$messages = DB::query('SELECT messages.*, users.username FROM messages, users WHERE messages.receiver=:receiver AND users.id=messages.sender OR sender=:sender AND users.id = messages.sender;', array(':receiver'=>$userid, ':sender'=>$userid));

foreach ($messages as $message) {
    
    if (strlen($message['body']) > 10) {
        $m = substr($message['body'], 0, 10)." ...";
    } else {
        $m = $message['body'];
    }
    
    
    if ($message['open'] == 0) {
        echo "<a href='my-messages.php?mid=".$message['id']."'><strong>".$m.'</strong></a> from '.$message['username'].'<hr />';
    } else {
        echo "<a href='my-messages.php?mid=".$message['id']."'>".$m.'</a> from '.$message['username'].'<hr />';
    }
}

}
/*///////*/
/*
SELECT messages.id, messages.body, s.username AS Sender, r.username AS Receiver
FROM messages
LEFT JOIN users s ON messages.sender = s.id
LEFT JOIN users r ON messages.receiver = r.id
WHERE r.username=:user OR s.username=:user
*/
?>