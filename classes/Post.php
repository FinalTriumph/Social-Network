<?php

class Post {
    
    public static function createPost($postbody, $loggedInUserId, $userid) {
        
            
        if (strlen($postbody) > 160 || strlen($postbody) < 1) {
            die('Incorrect length!');
        }
        
        $topics = self::getTopics($postbody);
            
        if ($loggedInUserId == $userid) {
                
            DB::query('INSERT INTO posts VALUES (\'\', :postbody, NOW(), :userid, 0, \'\', :topics)', array(':postbody'=>$postbody, ':userid'=>$userid, ':topics'=>$topics));
        } else {
            die('Incorrect user!');
        }
    }
    
    //////////////////////////////
    public static function createImgPost($postbody, $loggedInUserId, $userid) {
        
            
        if (strlen($postbody) > 160) {
            die('Incorrect length!');
        }
        
        $topics = self::getTopics($postbody);
            
        if ($loggedInUserId == $userid) {
                
            DB::query('INSERT INTO posts VALUES (\'\', :postbody, NOW(), :userid, 0, \'\', :topics)', array(':postbody'=>$postbody, ':userid'=>$userid, ':topics'=>$topics));
            $postid = DB::query('SELECT id FROM posts WHERE user_id=:userid ORDER BY id DESC LIMIT 1', array(':userid'=>$userid))[0]['id'];
            return $postid;
        
        } else {
            die('Incorrect user!');
        }
    }
    //////////////////////////////////
    
    public static function likePost($postid, $likerid) {
        if (!DB::query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postid, ':userid'=>$likerid))) {
            
            DB::query('UPDATE posts SET likes=likes+1 WHERE id=:postid', array(':postid'=>$postid));
            DB::query('INSERT INTO post_likes VALUES (\'\', :postid, :userid)', array(':postid'=>$postid, ':userid'=>$likerid));
        } else {
            
            DB::query('UPDATE posts SET likes=likes-1 WHERE id=:postid', array(':postid'=>$postid));
            DB::query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$postid, ':userid'=>$likerid));
        }
    }
    
    public static function getTopics($text) {
        
        $text = explode(" ", $text);
        $topics = "";
        
        foreach ($text as $word) {
            if (substr($word, 0, 1) == "#") {
                $topics .= substr($word, 1).",";
            }
        }
        return $topics;
    }
    
    public static function link_add($text) {
        
        $text = explode(" ", $text);
        $newstring = "";
        
        foreach ($text as $word) {
            if (substr($word, 0, 1) == "@") {
                $newstring .= "<a href='profile.php?username=".substr($word, 1)."'>".htmlspecialchars($word)." </a>";
            } else if (substr($word, 0, 1) == "#") {
                $newstring .= "<a href='topics.php?topic=".substr($word, 1)."'>".htmlspecialchars($word)." </a>";
            } else {
                $newstring .= htmlspecialchars($word)." ";
            }
        }
        return $newstring;
    }
    
    public static function displayPosts($userid, $username, $followerid) {
        $dbposts = DB::query('SELECT * FROM posts WHERE user_id=:userid ORDER BY id DESC', array(':userid'=>$userid));
        $posts = "";
        
        foreach($dbposts as $p) {
            
            if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$p['id'], 'userid'=>$followerid))) {
                
                $posts .= "<img src='".$p['postimg']."' style='max-height: 300px'><br />".self::link_add($p['body'])."
                <form action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                    <input type='submit' name='like' value='Like'>
                    <span>".$p['likes']." likes</span>
                </form>
                <hr /><br />";
            } else {
                $posts .= "<img src='".$p['postimg']."' style='max-height: 300px'><br />".self::link_add($p['body'])."
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