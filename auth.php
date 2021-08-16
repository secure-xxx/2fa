<?php
require '/etc/nginx/auth/conf.php';
session_start();

if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 86400) {
    // session started more than times ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}


if ($session = session_id())

{


  $conn = mysqli_connect($servername, $username, $password, $database);



$sql = "SELECT verify FROM data WHERE session='$session'";
$result = (mysqli_query($conn, $sql))->fetch_assoc()['verify'];

mysqli_close($conn);

if ($result == "2") {

http_response_code(200);
}

else     {

http_response_code(401);
}


}//end if session id

?>
