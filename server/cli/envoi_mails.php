<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */
$id_envoi=$argv[2];
require 'server/lib/PHPMailer/PHPMailerAutoload.php';
error_log(date('d/m/Y H:i:s')." - Script lancé.\n", 3, "data/log/envoi.log");
$envoi=Mailing::get_envoi($id_envoi,'',1);
if($envoi['statut']==1) {
	Mailing::play_envoi($id_envoi);
	error_log(date('d/m/Y H:i:s')." - Envoi numéro $id_envoi commencé.\n", 3, "data/log/envoi.log");
	$html='<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/><style>'.$C->news->css->value.'</style></head><body>'.$envoi['html'].'<body>';
	$sujet=$envoi['sujet'];
	$nb=$envoi['nb'];
	$expediteur=$envoi['expediteur'];
	$pjs=$envoi['pjs'];
	$pas=0;
	$exp=$C->mailing->expediteurs->value[$expediteur->id];
	$mailing_nbmail=$C->mailing->nbmail->value;
	$mailing_t_pause=$C->mailing->t_pause->value;
	$use_redirect=$C->app->use_redirect->value;
	$redirect_url=$C->app->url->value."/r.php";
	$unsubscribe_url=$C->app->url->value."/desinscription.php";
	$mail = new PHPMailer();
	$mail->SetLanguage("fr","server/lib/PHPmailer/language/");
	$mail->IsSMTP();
	$mail->Host = $exp->smtp_host->value;
	$mail->Port = $exp->smtp_port->value;
	$mail->SMTPAuth = $exp->smtp_auth->value;
	$mail->Username = $exp->smtp_username->value;
	$mail->Password = $exp->smtp_pwd->value;
	$mail->CharSet = "UTF-8";
	$mail->Subject = $sujet;
	foreach ($pjs as $pj) {
		if(!$pj->used) $mail->AddAttachment($pj->path);
	}
		
	$mail->MsgHTML($html);
	$mail->From = $exp->email->value;
	$mail->FromName = $exp->nom->value;
	error_log(date('d/m/Y H:i:s')." - nb = ".Mailing::nb_messages_boite_envoi($id_envoi)."\n", 3, "data/log/envoi.log");
	while (Mailing::nb_messages_boite_envoi($id_envoi)>0) {
		$htmlr=$html;
		if ($pas==$mailing_nbmail) {
			$pas=1;
			error_log(date('d/m/Y H:i:s')." - On attend\n", 3, "data/log/envoi.log");
			for($j=1;$j<$mailing_t_pause;$j++) {
				sleep(1);
				if(Mailing::statut_envoi($id_envoi)==2) {
					error_log(date('d/m/Y H:i:s')." - statut : ".Mailing::statut_envoi($id_envoi)." -> arret demandé\n", 3, "data/log/envoi.log");
					Mailing::pause_envoi($id_envoi);
					error_log(date('d/m/Y H:i:s')." - statut : ".Mailing::statut_envoi($id_envoi)."\n", 3, "data/log/envoi.log");
					error_log(date('d/m/Y H:i:s')." - Envoi numéro $id_envoi arrété.\n", 3, "data/log/envoi.log");
					exit(0);
				}
		
			}
		} else {
			$pas++;
		}
		if(Mailing::statut_envoi($id_envoi)==2) {
			error_log(date('d/m/Y H:i:s')." - statut : ".Mailing::statut_envoi($id_envoi)." -> arret demandé\n", 3, "data/log/envoi.log");
			Mailing::pause_envoi($id_envoi);
			error_log(date('d/m/Y H:i:s')." - statut : ".Mailing::statut_envoi($id_envoi)."\n", 3, "data/log/envoi.log");
			error_log(date('d/m/Y H:i:s')." - Envoi numéro $id_envoi arrété.\n", 3, "data/log/envoi.log");
			exit(0);
		}
		$m=Mailing::envoi_premier_message($id_envoi);
		$i=$m['i'];
		$c=Contacts::get_casquette($m['id_cas'],1);
		$usbcr_hash=base64_encode(json_encode(array("emails"=>$c['emails'])));
		$unsubscribeurl="$unsubscribe_url?hash=$usbcr_hash";
		if($use_redirect){
			$params=array(
				'contact'=>$m['id_cas'],
				'envoi'=>$id_envoi
			);
			$htmlr=replaceHref($htmlr, $redirect_url, $params);
		}
		$htmlr=str_replace("##UNSUBSCRIBEURL##",$unsubscribeurl,$htmlr);
		$mail->MsgHTML($htmlr);
		$nom=trim($c['prenom']." ".$c['nom']);
		$emails=$c['emails'];
		foreach($emails as $email){
			$mail->AddAddress($email,$nom);
		}
		if (!$mail->Send())
		{
			$log=array(
				'date'=>millisecondes(),
				'erreur'=>$mail->ErrorInfo,
				'i'=>$i,
				'nb'=>$nb,
				'cas'=>$c
			);
			Mailing::log_erreur($id_envoi,$log);
			Mailing::message_erreur($m['id'],$mail->ErrorInfo);
		}
		else
		{
			$log=array(
				'date'=>millisecondes(),
				'erreur'=>'',
				'i'=>$i,
				'nb'=>$nb,
				'cas'=>$c
			);
			Mailing::log_succes($id_envoi, $log);
			Mailing::sup_message($m['id']);
		}
		$mail->ClearAddresses();
		CR::maj(array("envoi/$id_envoi"));
	}
}
sleep(2);
Mailing::pause_envoi($id_envoi);
error_log(date('d/m/Y H:i:s')." - statut : ".Mailing::statut_envoi($id_envoi)."\n", 3, "data/log/envoi.log");
error_log(date('d/m/Y H:i:s')." - Envoi numéro $id_envoi arrété.\n", 3, "data/log/envoi.log");
exit(0);
?>
