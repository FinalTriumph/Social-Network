<?php

include('./classes/DB.php');
include('./classes/Login.php');
include('./classes/Post.php');
include('./classes/Image.php');
include('./classes/Notify.php');

$username = '';
$verified = false;
$isFollowing = false;

if (isset($_GET['username'])) {
    if (DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$_GET['username']))) {
        
        $username = DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['username'];
        $userid = DB::query('SELECT id FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['id'];
        $verified = DB::query('SELECT verified FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['verified'];
        $followerid = Login::isLoggedIn();
        
        // Follow
        if (isset($_POST['follow'])) {
            if ($userid !== $followerid) {
                
                if (!DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid))) {
                    
                    if ($followerid == 5) {
                        DB::query('UPDATE users SET verified=1 WHERE id=:userid', array(':userid'=>$userid));
                    }
                    
                    DB::query('INSERT INTO followers VALUES (\'\', :userid, :followerid)', array(':userid'=>$userid, ':followerid'=>$followerid));
                    
                } else {
                    echo 'Already following';
                }
                $isFollowing = true;
            }
        }
        ///////
        
        // Unfollow
        if (isset($_POST['unfollow'])) {
            if ($userid !== $followerid) {
            
                if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid))) {
                    
                    if ($followerid == 5) {
                        DB::query('UPDATE users SET verified=0 WHERE id=:userid', array(':userid'=>$userid));
                    }
                    
                    DB::query('DELETE FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid));
                }
                $isFollowing = false;
            }
        }
        ///////
        
        // Check if following
        if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid))) {
            //echo 'Already following';
            $isFollowing = true;
        }
        ///////
        
        if (isset($_POST['deletepost'])) {
            if (DB::query('SELECT id FROM posts WHERE id=:postid AND user_id=:userid', array(':postid'=>$_GET['postid'], ':userid'=>$followerid))) {
                
                DB::query('DELETE FROM posts WHERE id=:postid AND user_id=:userid', array(':postid'=>$_GET['postid'], ':userid'=>$followerid));
                DB::query('DELETE FROM post_likes WHERE post_id=:postid', array(':postid'=>$_GET['postid']));
                DB::query('DELETE FROM comments WHERE post_id=:postid', array(':postid'=>$_GET['postid']));
                echo 'Post Deleted!';
            }
        }
        
        if (isset($_POST['post'])) {
            
            if ($_FILES['postimg']['size'] == 0) {
                Post::createPost($_POST['postbody'], Login::isLoggedIn(), $userid);
            } else {
                
                $postid = Post::createImgPost($_POST['postbody'], Login::isLoggedIn(), $userid);
                Image::uploadImage('postimg', 'UPDATE posts SET postimg=:postimg WHERE id=:postid', array(':postid'=>$postid));
            }
        }
        
        if (isset($_GET['postid']) && !isset($_POST['deletepost'])) {
            Post::likePost($_GET['postid'], $followerid);
            
        }
        
        $posts = Post::displayPosts($userid, $username, $followerid);
        
    } else {
        die('User not found');
    }
}

?>
<!--
<h1><?php echo $username; ?>'s Profile<?php if ($verified) { echo ' - Verified'; } ?></h1>
<form action="profile.php?username=<?php echo $username; ?>" method="post">
    <?php 
    if ($userid !== $followerid) {
        if ($isFollowing) {
            echo '<input type="submit" name="unfollow" value="Unfollow">';
        } else {
            echo '<input type="submit" name="follow" value="Follow">';
        }
    }
    ?>
</form>

<form action="profile.php?username=<?php echo $username; ?>" method="post" enctype="multipart/form-data">
    <textarea name="postbody" rows="8" cols="80"></textarea>
    <br />Upload an image:
    <input type="file" name="postimg">
    <input type="submit" name="post" value="Post">
</form>

<div class="posts">
    <?php echo $posts; ?>
</div>
-->



<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Profile</title>
  <link href="https://fonts.googleapis.com/css?family=Catamaran" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="public/css/style.css">
</head>

<body>
    
    <!--<div id="heading">
        <form action="index.php" method="post">
            <input type="text" name="searchbox" value="">
            <input type="button" name="search" value="Search">
        </form>
    </div>-->
    
    <h1 id="h1_username"><?php echo $username; ?>'s Profile<?php if ($verified) { echo ' - Verified'; } ?> </h1>
    
    <?php 
    if ($userid !== $followerid) {
        echo '<form id="foll_unfoll_form" action="profile.php?username='.$username.'" method="post">';
        if ($isFollowing) {
            echo '<input type="submit" name="unfollow" id="foll_unfoll_btn" value="Unfollow">';
        } else {
            echo '<input type="submit" name="follow" id="foll_unfoll_btn" value="Follow">';
        }
        echo '</form>';
    } else { 
        echo '<button id="new_post_btn">NEW POST</button>';
    
    
        echo '<form id="new_post_form" action="profile.php?username=<?php echo $username; ?>" method="post" enctype="multipart/form-data">';
            echo '<input type="button" name="close" id="close_form" value="x"><br /><br />';
            echo '<textarea name="postbody" rows="8" cols="80"></textarea>';
            echo '<br />Upload an image:';
            echo '<input type="file" name="postimg" class="post_btn">';
            echo '<input type="submit" name="post" class="post_btn" value="Post">';
        echo '</form>';
        
    }
    ?>
    
    <div id="timeline"></div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
    <script type="text/javascript">
    /* global $ */
    
        function scrollToAnchor(aid) {
            var aTag = $(aid);
            $('html, body').animate({scrollTop: aTag.offset().top}, 'slow');
        }
        
        
        $(document).ready(function() {
            
            $.ajax({
                type: "GET",
                url: 'api/profileposts?username=<?php echo $username; ?>',
                processData: false,
                contentType: 'application/json',
                data: '',
                success: function(res) {
                    var posts = JSON.parse(res);
                    $.each(posts, function(index) {
                        
                        
                        $("#timeline").append(
                            '<div class="post" id="'+posts[index].PostId+'"><p>'+posts[index].PostBody+'</p><img src="'+posts[index].PostImage+'" /><div class="post_footer"> - Posted by '+posts[index].PostedBy+' on '+posts[index].PostDate+'<button data-id="'+posts[index].PostId+'">'+posts[index].Likes+' Likes</button><button data-postid="'+posts[index].PostId+'">Comments</button></div><div class="comments" data-commentsdiv="'+posts[index].PostId+'"><hr /></div></div>'
                            );
                        
                    });
                    
                    $('[data-id]').click(function() {
                        var buttonid = $(this).attr('data-id');
                        $.ajax({
                            type: "POST",
                            url: 'api/likes?id=' + $(this).attr('data-id'),
                            processData: false,
                            contentType: 'application/json',
                            data: '',
                            success: function(res) {
                                var likes = JSON.parse(res).Likes;
                                $('[data-id="'+buttonid+'"]').html(likes + ' Likes');
                            },
                            error: function() {
                                console.log(JSON.parse(res.responseText));
                            }
                        });
                    });
                    
                    $('[data-postid]').click(function() { 
                        var buttonid = $(this).attr('data-postid');
                        if ($('[data-commentsdiv="'+buttonid+'"]').css('display') == 'block') {
                            $('[data-commentsdiv="'+buttonid+'"]').css('display', 'none');
                            $('[data-commentsdiv="'+buttonid+'"]').html('<hr />');
                        } else {
                            $('[data-commentsdiv="'+buttonid+'"]').css('display', 'block');
                            $('[data-commentsdiv="'+buttonid+'"]').html('<hr /><p>Loading comments...</p><hr />');
                            $.ajax({
                                type: "GET",
                                url: 'api/comments?postid=' + $(this).attr('data-postid'),
                                processData: false,
                                contentType: 'application/json',
                                data: '',
                                success: function(res) {
                                    $('[data-commentsdiv="'+buttonid+'"]').html('<hr />');
                                    if (res == "No comments") {
                                        $('[data-commentsdiv="'+buttonid+'"]').append('<p>No comments to show</p><hr />');
                                    } else {
                                        var comments = JSON.parse(res);
                                        $.each(comments, function(index) {
                                            $('[data-commentsdiv="'+buttonid+'"]').append('<p>'+comments[index]['Comment']+'<br />From '+comments[index]['CommentedBy']+' - '+comments[index]['CommentedAt']+'</p><hr />');
                                        });
                                    }
                                },
                                error: function() {
                                    $('[data-commentsdiv="'+buttonid+'"]').html("<hr /><p>Error: couldn't load comments properly</p>");
                                    console.log(JSON.parse(res.responseText));
                                }
                            });
                        }
                        
                    });
                    scrollToAnchor(window.location.hash);
                },
                error: function(res) {
                    console.log(JSON.parse(res.responseText));
                }
            });
        });
        
        $("#new_post_btn").click(function() {
            $("#new_post_form").slideDown(300);
        });
        $("#close_form").click(function() {
            $("#new_post_form").slideUp(300);
        });
    </script>
    
</body>

</html>