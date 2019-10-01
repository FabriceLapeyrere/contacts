<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'conf/main.php';
$C=Config::get();
$hash=$_GET['h'];
$params=json_decode(base64_decode($hash),true);
$contact=$params['contact'];
$email= isset($params['email']) ? $params['email'] : "";
$envoi=$params['envoi'];
$url=$params['url'];
if (ctype_digit(strval($contact)) && ctype_digit(strval($envoi))) {
    // $contact is an integer
    $isimg=isset($params['isImg']) ? $params['isImg'] : 0;
    if ($url=="") {
    	exit();
    }
    if ($isimg==1){
    	// on redirige
    	if (file_exists($url)) {
    		while(ob_get_level()) ob_end_clean();
    		header('Connection: close');
    		ignore_user_abort();
    		ob_start();
    		$path_parts=pathinfo($url);
    		switch(strtolower($path_parts['extension'])) {
    			case "gif":
    			header("Content-type: image/gif");
    			break;
    			case "jpg":
    			case "jpeg":
    			header("Content-type: image/jpeg");
    			break;
    			case "png":
    			header("Content-type: image/png");
    			break;
    			case "bmp":
    			header("Content-type: image/bmp");
    			break;
    		}
    		header('Content-Length: ' . filesize($url));
    		header('Content-Disposition: filename=' . basename($url));
    		readfile($url);
    		ob_end_flush();
    		flush();
    	} else {
    		exit();
    	}
    } else {
    	// on redirige
    	while(ob_get_level()) ob_end_clean();
    	header('Connection: close');
    	ignore_user_abort();
    	ob_start();
    	$size = ob_get_length();
    	header("Content-Length: $size");
    	header('location:'.$url);
    	ob_end_flush();
    	flush();
    }
	// on remplit la base
	$db= new DB(true);
	$c=Contacts::get_casquette($contact,false,1);
	$nom=trim($c['prenom']." ".$c['nom']);
	$date=millisecondes();
	$notify=false;
	if ($isimg==1){
		$db->database->beginTransaction();
		$query = "SELECT * FROM r WHERE id_envoi=$envoi AND id_cas=$contact AND email='$email' AND url='Lu'";
		$res=array();
		foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
			$res[]=$row;
		}
		if (count($res)==0) {
			$insert = $db->database->prepare('INSERT INTO r (id_cas, id_envoi, email, url, date) VALUES (?,?,?,?,?) ');
			$insert->execute(array($contact, $envoi, $email, 'Lu', $date));
			$notify=true;
		}
		$db->database->commit();
	} else {
		$insert = $db->database->prepare('INSERT INTO r (id_cas, id_envoi, email, url, date) VALUES (?,?,?,?,?) ');
		$insert->execute(array($contact, $envoi, $email, $url, $date));
		$notify=true;
	}
	if ($notify) {
		WS_maj(array("envoi/$envoi","impacts","contact/*"));
		//on previent l'expéditeur!!
		if ($C->mailing->redirect_notification->value) {
			$e=Mailing::get_envoi($envoi,'',1);
			$expediteur=$e['expediteur'];
			$exp=$C->mailing->expediteurs->value[$expediteur->id];

			require 'server/lib/PHPMailer/PHPMailerAutoload.php';
			$mail = new PHPMailer();
			$mail->SetLanguage("fr","server/lib/PHPmailer/language/");
			$mail->IsSMTP();
			$mail->Host = $exp->smtp_host->value;
			$mail->Port = $exp->smtp_port->value;
			$mail->SMTPAuth = $exp->smtp_auth->value;
			$mail->Username = $exp->smtp_username->value;
			$mail->Password = $exp->smtp_pwd->value;
			$mail->CharSet = "UTF-8";
			$mail->Subject = '[[Nouveau clic]]';
			$mail->Body = "$nom (N° $contact) a cliqué sur $url !";
			$mail->From = $exp->email->value;
			$mail->FromName = 'Newsletter';
			$mail->AddAddress($exp->email->value,$exp->nom->value);
			$mail->Send();
		}
	}
} else {
    if (isset($params['url'])) {
        // on redirige
    	while(ob_get_level()) ob_end_clean();
    	header('Connection: close');
    	ignore_user_abort();
    	ob_start();
    	$size = ob_get_length();
    	header("Content-Length: $size");
    	header('location:'.$url);
    	ob_end_flush();
    	flush();
    } else {
?>
<head>
    <title>Lien malformé...</title>
    <meta charset="UTF-8">
</head>
<body>
    Le lien est malformé, désolé...
</body>
<?
    }
}
