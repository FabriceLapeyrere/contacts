<?php
	class CR
	{
		public static function get_context($context) {
			global $S,$config;
			$type=$context->type;
			$params= isset($context->params) ? $context->params : new stdClass();
			$res=array();
			$c=WS::get_cache($context);
			if ($c!==false) {
				$res=$c;
				error_log("from cache\n",3,"./data/log/link.log");
			}
			else {
				$tab=explode('/',$type);
				switch ($tab[0]) {
					case "logged":
						$subs=WS::get_subs();
						$logged=array();
						$logged_ids=array();
						foreach($subs as $uid=>$sub) {
							$logged[$uid]=$sub->user;
							if(!in_array($sub->user->id,$logged_ids)) $logged_ids[]=$sub->user->id;
						}
						$res=array('byUid'=>$logged, 'ids'=>$logged_ids);
						break;
					case "user":
						$res=User::get_user($S['user']['id']);
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
						$res=Contacts::get_casquettes($params,0,$S['user']['id']);
						break;
					case "casquettes_sel":
						$res=Contacts::get_casquettes($params,0,$S['user']['id']);
						break;
					case "casquettes_mail_erreur":
						$params->query='::mailerreur::';
						$res=Contacts::get_casquettes($params,0,$S['user']['id']);
						break;
					case "etabs":
						$params->query.=' AND ::type/2::';
						$res=Contacts::get_casquettes($params);
						break;
					case "contact":
						$res=Contacts::get_contact($tab[1],true,$S['user']['id']);
						break;
					case "suivis":
						$res=Suivis::get_suivis($S['user']['id']);
						break;
					case "suivi":
						$res=Suivis::get_suivi($tab[1],$S['user']['id']);
						break;
					case "panier":
						$res=User::get_panier($S['user']['id']);
						break;
					case "envois":
						$res=Mailing::get_envois($S['user']['id']);
						break;
					case "envoi":
						$res=Mailing::get_envoi($tab[1],$params,$S['user']['id']);
						break;
					case "imap":
						$res=Imap::get_status();
						break;
					case "config":
						$res=$config->get_config();
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
					case "mails":
						$res=Mailing::get_mails();
						break;
					case "mail":
						$res=Mailing::get_mail($tab[1]);
						break;
					case "newss":
						$res=Mailing::get_newss(0,$S['user']['id']);
						break;
					case "news":
						$res=Mailing::get_news($tab[1],$S['user']['id']);
						break;
					case "modeles":
						$res=Mailing::get_modeles();
						break;
					case "modele":
						$res=Mailing::get_modele($tab[1]);
						break;
					case "chat":
						$res=Chat::get_chat($S['user']['id']);
						break;
				}
				WS::set_cache($context,$res);
				error_log("computed\n",3,"./data/log/link.log");
			}
			return $res;
		}
		public static function context_verrou($type){
			$tab=explode('/',$type);
			error_log("context verrou $type\n",3,"./data/log/link.log");
			switch ($tab[0]) {
				case "casquette":
					return 'contact/'.Contacts::get_contact_casquette($tab[1]);
				case "tag":
					return 'tags';
				case "user":
					return 'users';
				case "group":
					return 'groups';
				case "selection":
					return 'selections';
				case "newsbloc":
					return 'news/'.$tab[1];
				default:
					return $type;
			}
		}
		public static function deps($type){
			global $S;
			$tab=explode('/',$type);
			$res=array();
			switch ($tab[0]) {
				case 'contact':
					$res=array($type,'casquettes','casquettes_mail_erreur','casquettes_sel','etabs','suivis');
					$etabs=Contacts::get_etabs_contact($tab[1]);
					foreach($etabs as $id_etab) {
						$res[]='contact/'.$id_etab;
					}
					$cols=Contacts::get_cols_etab($tab[1]);
					foreach($cols as $id_col) {
						$res[]='contact/'.$id_col;
					}
					break;
				case 'user':
					$res=array($type,'panier');
					$res[]='users';
					$res[]='usersall';
					break;
				case 'users':
					$res=array($type,'usersall');
					break;
				case 'casquettes':
					$res=array($type,'etabs');
					break;
				case 'suivi':
					$res=array($type,'suivis');
					break;
				case 'modele':
					$res=array($type,'modeles');
					break;
				case 'support':
					$res=array($type,'supports');
					break;
				case 'mail':
					$res=array($type,'mails');
					break;
				case 'news':
					$res=array($type,'newss');
					break;
				case 'imap':
					$res=array($type);
					break;
				default:
					$res[]=$type;
					break;
			}
			return $res;
		}
		public static function maj($types){
			$res=array();
			foreach($types as $type){
				foreach(WS::get_sub_contexts($type) as $t){
					$res=array_merge($res,CR::deps($t));
				}
			}
			$res=array_unique($res);
			WS::del_cache($res);
			WS::prep_notify($res);
		}
	}
