<?php
class Imap {
	protected $WS;
	protected $from;
	public function __construct($WS,$from) {
 	 	$this->WS= $WS;
 	 	$this->from= $from;
	}
	public function start_check($id){
		$command = "nohup /usr/bin/php exec.php imap_check $id > /dev/null 2>&1 &";
		exec($command);
	}	
	public function check_imap($id){
		$C=Config::get();
		$exps=$C->mailing->expediteurs->value;
		Imap::set_status($id,count($exps),0,1,0,0);
		$cass=array();
		foreach($exps as $k=>$exp){
			if ($exp->imap_check->value) {
				$server=$exp->imap_host->value;
				$port=$exp->imap_port->value;
				$user=$exp->imap_username->value;
				$pass=$exp->imap_pwd->value;
				$mbox = imap_open("{".$server.":".$port."/novalidate-cert}", $user, $pass)
					 or die("can't connect: " . imap_last_error());
				$name = 'mails en erreur';
				$liste = imap_list($mbox, "{".$server."}", "*");
				#On crée le dossier s'il n'existe pas
				if (! in_array("{".$server."}$name", $liste)) {
					imap_createmailbox($mbox, imap_utf7_encode("{".$server."}$name"));
					imap_subscribe($mbox,imap_utf7_encode("{".$server."}$name"));
				}
				
				$total=imap_num_msg($mbox);
				$pp=-1;
				for ($i=1; $i<=$total; $i++) {
					$header=imap_fetchheader($mbox,$i);
					$email="";
					if (strpos($header,'delivery-status')!==false || strpos($header,'multipart/report')!==false || strpos($header,'X-Failed-Recipients')!==false) {
						if(strpos($header,'X-Failed-Recipients')!==false){
							$email=Imap::extractXFailedRecipient($header);
						} else {
							$first=imap_fetchbody($mbox,$i,'1.1');
							if (trim($first)=="") {
								$body=imap_fetchbody($mbox,$i,'1');
								$email=Imap::extractEmailsFromString($body);
							} else {
								$email=Imap::extractEmailsFromString($first);
							}
						}
					}
					if ($email!="") {
						$cas=Contacts::get_idcasquette_email($email);
						$cass=array_unique(array_merge($cass,$cas));
						foreach($cas as $cas_id){
							imap_mail_move ($mbox,$i,$name);
							$mail_err=Contacts::do_set_mail_erreur($cas_id,$email,$id);
							WS_maj($mail_err['maj']);
						}
					}
					$p=floor(100*$i/$total);
					if ($p!=$pp) {
						Imap::set_status($id,count($exps),$k,1,$p,count($cass));
						$pp=$p;
					}
				}
			}
		}
		sleep(5);
		Imap::set_status($id,count($exps),$k,2,100,count($cass));
	}
	public static function extractEmailsFromString($sChaine) {
		if(false !== preg_match_all('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $sChaine, $aEmails)) {
			if(is_array($aEmails[0]) && sizeof($aEmails[0])>0) {
				return $aEmails[0][0];
			}
		}
		return "";
	}
	public static function extractXFailedRecipient($sChaine) {
		$sChaine=substr($sChaine,strpos($sChaine,"X-Failed-Recipients"));
		if(false !== preg_match_all('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $sChaine, $aEmails)) {
			if(is_array($aEmails[0]) && sizeof($aEmails[0])>0) {
				return $aEmails[0][0];
			}
		}
		return "";
	}
	public static function get_status(){
		if(!file_exists("./data/files/traitements/imap")) $status=array('running'=>0);
		else $status=json_decode(file_get_contents("./data/files/traitements/imap"));
		return $status;
	}
	public static function get_imaps(){
		$imaps=array();
		foreach(glob('./data/files/traitements/historique/imap-*') as $f) {
			$imaps[]=json_decode(file_get_contents($f));
		}
		return $imaps;
	}
	public static function set_status($id,$nbb,$ib,$r,$p,$c){
		$status=array('by'=>$id,'nb_boites'=>$nbb,'index_boite'=>$ib,'running'=>$r, 'pourcentage'=>$p, 'nb'=>$c);
		file_put_contents("./data/files/traitements/imap",json_encode($status));
		$t=millisecondes();
		if ($r==2) {
			$imap=array('by'=>$id,'date'=>$t, 'nb'=>$c);
			file_put_contents("./data/files/traitements/historique/imap-$t",json_encode($imap));
		}
		WS_maj(array("imap"));
	}
}

