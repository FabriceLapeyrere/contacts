<?php
	class CR
	{
		protected $WS;
		protected $from;
		protected $User;
		protected $Contacts;
		protected $Suivis;
		protected $Mailing;
		protected $Imap;
		protected $Publipostage;
		protected $Chat;
		protected $Config;
		public function __construct($WS,$from) {
	 	 	$this->WS= $WS;
	 	 	$this->from= $from;
			$this->User= new User($this->WS,$this->from);
	 		$this->Contacts= new Contacts($this->WS,$this->from);
	 		$this->Suivis= new Suivis($this->WS,$this->from);
	 		$this->Mailing= new Mailing($this->WS,$this->from);
	 		$this->Imap= new Imap($this->WS,$this->from);
	 		$this->Publipostage= new Publipostage($this->WS,$this->from);
	 		$this->Chat= new Chat($this->WS,$this->from);
	 		$this->Config= new Config($this->WS,$this->from);
	 	}
	 	public function get_context($context) {
			$uid=$this->from->resourceId;
			$u=$this->WS->getSession($this->from,'user');
			$type=$context->type;
			if (!isset($context->params)) $context->params=new stdClass();
			$params=$context->params;
			$res=array();
			$c=$this->WS->get_cache($context,$this->from);
			if ($c!==false) {
				$res=$c;
				error_log("from cache\n",3,"./data/log/link.log");
			} else {
				$tab=explode('/',$type);
				switch ($tab[0]) {
					case "logged":
						$subs=$this->WS->subs;
						$logged=array();
						$logged_ids=array();
						foreach($subs as $uid=>$sub) {
							$logged[$uid]=$sub->user;
							if(!in_array($sub->user->id,$logged_ids)) $logged_ids[]=$sub->user->id;
						}
						$res=array('byUid'=>$logged, 'ids'=>$logged_ids);
						break;
					case "verrous":
						$res=$this->WS->verrous;
						break;
					case "user":
						$res=User::get_user($u['id']);
						break;
					case "users":
						$res=User::get_users();
						break;
					case "usersall":
						$res=User::get_users_all();
						break;
					case "groups":
						$res=User::get_groups();
						break;
					case "tags":
						$res=Contacts::get_tags();
						break;
					case "selections":
						$res=Contacts::get_selections();
						break;
					case "casquettes":
						$res=Contacts::get_casquettes($params,0,$u['id']);
						break;
					case "carte":
						$res=Contacts::get_carte($params,$u['id']);
						foreach($res['geojson_clusters']->features as $k=>$c){
							if (count(explode(',',$c->properties->ids))>50) {
								$hash=md5($c->properties->ids);
								$this->WS->tmp[$hash]=$c->properties->ids;
								$res['geojson_clusters']->features[$k]->properties->ids="cache/$hash";
							}
						}
						break;
					case "cluster":
						$tmp_params=json_decode(json_encode($params));
						if (strpos($tmp_params->ids,"cache/")===0) {
							$tmp_params->ids=$this->WS->tmp[str_replace('cache/','',$tmp_params->ids)];
						}
						$res=Contacts::get_cluster($tmp_params,$u['id']);
						$res['params']=$params;
						break;
					case "check_nom":
						$res=Contacts::get_casquettes($params,0,$u['id']);
						break;
					case "casquettes_sel":
						$res=Contacts::get_casquettes($params,0,$u['id']);
						break;
					case "casquettes_mail_erreur":
						$params->query='::mailerreur::';
						$res=Contacts::get_casquettes($params,0,$u['id']);
						break;
					case "etabs":
						$res=Contacts::get_casquettes($params,0,$u['id']);
						break;
					case "contact":
						$res=Contacts::get_contact($tab[1],true,$u['id']);
						break;
					case "contact_prev_next":
						$res=Contacts::get_contact_prev_next($tab[1],$params,$u['id']);
						break;
					case "suivis":
						$res=Suivis::get_suivis($params,$u['id']);
						break;
					case "suivis_thread":
						$res=Suivis::get_suivis_thread($tab[1],$u['id']);
						break;
					case "suivi":
						$res=Suivis::get_suivi($tab[1],$u['id']);
						break;
					case "panier":
						$res=User::get_panier($u['id']);
						break;
					case "envois":
						$res=Mailing::get_envois($params,$u['id']);
						break;
					case "doublons_texte":
						$res=Contacts::get_doublons_texte($params,$u['id']);
						break;
					case "doublons_email":
						$res=Contacts::get_doublons_email($params,$u['id']);
						break;
					case "impacts":
						$res=Mailing::get_impacts($params,$u['id']);
						break;
					case "envoi":
						$res=Mailing::get_envoi($tab[1],$params,$u['id']);
						break;
					case "imap":
						$res=Imap::get_status();
						break;
					case "config":
						$res=$this->Config->get_config();
						break;
					case "log":
						$res=User::get_log();
						break;
					case "supports":
						$res=Publipostage::get_supports();
						break;
					case "support":
						$res=Publipostage::get_support($tab[1]);
						break;
					case "templates":
						$res=Publipostage::get_templates();
						break;
					case "template":
						$res=Publipostage::get_template($tab[1]);
						break;
					case "mails":
						$res=Mailing::get_mails();
						break;
					case "mail":
						$res=Mailing::get_mail($tab[1]);
						break;
					case "newss":
						$res=Mailing::get_newss($params,$u['id']);
						break;
					case "news":
						$res=Mailing::get_news($tab[1],$u['id']);
						break;
					case "modeles":
						$res=Mailing::get_modeles();
						break;
					case "modele":
						$res=Mailing::get_modele($tab[1]);
						break;
					case "chat":
						$res=Chat::get_chat($u['id']);
						break;
					case "forms":
						$res=Forms::get_forms($u['id']);
						break;
					case "form":
						$res=Forms::get_form($tab[1],$u['id']);
						break;
					case "form_casquettes":
						$res=Contacts::get_form_casquettes($tab[1],$params,$u['id']);
						break;
					case "form_instance":
						$res=Forms::get_form_instance($tab[1],$tab[2],$u['id']);
						break;
					}
				$this->WS->set_cache($context,$res,$this->from);
				error_log("computed\n",3,"./data/log/link.log");
			}
			return $res;
		}
	}
