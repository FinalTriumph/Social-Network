<?php 

require_once("DB.php");

$db = new DB('127.0.0.1', 'SocialNetwork', 'finaltriumph', '');

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    echo json_encode(($db->query("SELECT * FROM users")));
    http_response_code(200);

    
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {
    echo $_POST['text'];
    
} else {
    http_response_code(405);
}

?>

<form action="index.php" method="delete">
    <input type="text" name="text" value="" placeholder="Text ..."><p />
    <input type="button" name="submit" id="submit" value="Submit">
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js"></script>
<script type="text/javascript">
    $("#submit").click(function() {
        $.ajax({
            type: 'GET',
            url: window.location
        })
    })
</script>