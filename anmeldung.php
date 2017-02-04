<?php 
require_once('conf.php');
$db_link = mysqli_connect (MYSQL_HOST,  MYSQL_USER,  MYSQL_PW, MYSQL_DB);
header('Content-Type: text/html; charset=UTF-8');
if (! $db_link ) { 
	echo 'Verbindung nicht möglich: '; 
	die;
}

if (! isset($_REQUEST["voucher"])){
	header('Location:login.html');
}
$voucher = $_REQUEST["voucher"];
//echo $voucher;

if ($result = mysqli_query($db_link, "SELECT * FROM FWV_REGISTRATIONS WHERE CODE = $voucher LIMIT 1")) {
    //print_r($result);
    
    $data = mysqli_fetch_array($result, MYSQLI_ASSOC);
    
    //print_r($data);
    
	if(!$data){
		header('Location:login.html');
	}
	
	if(isset($_POST['voucher'])){
		//print_r($_POST);
		
	 	$success=	insertData($_POST, $db_link);
	 	print_r($success);
	 	if($success){
			sendMail($_POST);

	 		//	header('Location:anmeldung.php?voucher='.$_POST["voucher"]);
	 	}
	}
	
	
	

	printForm($data);
    /* free result set */
    mysqli_free_result($result);
}else{
	echo "voucher unbekannt!";
}

function printForm($data){
	echo '<!DOCTYPE html>
			<html lang="de">
				<head>
					<meta charset="utf-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>Feuerwehrfest Velber</title>
					<link rel="stylesheet" href="style.css">
				</head>
		<body>
		<div id="main">
		<h1>Willkommen '.$data["OFW_NAME"].'</h1>
		<h2>105 Jahre Freiwillige Feuerwehr Velber. 1 - 3 September</h2>';
		
	
	echo '<form action="anmeldung.php" method="POST"> ';
	echo '<input type="hidden" id="voucher" name="voucher" value="'. $data["CODE"] .'"/>';	
	echo '<label for="email">Kontakt Email</label> <input id="email" type="text" name="CONTACT_EMAIL" value="'. $data["CONTACT_EMAIL"] .'" required /> ';
	echo '<label for="name">Ansprechpartner</label> <input id="name" type="text" name="CONTACT_NAME" value="'. $data["CONTACT_NAME"] .'" required /> ';
	echo '<label for="kommers">Kommers</label><input id="kommers" type="number" name="COUNT_KOMMERS" value="'. $data["COUNT_KOMMERS"] .'"min="1" max="100" required /> ';
	echo '<label for="kommersEssen">Kommers mit Essen</label><input id="kommersEssen" type="number" name="COUNT_KOMMERS_ESSEN" value="'. $data["COUNT_KOMMERS_ESSEN"] .'" min="1" max="100" required /> ';
	echo'<label for="ausmarsch">Beim Ausmarsch dabei</label>';
	if ($data["AUSMARSCH"] == 1){
		echo '<input id="ausmarsch" type="checkbox" name="AUSMARSCH" value="1" checked="checked"/>';
	}else{
		echo '<input id="ausmarsch" type="checkbox" name="AUSMARSCH" value="1"/>';
	}
	echo'<label for="musikzug">Musikzug dabei</label>';
	if ($data["MUSIKZUG"] == 1){
		echo '<input id="musikzug" type="checkbox" name="MUSIKZUG" value="1" checked="checked"/>';
	}else{
		echo '<input id="musikzug" type="checkbox" name="MUSIKZUG" value="1"/>';
	}
	echo'<label for="teilnahme">Wir nehmen Teil</label>';
	if ($data["TEILNAHME"] == 1){
		echo '<input id="teilnahme" type="checkbox" name="TEILNAHME" value="1" checked="checked"/>';
	}else{
		echo '<input id="teilnahme" type="checkbox" name="TEILNAHME" value="1" />';
	}	
	echo '<input type="submit" value="senden" />';
	echo '</form>';
	echo '</div></body></html>';
}

function insertData($post, $link){
	$a = isset($post['AUSMARSCH']) ?1 : 0;
	$m= isset($post['MUSIKZUG']) ?1 : 0;
	$t = isset($post['TEILNAHME']) ?1 : 0;
	$data = array(
		"`CONTACT_EMAIL` = '" . mysqli_real_escape_string($link, $post['CONTACT_EMAIL']) . "'",
		"`CONTACT_NAME` = '" . mysqli_real_escape_string($link, $post['CONTACT_NAME']). "'",
		"`COUNT_KOMMERS` = '" . mysqli_real_escape_string($link, $post['COUNT_KOMMERS']). "'",
		"`COUNT_KOMMERS_ESSEN` = '" . mysqli_real_escape_string($link, $post['COUNT_KOMMERS_ESSEN']). "'",
		"`AUSMARSCH` = " . $a,
		"`MUSIKZUG` = "  . $m,
		"`TEILNAHME` = " . $t,
		"`UPDATED`= '" . date("Y-m-d H:i:s")."'"
	);
	$sql = "update FWV_REGISTRATIONS 
				SET " .implode(', ', $data) . "  
			where code = '".mysqli_real_escape_string($link, $post['voucher'])."'";
	//var_dump (mysqli_query($link, $sql));
	//var_dump(mysqli_error($link));
	//echo $sql;
	return mysqli_query($link, $sql);
}

function sendMail($post){
			$subject = "Anmeldung zum Feuerwehrfest in Velber";
			$email = $post["CONTACT_EMAIL"];
			$name = $post['CONTACT_NAME'];
			$kommers = $post['COUNT_KOMMERS'];
			$kommersEssen = $post['COUNT_KOMMERS_ESSEN'];
			$a = isset($post['AUSMARSCH']) ?'Ja' : 'Nein';
			$m= isset($post['MUSIKZUG']) ?'Ja' : 'Nein';
			$t = isset($post['TEILNAHME']) ?'Ja' : 'Nein';
			$voucher = $post['voucher'];
			$mail_header = "From:feuerwehrfest@feuerwehr-velber.de <feuerwehrfest@feuerwehr-velber.de>\r\n".
			'Content-type: text/plain; charset=UTF-8' . "\r\n";
			// Nachrichtenlayout erstellen
			$message = '
Ihre Daten

Voucher:			'.$voucher.'
Email:				'.$email.'
Ansprechpartner:'.$name.'
Kommers:			'.$kommers.'
Kommers Essen:	'.$kommersEssen.'
Ausmarsch:		'.$a.'
Musikzug:			'.$m.'
Teilnahme:			'.$t;
	$mailsuccess = mail($email, $subject, $message, $mail_header );	
	 		
}