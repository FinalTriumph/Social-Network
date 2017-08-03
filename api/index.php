<?php 

require_once("DB.php");
require_once("Mail.php");


$db = new DB('127.0.0.1', 'SocialNetwork', 'finaltriumph', '');

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    
    if ($_GET['url'] == "musers") {
        
        $token = $_COOKIE['SNID'];
        $userid = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
        
        $users = $db->query('SELECT s.username AS sender, r.username AS receiver, s.id AS senderid, r.id AS receiverid
        FROM messages
        LEFT JOIN users s ON s.id = messages.sender
        LEFT JOIN users r ON r.id = messages.receiver
        WHERE (s.id =:userid OR r.id=:userid)', array(':userid'=>$userid));
        
        $u = array();
        foreach($users as $user) {
            if(!in_array(array('username'=>$user['receiver'], 'id'=>$user['receiverid']), $u)) {
                array_push($u, array('username'=>$user['receiver'], 'id'=>$user['receiverid']));
            }
            if(!in_array(array('username'=>$user['sender'], 'id'=>$user['senderid']), $u)) {
                array_push($u, array('username'=>$user['sender'], 'id'=>$user['senderid']));
            }
        }
        echo json_encode($u);
        
    } else if ($_GET['url'] == "auth") {
        
    } else if ($_GET['url'] == "messages") {
        $sender = $_GET['sender'];
        
        $token = $_COOKIE['SNID'];
        $receiver = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
        
        $messages = $db->query('SELECT messages.id, messages.body, 
            s.username AS sender, r.username AS receiver
            FROM messages
            LEFT JOIN users s ON messages.sender = s.id
            LEFT JOIN users r ON messages.receiver = r.id
            WHERE (r.id=:r AND s.id=:s) OR (r.id=:s AND s.id=:r)', array(':r'=>$receiver, ':s'=>$sender));
        
        echo json_encode($messages);
    
        
    } else if ($_GET['url'] == "search") {
        
        $tosearch = explode(" ", $_GET['query']);
    
        if (count($tosearch) == 1) {
            $tosearch = str_split($tosearch[0], 2);
        }
        
        /*
        $whereclause = "";
        $paramsarray = array(':username'=>'%'.$_POST['searchbox'].'%');
        for ($i = 0; $i < count($tosearch); $i++) {
            $whereclause .= " OR username LIKE :u$i";
            $paramsarray[":u$i"] = $tosearch[$i];
        }
        
        $users = DB::query('SELECT users.username FROM users WHERE users.username LIKE :username '.$whereclause.'', $paramsarray);
        print_r($users);*/
        
        $whereclause = "";
        $paramsarray = array(':body'=>'%'.$_GET['query'].'%');
        for ($i = 0; $i < count($tosearch); $i++) {
            if ($i % 2) {
                $whereclause .= " OR body LIKE :p$i";
                $paramsarray[":p$i"] = $tosearch[$i];
            }
        }
        
        $posts = $db->query('SELECT posts.id, posts.body, posts.posted_at, users.username FROM posts, users WHERE users.id = posts.user_id AND posts.body LIKE :body '.$whereclause.' LIMIT 10', $paramsarray);
        
        echo json_encode($posts);
        
        
    } else if ($_GET['url'] == "users") {
        
        $token = $_COOKIE['SNID'];
        $user_id = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
        $username = $db->query('SELECT username FROM users WHERE id=:id', array(':id'=>$user_id))[0]['username'];
        echo $username;
        
    } else if ($_GET['url'] == "comments" && isset($_GET['postid'])) {
        $output = "";
        $comments = $db->query('SELECT comments.comment, comments.posted_at, users.username FROM comments, users WHERE post_id=:postid AND comments.user_id=users.id', array(':postid'=>$_GET['postid']));
        $output .= "[";
        foreach($comments as $comment) {
            $output .= "{";
            $output .= '"Comment": "'.$comment['comment'].'",';
            $output .= '"CommentedAt": "'.$comment['posted_at'].'",';
            $output .= '"CommentedBy": "'.$comment['username'].'"';
            $output .= "},";
            //echo $comment['comment']." ~ ".$comment['username']."<hr />";
        }
        $output = substr($output, 0, strlen($output)-1);
        if (strlen($output) !== 0) {
            $output .= "]";
        } else {
            $output = "No comments";
        }
        
        echo $output;
        
    } else if ($_GET['url'] == "posts") {
        $start = (int)$_GET['start'];
        
        $token = $_COOKIE['SNID'];
        
        $userid = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
        
        $followingposts = $db->query('SELECT posts.id, posts.body, posts.posted_at, posts.postimg, posts.likes, users.`username` FROM users, posts, followers 
        WHERE posts.user_id = followers.user_id 
        AND users.id = posts.user_id 
        AND follower_id = :userid
        ORDER BY posts.posted_at DESC 
        LIMIT 5 
        OFFSET '.$start.'', array(':userid'=>$userid));
        
        $response = "[";
        
        foreach($followingposts as $post) {
            
            $response .= "{";
                $response .= '"PostId": "'.$post['id'].'",';
                $response .= '"PostBody": "'.$post['body'].'",';
                $response .= '"PostedBy": "'.$post['username'].'",';
                $response .= '"PostDate": "'.$post['posted_at'].'",';
                $response .= '"PostImage": "'.$post['postimg'].'",';
                $response .= '"Likes": "'.$post['likes'].'"';
            $response .= "},";
             
        }
        //$response = substr($response, 0, strlen($response)-1);
        //$response .= "]";
        
        $response = substr($response, 0, strlen($response)-1);
        if (strlen($response) !== 0) {
            $response .= "]";
        } else {
            $response = "No posts";
        }
        
        echo $response;
        
    } else if ($_GET['url'] == "profileposts") {
        $start = (int)$_GET['start'];
        $userid = $db->query('SELECT id FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['id'];
        
        $followingposts = $db->query('SELECT posts.id, posts.body, posts.posted_at, posts.postimg, posts.likes, users.`username` FROM users, posts
        WHERE users.id = posts.user_id
        AND users.id = :userid
        ORDER BY posts.posted_at DESC 
        LIMIT 5 
        OFFSET '.$start.'', array(':userid'=>$userid));
        
        $response = "[";
        
        foreach($followingposts as $post) {
            
            $response .= "{";
                $response .= '"PostId": "'.$post['id'].'",';
                $response .= '"PostBody": "'.$post['body'].'",';
                $response .= '"PostedBy": "'.$post['username'].'",';
                $response .= '"PostDate": "'.$post['posted_at'].'",';
                $response .= '"PostImage": "'.$post['postimg'].'",';
                $response .= '"Likes": "'.$post['likes'].'"';
            $response .= "},";
             
        }
        //$response = substr($response, 0, strlen($response)-1);
        //$response .= "]";
        
        $response = substr($response, 0, strlen($response)-1);
        if (strlen($response) !== 0) {
            $response .= "]";
        } else {
            $response = "No posts";
        }
        
        echo $response;
    }
    
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    
    if ($_GET['url'] == "message") {
        
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);
        
        $body = $postBody->body;
        $receiver = $postBody->receiver;
        
        $token = $_COOKIE['SNID'];
        
        $userid = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
        
        if (strlen($body) > 100) {
            echo "{ 'Error': 'Message too long!'}";
        } else {
            $db->query('INSERT INTO messages VALUES(\'\', :body, :sender, :receiver, 0)', array(':body'=>$body, ':sender'=>$userid, ':receiver'=>$receiver));
            
            echo '{ "Success": "Message Sent" }';
        }
        
    } else if ($_GET['url'] == "users") {
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);
        
        $username = $postBody->username;
        $email = $postBody->email;
        $password = $postBody->password;
        
        if (!$db->query('SELECT username FROM users WHERE username = :username', array(':username'=>$username))) {
        
            if (strlen($username) >= 3 && strlen($username) <= 32) {
                
                if (preg_match('/[a-zA-Z0-9_]+/', $username)) {
                    
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        
                        if (!$db->query('SELECT email FROM users WHERE email=:email', array(':email'=>$email))) {
                            
                            if (strlen($password) > 5 && strlen($password) < 60) {
                        
                                $db->query('INSERT INTO users VALUES (\'\', :username, :password, :email, \'0\', \'\')', array(':username'=>$username, ':password'=>password_hash($password, PASSWORD_BCRYPT), ':email'=>$email));
                                Mail::sendMail('Welcome to our Social Network!', 'Your account has been created!', $email);
                                echo '{ "Success": "User Created!" }';
                                http_response_code(200);
                            
                            } else {
                                echo '{ "Error": "Invalid password" }';
                                http_response_code(409);
                            }
                        } else {
                            echo '{ "Error": "Email already exists" }';
                            http_response_code(409);
                        }
                    } else {
                        echo '{ "Error": "Invalid email" }';
                        http_response_code(409);
                    }
                } else {
                    echo '{ "Error": "Invalid username" }';
                    http_response_code(409);
                }
            } else {
                echo '{ "Error": "Invalid username" }';
                http_response_code(409);
            }
        } else {
            echo '{ "Error": "Username already exists" }';
            http_response_code(409);
        }
        
        
        
        
    }
    
    if ($_GET['url'] == "auth") {
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);
        
        $username = $postBody->username;
        $password = $postBody->password;
        
        if ($db->query('SELECT username FROM users WHERE username=:username', array(':username'=>$username))) {
            if (password_verify($password, $db->query('SELECT password FROM users WHERE username=:username', array(':username'=>$username))[0]['password'])) {
                
                $cstrong = true;
                $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                $user_id = $db->query('SELECT id FROM users WHERE username=:username', array(':username'=>$username))[0]['id'];
                $db->query('INSERT INTO login_tokens VALUES (\'\', :token, :user_id)', array(':token'=>sha1($token), ':user_id'=>$user_id));
                echo '{ "Token": "'.$token.'" }';
            } else {
                echo '{ "Error": "Invalid Password" }';
                http_response_code(401);
            }
        } else {
            echo '{ "Error": "Invalid username" }';
            http_response_code(401);
        }
    }
    
    if ($_GET['url'] == "likes") {
        
        $postid = $_GET['id'];
        $token = $_COOKIE['SNID'];
        $likerid = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token'=>sha1($token)))[0]['user_id'];
        
        if (!$db->query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postid, ':userid'=>$likerid))) {
            
            $db->query('UPDATE posts SET likes=likes+1 WHERE id=:postid', array(':postid'=>$postid));
            $db->query('INSERT INTO post_likes VALUES (\'\', :postid, :userid)', array(':postid'=>$postid, ':userid'=>$likerid));
            //Notify::createNotify("", $postid);
        } else {
            
            $db->query('UPDATE posts SET likes=likes-1 WHERE id=:postid', array(':postid'=>$postid));
            $db->query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postid, ':userid'=>$likerid));
        }
        
        echo "{";
        echo '"Likes":';
        echo $db->query('SELECT likes FROM posts WHERE id=:postid', array(':postid'=>$postid))[0]['likes'];
        echo "}";
        
    }

} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
    
    if ($_GET['url'] == "auth") {
        if (isset($_GET['token'])) {
            if ($db->query('SELECT token FROM login_tokens WHERE token=:token', array(':token'=>sha1($_GET['token'])))) {
                $db->query('DELETE FROM login_tokens WHERE token=:token', array(':token'=>sha1($_GET['token'])));
                echo '{ "Status": "Success" }';
                http_response_code(200);
            } else {
                echo '{ "Error": "Invalid token" }';
                http_response_code(400);
            }
        } else {
            echo '{ "Error": "Malformed request" }';
            http_response_code(400);
        }
    }
    
} else {
    http_response_code(405);
}

?>