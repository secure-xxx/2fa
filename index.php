<?php

require '/etc/nginx/auth/conf.php';

session_start();

if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 86400) {
    // session started more than 30 minutes ago
    session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
}


if ($session = session_id())

{

  $conn = mysqli_connect($servername, $username, $password, $database);



$sql = "SELECT verify FROM data WHERE session='$session'";
$result = (mysqli_query($conn, $sql))->fetch_assoc()['verify'];

mysqli_close($conn);

if ($result == "") {

echo <<<HTML

<style>

	.transparent {
	display: block;
	background-size: cover;
	text-align: center;
	}

	.transparent:before {
	content: "";
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: transparent;
	}
	
	.form-inner {position: relative;}
	.form-inner h4 {
	position: relative;
	color: BLACK;
	font-family: 'Roboto', sans-serif;
	font-size: 22px;
	text-transform: uppercase;
	}

	.form-inner {position: relative;}
	.form-inner h5 {
	position: relative;
	color: BLACK;
	font-family: 'Roboto', sans-serif;
	font-weight: 4000;
	font-size: 10px;
	text-transform: uppercase;
	}

	.form-inner label {
	display: block;
	padding-left: 15px;
	font-family: 'Roboto', sans-serif;
	color: BLACK;
	text-transform: uppercase;
	font-size: 14px;
	}
	
	.form-inner input {
	width: 45%;
	height: 40px;
	margin: 0px 0 5px;
	border-width: 0;
	line-height: 40px;
	border-radius: 5px;
	background: GREY;
	text-align: center;
	font-family: 'Roboto', sans-serif;
	font-size: 18px;
	text-transform: uppercase;
	color: WHITE;
	}

	.bg {
	background-image: url(https://www.rgi-rn.ru/wp-content/uploads/2021/06/2fa-scaled.jpeg);
	background-size: cover;
	padding-top: 15%;
	padding-left:33%;
	padding-right:33%;
	box-sizing: border-box;
	width: 100%;
	height: 100%;
	text-align: center;
	}
	
	.a {
	color: BLACK;
        font-family: 'Roboto', sans-serif;
        font-weight: 4000;
        font-size: 10px;
        text-transform: uppercase;
	}

	::placeholder {
	color: #575555;
	}

	</style>
<div class="bg">
<div class"img"><img width="150px" height="150px" src="https://www.rgi-rn.ru/wp-content/uploads/2021/05/logo-color-finalv4-rounded-type.png"></div>
<form name='phone' id='phone' method='post' class="transparent">
   <div class="form-inner">
	 <h4>для доступа к запрашиваемому корпоративному ресурсу необходимо подтвердить номер вашего телефона</h4>

     <input id="phone" name="phone" maxlength="11" placeholder="89000000000">
	<br>
     <input type="submit" value="Отправить">
	<h5>* Нажимая на кнопку "Отправить", вы соглашаетесь на передачу, обработку и хранение переданных персональных данных, файлов Cookies, также, вы подтверждаете, 
 что вы уполномочены соответствующими привилегиями для доступа к данной ИС. О неисправностях ИС или некорректном функционировании необходимо сообщать по электронной почте: <a class="a" href="mailto://support@rgi-rn.ru">support@rgi-rn.ru</a> ИЛИ по телефону: <a class="a" href="tel://83432278808">8(343)227-88-08</a></h5>
  </div>
</div>
</form>


HTML;


// Присваиваем переменной значение из поля
if ($phone = $_POST['phone'])

{

//LDAP Bind paramters, need to be a normal AD User account.
 
if (FALSE === $ldap_connection){
    // Uh-oh, something is wrong...
 echo 'Unable to connect to the ldap server';
}
 
// We have to set this option for the version of Active Directory we are using.
ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3) or die('Unable to set LDAP protocol version');
ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.
 
if (TRUE === ldap_bind($ldap_connection, $ldap_username, $ldap_password)){
 
 //Your domains DN to query
    $ldap_base_dn = 'DC=rgi-rn,DC=ru';
  
 //Get standard users and contacts
    $search_filter = '(|(objectCategory=person)(objectCategory=contact))';
  
 //Connect to LDAP
 $result = ldap_search($ldap_connection, $ldap_base_dn, $search_filter);
  
    if (FALSE !== $result){
 $entries = ldap_get_entries($ldap_connection, $result);
  
 // Uncomment the below if you want to write all entries to debug somethingthing 
 //var_dump($entries);
  
 //For each account returned by the search
 for ($x=0; $x<$entries['count']; $x++){
  
 //
 //Retrieve values from Active Directory
 //
  
 //Windows Usernaame
 $LDAP_samaccountname = "";
  
 if (!empty($entries[$x]['samaccountname'][0])) {
 $LDAP_samaccountname = $entries[$x]['samaccountname'][0];
 if ($LDAP_samaccountname == "NULL"){
 $LDAP_samaccountname= "";
 }
 } else {
 //#There is no samaccountname s0 assume this is an AD contact record so generate a unique username
  
 $LDAP_uSNCreated = $entries[$x]['usncreated'][0];
 $LDAP_samaccountname= "CONTACT_" . $LDAP_uSNCreated;
 }
  
  
 //Telephone Number
 $LDAP_DDI = "";
  
 if (!empty($entries[$x]['mobile'][0])) {
 $LDAP_DDI = $entries[$x]['mobile'][0];
 if ($LDAP_DDI == "NULL"){
 $LDAP_DDI = "";
 }
 if ($LDAP_DDI == $phone){

 $trigger = "success";
} 
}

  
 } //END for loop
 } //END FALSE !== $result


  
 ldap_unbind($ldap_connection); // Clean up after ourselves.
 
} //END ldap_bind

//start triggering ldap search
if ($trigger == "success")
{

$code = rand (100000 , 999999);
$verify = "1";

$updateconn = mysqli_connect($servername, $username, $password, $database);
$update = "INSERT INTO data (session, code, verify) VALUES ('$session', '$code', '$verify')";
mysqli_query($updateconn, $update);

mysqli_close($updateconn);

/////////////////////////////START SENDING SMS///////////////////////////////
$pattern = '/^8/';
$replacement = '7';
$send = preg_replace($pattern, $replacement, $phone);

$bearer = '*************';
$naming = '*************';
$msid = $send;
$message = "Код подтверждения: " . $code;

$httpHeaders = array(
    'http' => array(
        'protocol_version' => 1.1,
        'header' => "Authorization:Bearer ".$bearer,
    ));

$context = stream_context_create($httpHeaders);

$params = array('stream_context' => $context, 
                                'trace' => 1,
                                'exceptions' => 0);

$client = new SoapClient("**************",
        $params);

$result = $client->SendMessage(
                        array (
                                        "naming" => $naming,
                                        "msid" => $msid,
                                        "message" => $message,
                                )
                );
/////////////////////////////END SENDING SMS///////////////////////////////

header("Refresh:0");


}      //end of success trigger

if ($trigger !== "success")  ////////////////if phone wrong
{

session_destroy();
header("Refresh:0");

}



} // end if post








}				////end if result = 3

///////////////#########################################////////////////


if ($result == "1") {

echo <<<HTML

	<style>

	.transparent {
//	position: relative;
//	max-width: 500px;
//	padding: 10px 10px;
	background-size: cover;
	text-align: center;
	}

	.transparent:before {
	content: "";
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: transparent;
	}
	
	.form-inner {position: relative;}
	.form-inner h4 {
	position: relative;
	color: BLACK;
	font-family: 'Roboto', sans-serif;
	font-size: 22px;
	text-transform: uppercase;
	}

	.form-inner {position: relative;}
	.form-inner h5 {
	position: relative;
	color: BLACK;
	font-family: 'Roboto', sans-serif;
	font-weight: 4000;
	font-size: 10px;
	text-transform: uppercase;
	}

	.form-inner label {
	display: block;
	padding-left: 15px;
	font-family: 'Roboto', sans-serif;
	color: BLACK;
	text-transform: uppercase;
	font-size: 14px;
	}
	
	.form-inner input {
	width: 50%;
	height: 40px;
	margin: 0px 0 5px;
	border-width: 0;
	line-height: 40px;
	border-radius: 5px;
	background: GREY;
	text-align: center;
	font-family: 'Roboto', sans-serif;
	font-size: 18px;
	text-transform: uppercase;
	color: WHITE;
	}

	.bg {
	background-image: url(https://www.rgi-rn.ru/wp-content/uploads/2021/06/2fa-scaled.jpeg);
	background-size: cover;
	padding-top: 15%;
	padding-left:33%;
	padding-right:33%;
	box-sizing: border-box;
	width: 100%;
	height: 100%;
	text-align: center;
	}
	
	</style>

<div class="bg">
<div class"img"><img width="150px" height="150px" src="https://www.rgi-rn.ru/wp-content/uploads/2021/05/logo-color-finalv4-rounded-type.png"></div>
<form name='pass' id='pass' method='post' class="transparent">
   <div class="form-inner">
     <h4>введите код из SMS</h4>
     <input id="pass" name="pass" maxlength="6">
     <input type="submit" value="Отправить">
  </div>
</form>
</div>

HTML;


$pass = $_POST['pass'];
if ($pass != "")

{



  $conn = mysqli_connect($servername, $username, $password, $database);



$sql = "SELECT session FROM data WHERE code=$pass";
$result = (mysqli_query($conn, $sql));

while ($row = $result->fetch_assoc()) {
        $req = ($row["session"]);
    }


mysqli_close($conn);

if ($req == $session)

{



$updateconn = mysqli_connect($servername, $username, $password, $database);
$verify = "2";
$update = "UPDATE data SET verify=$verify WHERE code='$pass'";
mysqli_query($updateconn, $update);
mysqli_close($updateconn);


header("Refresh:0");

}

if ($req !== $session)

{

session_regenerate_id(true);
header("Refresh:0");

}


}///////////////////if $req == session



} //////////end if $result==2



}//end if session id

?>
