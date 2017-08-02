<?php

class Image {
    
    public static function uploadImage($formname, $query, $params) {
        $image = base64_encode(file_get_contents($_FILES[$formname]['tmp_name']));
        
        $options = array('http'=>array(
            'method'=>"POST",
            'header'=>"Authorization: Bearer 8b692d2c84cfdc5b31866f7895eb45e7552a4959\n".
            "Content-Type: application/x-www-form-urlencoded",
            'content'=>$image
        ));
    
        $context = stream_context_create($options);
        
        $imgurURL = "https://api.imgur.com/3/image";
        
        if ($_FILES[$formname]['size'] > 10240000) {
            die('Image too big, must be 10MB or less!');
        }
        
        $response = file_get_contents($imgurURL, false, $context);
        
        $response = json_decode($response);
        
        $imglink = $response->data->link;
        
        //DB::query('UPDATE users SET profileimg=:profileimg WHERE id=:userid', array(':profileimg'=>$imglink, ':userid'=>$userid));
        
        $preparams = array(':'.$formname=>$imglink);
        
        $params = $preparams + $params;
        
        DB::query($query, $params);
    }
}

?>