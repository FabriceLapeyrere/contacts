<?php
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'fake_ws/conf.php';
include 'conf/main.php';
$hash=$_GET['h'];
$params=json_decode(base64_decode($hash),true);
$contact=$params['contact'];
$envoi=$params['envoi'];
$url=$params['url'];

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

// on remplit la base
$db= new DB();
$c=Contacts::get_casquette($contact,false,1);
$nom=trim($c['prenom']." ".$c['nom']);
$date=millisecondes();
$insert = $db->database->prepare('INSERT INTO r (id_cas, id_envoi, url, date) VALUES (?,?,?,?) ');
$insert->execute(array($contact, $envoi, $url, $date));
CR::maj(array("envoi/$envoi"));

//on previent l'expéditeur!!
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
$mail->FromName = 'Le Fil';
$mail->AddAddress($exp->email->value,$exp->nom->value);
$mail->Send();
