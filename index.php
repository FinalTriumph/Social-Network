<?php

include('./classes/DB.php');
include('./classes/Login.php');

$showTimeline = false;

if (Login::isLoggedIn()) {
    //echo 'Logged In';
    //echo Login::isLoggedIn();
    $showTimeline = true;
} else {
    echo 'Not logged in';
}

// change follower_id
$followingposts = DB::query('SELECT posts.body, posts.likes, users.`username` FROM users, posts, followers 
WHERE posts.user_id = followers.user_id 
AND users.id = posts.user_id 
AND follower_id = 5
ORDER BY posts.likes DESC');

foreach($followingposts as $post) {
    echo $post['body']." ~ ".$post['username']."<hr />";
}

?>