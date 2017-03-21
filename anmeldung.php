<?php 
require_once('conf.php');
$db_link = mysqli_connect (MYSQL_HOST,  MYSQL_USER,  MYSQL_PW, MYSQL_DB);
header('Content-Type: text/html; charset=UTF-8');
if (! $db_link ) { 
	echo 'Verbindung nicht möglich: '; 
	die;
}

if (! isset($_REQUEST["voucher"])){
	header('Location:index.html');
}
$voucher = $_REQUEST["voucher"];
//echo $voucher;

if ($result = mysqli_query($db_link, "SELECT * FROM FWV_REGISTRATIONS WHERE CODE = '$voucher' LIMIT 1")) {
    //print_r($result);
    
    $data = mysqli_fetch_array($result, MYSQLI_ASSOC);
    
    //print_r($data);
    
	if(!$data){
		header('Location:index.html');
	}
	
	if(isset($_POST['voucher'])){
		//print_r($_POST);
		
	 	$success=	insertData($_POST, $db_link);
	 	print_r($success);
	 	if($success){
			sendMail($_POST);

	 		//	header('Location:anmeldung.php?voucher='.$_POST["voucher"]);
	 		
	 		echo '<h1>Vielen Dank für Ihre Mitteilung.</h1>';
	 		exit;
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
	echo'<table>
	<tr>
		<td><label for="email">Kontakt Email</label></td> 
		<td><input id="email" type="text" class="inptext" name="CONTACT_EMAIL" value="'. $data["CONTACT_EMAIL"] .'" required /> </td>
	</tr>
	<tr>
	<td><label for="name">Ansprechpartner</label></td> 
	<td><input id="name" type="text" class="inptext" name="CONTACT_NAME" value="'. $data["CONTACT_NAME"] .'" required /> </td>
	</tr>
	<tr>
	<td><label for="kommers">Teilnehmer Kommers</label></td>
	<td><input id="kommers" type="number" class="inpnumber" name="COUNT_KOMMERS" value="'. $data["COUNT_KOMMERS"] .'"min="1" max="100" required /> </td>
	</tr>
	<tr>
	<td><label for="kommersEssen">Anmeldungen zum Essen</label></td>
	<td><input id="kommersEssen" type="number" class="inpnumber" name="COUNT_KOMMERS_ESSEN" value="'. $data["COUNT_KOMMERS_ESSEN"] .'" min="1" max="100" required /> </td>
	</tr>
	<tr>
	<td><label for="ausmarsch">Teilnehmer Ausmarsch</label></td>
	<td><input id="ausmarsch" type="number" class="inputnumber" name="AUSMARSCH" value="'.$data["AUSMARSCH"].'"/> </td>
	</tr>
	<tr>
	<td><label for="musikzug">mit Musikzug</label></td>
	<td><input id="musikzug" type="checkbox" name="MUSIKZUG" value="1" '; if ($data["MUSIKZUG"] == 1){ echo 'checked="checked" ';} echo '/> </td>
	</tr>
	<tr>
	<td><label for="teilnahme">Wir können leider nicht teilnehmen</label> </td>
	<td><input id="teilnahme" type="checkbox" name="TEILNAHME" value="1"';if ($data["ABSAGE"] == 1){ echo 'checked="checked" ';} echo'/> </td>
	</tr>
	</table>';
	
	echo '<input type="submit" value="senden" />';
	echo '</form>';
	echo '</div></body></html>';
}

function insertData($post, $link){
	$m= isset($post['MUSIKZUG']) ?1 : 0;
	$t = isset($post['TEILNAHME']) ?1 : 0;
	$data = array(
		"`CONTACT_EMAIL` = '" . mysqli_real_escape_string($link, $post['CONTACT_EMAIL']) . "'",
		"`CONTACT_NAME` = '" . mysqli_real_escape_string($link, $post['CONTACT_NAME']). "'",
		"`COUNT_KOMMERS` = '" . mysqli_real_escape_string($link, $post['COUNT_KOMMERS']). "'",
		"`COUNT_KOMMERS_ESSEN` = '" . mysqli_real_escape_string($link, $post['COUNT_KOMMERS_ESSEN']). "'",
		"`AUSMARSCH` = '" .  mysqli_real_escape_string($link, $post['AUSMARSCH']). "'",
		"`MUSIKZUG` = "  . $m,
		"`ABSAGE` = " . $t,
		"`UPDATED`= '" . date("Y-m-d H:i:s")."'"
	);
	$sql = "update FWV_REGISTRATIONS 
				SET " .implode(', ', $data) . "  
			where code = '".mysqli_real_escape_string($link, $post['voucher'])."'";
	var_dump (mysqli_query($link, $sql));
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
			$t = isset($post['ABSAGE']) ?'JA' : 'Nein';
			$voucher = $post['voucher'];
			$mail_header = "From:feuerwehrfest@feuerwehr-velber.de <feuerwehrfest@feuerwehr-velber.de>\r\n".
			'Content-type: text/html; charset=UTF-8' . "\r\n";
			$message = '
<h1>Ihre Daten</h1>

<table> 
<tr>
	<td>Voucher:</td>
	<td>	'.$voucher.'</td>
</tr><tr>
	<td>Email:</td>
	<td>'.$email.'</td>
</tr><tr>
	<td>Ansprechpartner:</td>
	<td>'.$name.'</td>
</tr><tr>
	<td>Kommers:</td>
	<td>'.$kommers.'</td>
</tr><tr>
	<td>Kommers Essen:	</td>
	<td>'.$kommersEssen.'</td>
</tr><tr>
	<td>Ausmarsch:</td>
	<td>'.$a.'</td>
</tr><tr>
	<td>Musikzug:</td>
	<td>'.$m.'</td>
</tr>
</tr><tr>
	<td>Absage:	</td>
	<td>'.$t.'</td>
</tr>
</table>';

				
	$mailsuccess = mail($email, $subject, $message, $mail_header );	
	 		
}