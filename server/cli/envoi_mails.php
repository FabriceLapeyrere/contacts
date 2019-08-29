<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */
$id_envoi=$argv[2];
require 'server/lib/PHPMailer/PHPMailerAutoload.php';
error_log(date('d/m/Y H:i:s')." - Script lancé.\n", 3, "../data/log/envoi.log");
$envoi=Mailing::get_envoi($id_envoi,'',1);
$C=Config::get();
if($envoi['statut']==1) {
    $emails_ok=array();
	$M=Mailing::do_play_envoi($id_envoi);
	WS_maj($M['maj']);
	error_log(date('d/m/Y H:i:s')." - Envoi numéro $id_envoi commencé.\n", 3, "../data/log/envoi.log");
	$sujet=$envoi['sujet'];
	$html=$envoi['html'];
	if ($envoi['type']=='news') {
		$html=$C->news->main_wrapper->value;
		$html=str_replace('::sujet::',$sujet,$html);
		$html=str_replace('::css::',$C->news->css->value,$html);
		$html=str_replace('::html::',$envoi['html'],$html);
	}
	if ($envoi['type']=='mail') {
		$html=$C->email->main_wrapper->value;
		$html=str_replace('::sujet::',$sujet,$html);
		$html=str_replace('::css::',$C->email->css->value,$html);
		$html=str_replace('::html::',$envoi['html'],$html);
	}
	$nb=$envoi['nb'];
	$expediteur=$envoi['expediteur'];
	$pjs=$envoi['pjs'];
	$pas=0;
	$exp=$C->mailing->expediteurs->value[$expediteur->id];
	$mailing_nbmail=$C->mailing->nbmail->value;
	$mailing_t_pause=$C->mailing->t_pause->value;
	$use_redirect=$C->mailing->use_redirect->value;
	$remote_imgs=$C->mailing->remote_imgs->value;
	$base=$C->app->url->value;
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

	$mail->From = $exp->email->value;
	$mail->FromName = $exp->nom->value;
	error_log(date('d/m/Y H:i:s')." - nb = ".Mailing::nb_messages_boite_envoi($id_envoi)."\n", 3, "../data/log/envoi.log");
	while (Mailing::nb_messages_boite_envoi($id_envoi)>0) {
		$htmlr=$html;
		if ($pas==$mailing_nbmail) {
			$pas=1;
			error_log(date('d/m/Y H:i:s')." - On attend\n", 3, "../data/log/envoi.log");
			for($j=1;$j<$mailing_t_pause;$j++) {
				sleep(1);
				if(Mailing::statut_envoi($id_envoi)==2) {
					error_log(date('d/m/Y H:i:s')." - statut : ".Mailing::statut_envoi($id_envoi)." -> arret demandé\n", 3, "../data/log/envoi.log");
					$M=Mailing::do_pause_envoi($id_envoi);
					WS_maj($M['maj']);
					error_log(date('d/m/Y H:i:s')." - statut : ".Mailing::statut_envoi($id_envoi)."\n", 3, "../data/log/envoi.log");
					error_log(date('d/m/Y H:i:s')." - Envoi numéro $id_envoi arrété.\n", 3, "../data/log/envoi.log");
					exit(0);
				}

			}
		} else {
			$pas++;
		}
		if(Mailing::statut_envoi($id_envoi)==2) {
			error_log(date('d/m/Y H:i:s')." - statut : ".Mailing::statut_envoi($id_envoi)." -> arret demandé\n", 3, "../data/log/envoi.log");
			$M=Mailing::do_pause_envoi($id_envoi);
			WS_maj($M['maj']);
			error_log(date('d/m/Y H:i:s')." - statut : ".Mailing::statut_envoi($id_envoi)."\n", 3, "../data/log/envoi.log");
			error_log(date('d/m/Y H:i:s')." - Envoi numéro $id_envoi arrété.\n", 3, "../data/log/envoi.log");
			exit(0);
		}
		$m=Mailing::envoi_premier_message($id_envoi);
		$i=$m['i'];
		if($use_redirect){
			$params=array(
				'contact'=>$m['id_cas'],
				'envoi'=>$id_envoi
			);
			$htmlr=replaceHref($htmlr, $redirect_url, $params);
		}
		if($remote_imgs){
			$params=array(
				'contact'=>$m['id_cas'],
				'envoi'=>$id_envoi
			);
			$htmlr=replaceImgs($htmlr, $base, $params, $use_redirect, $redirect_url);
		}
		$c=Contacts::get_casquette($m['id_cas'],false,1);
		$nom=trim($c['prenom']." ".$c['nom']);
		$emails=$c['emails'];
		foreach($emails as $email){
		    if (!in_array($email,$emails_ok)) {
		        //lien de desinscription
		        $usbcr_hash=base64_encode(json_encode(array("emails"=>array($email))));
		        $unsubscribeurl="$unsubscribe_url?hash=$usbcr_hash";
		        $html_def=str_replace("##UNSUBSCRIBEURL##",$unsubscribeurl,$htmlr);

		        $mail->MsgHTML($html_def);
			    $mail->AddAddress($email,$nom);

			    $emails_ok[]=$email;
		        $M=array();
		        if (!$mail->Send())
		        {
			        $log=array(
				        'date'=>millisecondes(),
				        'erreur'=>$mail->ErrorInfo,
				        'i'=>$i,
				        'nb'=>$nb,
				        'cas'=>$c,
				        'email'=>$email
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
				        'cas'=>$c,
				        'email'=>$email
			        );
			        $M=Mailing::do_log_succes($id_envoi, $log);
			        Mailing::sup_message($m['id']);
		        }
		        $mail->ClearAddresses();
		    }
		    else {
	            $log=array(
			        'date'=>millisecondes(),
			        'erreur'=>'',
			        'message'=>"E-mail déjà envoyé à cette adresse",
			        'i'=>$i,
			        'nb'=>$nb,
			        'cas'=>$c,
			        'email'=>$email
		        );
		        $M=Mailing::do_log_succes($id_envoi, $log);
		        Mailing::sup_message($m['id']);
		    }
		}
        if (count($emails)==0) {
            $log=array(
		        'date'=>millisecondes(),
		        'erreur'=>"Ce contact n'a pas d'e-mail...",
		        'i'=>$i,
		        'nb'=>$nb,
		        'cas'=>$c,
		        'email'=>$email
	        );
	        Mailing::log_erreur($id_envoi,$log);
		    Mailing::sup_message($m['id']);
        }
		WS_maj(array_merge($M['maj'],array("envoi/$id_envoi")));
	}
}
sleep(2);
$M=Mailing::do_pause_envoi($id_envoi);
WS_maj($M['maj']);
error_log(date('d/m/Y H:i:s')." - statut : ".Mailing::statut_envoi($id_envoi)."\n", 3, "../data/log/envoi.log");
error_log(date('d/m/Y H:i:s')." - Envoi numéro $id_envoi arrété.\n", 3, "../data/log/envoi.log");
exit(0);
?>
