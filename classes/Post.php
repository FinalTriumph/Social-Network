<?php

class Post {
    
    public static function createPost($postbody, $loggedInUserId, $userid) {
        
            
        if (strlen($postbody) > 160 || strlen($postbody) < 1) {
            die('Incorrect length!');
        }
            
        if ($loggedInUserId == $userid) {
                
            DB::query('INSERT INTO posts VALUES (\'\', :postbody, NOW(), :userid, 0)', array(':postbody'=>$postbody, ':userid'=>$userid));
        } else {
            die('Incorrect user!');
        }
    }
    
    public static function likePost($postid, $likerid) {
        if (!DB::query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postid, ':userid'=>$likerid))) {
            
            DB::query('UPDATE posts SET likes=likes+1 WHERE id=:postid', array(':postid'=>$postid));
            DB::query('INSERT INTO post_likes VALUES (\'\', :postid, :userid)', array(':postid'=>$postid, ':userid'=>$likerid));
        } else {
            
            DB::query('UPDATE posts SET likes=likes-1 WHERE id=:postid', array(':postid'=>$postid));
            DB::query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postid, ':userid'=>$likerid));
        }
    }
    
    public static function displayPosts($userid, $username, $followerid) {
        $dbposts = DB::query('SELECT * FROM posts WHERE user_id=:userid ORDER BY id DESC', array(':userid'=>$userid));
        $posts = "";
        
        foreach($dbposts as $p) {
            
            if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$p['id'], 'userid'=>$followerid))) {
                
                $posts .= htmlspecialchars($p['body'])."
                <form action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                    <input type='submit' name='like' value='Like'>
                    <span>".$p['likes']." likes</span>
                </form>
                <hr /><br />";
            } else {
                $posts .= htmlspecialchars($p['body'])."
                <form action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                    <input type='submit' name='unlike' value='Unlike'>
                    <span>".$p['likes']." likes</span>
                </form>
                <hr /><br />";
            }
        }
        return $posts;
    }
    
    
}

?>