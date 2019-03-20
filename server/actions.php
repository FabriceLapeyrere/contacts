<?php
	class Actions
	{
		protected $WS;
		protected $from;
		protected $User;
		protected $Contacts;
		protected $Suivis;
		protected $Mailing;
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
			$this->Publipostage= new Publipostage($this->WS,$this->from);
			$this->Forms= new Forms($this->WS,$this->from);
	 		$this->Chat= new Chat($this->WS,$this->from);
	 		$this->Config= new Config($this->WS,$this->from);
	 	}
		//Login/Logout
		public function login($params){
			$login=$params->login;
			$password=$params->password;
			$u=User::check($login,$password);
			if (count($u)>0){
				$this->WS->setSession($this->from,'user',array(
					'login'=>$u['login'],
					'name'=>$u['name'],
					'id'=>$u['id']
				));
			}
			$this->WS->maj(array('logged'));
		}
		public function logout($params){
			$this->WS->removeSession($this->from,'user');
			$this->WS->maj(array('logged'));
		}
		//ADMIN
		public function addUser($params){
			$u=$this->WS->getSession($this->from,'user');
			if ($u['id']==1) {
					return $this->User->create($params->login,$params->name,$params->pwd);
			} else {
				return 0;
			}
		}
		public function addUserGroup($params){
			$u=$this->WS->getSession($this->from,'user');
			if ($u['id']==1) {
					return $this->User->add_user_group($params->id_user,$params->id_group,$u['id']);
			} else {
				return 0;
			}
		}
		public function delUserGroup($params){
			$u=$this->WS->getSession($this->from,'user');
			if ($u['id']==1) {
					return $this->User->del_user_group($params->id_user,$params->id_group,$u['id']);
			} else {
				return 0;
			}
		}
		public function addGroup($params){
			$u=$this->WS->getSession($this->from,'user');
			if ($u['id']==1) {
					return $this->User->add_group($params->nom,$u['id']);
			} else {
				return 0;
			}
		}
		public function delGroup($params){
			$u=$this->WS->getSession($this->from,'user');
			if ($u['id']==1) {
					return $this->User->del_group($params->id,$u['id']);
			} else {
				return 0;
			}
		}
		public function modGroup($params){
			$u=$this->WS->getSession($this->from,'user');
			if ($u['id']==1) {
					return $this->User->mod_group($params->id,$params->nom,$u['id']);
			} else {
				return 0;
			}
		}
		public function modUser($params){
			$u=$this->WS->getSession($this->from,'user');
			if (isset($u) && ($u['id']==1 || $u['id']==$params->id)) {
				$pwd=isset($params->pwd) ? $params->pwd : '';
				$user=$this->User->update($params->id,$params->login,$params->name,$pwd);
				return $user;
			} else {
				return 0;
			}
		}
		public function delUser($params){
			$u=$this->WS->getSession($this->from,'user');
			if (isset($u) && $u['id']==1) {
				$this->User->del($params->id, $u['id']);
			} else {
				return 0;
			}
		}
		public function modPanier($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->User->mod_prefs($params,$u['id']);
		}
		public function addPanier($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->User->add_panier($params,$u['id']);
		}
		public function panierAll($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->User->panier_all($params,$u['id']);
		}
		public function delPanier($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->User->del_panier($params,$u['id']);
		}
		public function setConfig($params){
			$u=$this->WS->getSession($this->from,'user');
			if (isset($u) && $u['id']==1) {
				$this->Config->set_config($params->config,$u['id']);
			} else {
				return 0;
			}
		}
		public function addAcl($params){
			$u=$this->WS->getSession($this->from,'user');
		 	$this->User->add_acl($params->type_ressource,$params->id_ressource,$params->type_acces,$params->id_acces,$params->level,$u['id']);
		}
		public function delAcl($params){
			$u=$this->WS->getSession($this->from,'user');
		 	$this->User->del_acl($params->type_ressource,$params->id_ressource,$params->type_acces,$params->id_acces,$u['id']);
		}
		//CHAT
		public function sendMessage($params){
			$u=$this->WS->getSession($this->from,'user');
			if ($params->id_from == $u['id']) {
				return $this->Chat->send_message($params,$u['id']);
			}
		}
		public function modMessage($params){
			$u=$this->WS->getSession($this->from,'user');
			if ($params->message->id_from == $u['id']) {
				return $this->Chat->mod_message($params,$u['id']);
			}
		}
		public function setLus($params){
			$u=$this->WS->getSession($this->from,'user');
			if ($params->id_user == $u['id']) {
				return $this->Chat->set_lus($params,$u['id']);
			}
		}
		//CONTACTS
		public function addContact($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->add_contact($params,$u['id']);
		}
		public function modContact($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->mod_contact($params,$u['id']);
		}
		public function delContact($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->del_contact($params,$u['id']);
		}
		public function delCasquettesPanier($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->del_casquettes_panier($params,$u['id']);
		}
		public function unErrorEmailPanier($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->un_error_email_panier($params,$u['id']);
		}
		public function modCasquette($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->mod_casquette($params,$u['id']);
		}
		public function assCasquette($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->ass_casquette($params,$u['id']);
		}
		public function desAssEtablissement($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->des_ass_etablissement($params,$u['id']);
		}
		public function moveCasquette($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->move_casquette($params,$u['id']);
		}
		public function mergeCasquette($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->merge_casquette($params,$u['id']);
		}
		public function addCasquette($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->add_casquette($params,$u['id']);
		}
		public function delCasquette($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->del_casquette($params,$u['id']);
		}
		public function delEmailCasquette($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->del_email_casquette($params,$u['id']);
		}
		public function addCasTag($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->add_cas_tag($params,$u['id']);
		}
		public function addPanierTag($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->add_panier_tag($params,$u['id']);
		}
		public function delPanierTag($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->del_panier_tag($params,$u['id']);
		}
		public function delCasTag($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->del_cas_tag($params,$u['id']);
		}
		public function movTag($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->move_tag($params,$u['id']);
		}
		public function modTag($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->mod_tag($params,$u['id']);
		}
		public function addTag($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->add_tag($params,$u['id']);
		}
		public function delTag($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->del_tag($params,$u['id']);
		}
		public function modSelection($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->mod_selection($params,$u['id']);
		}
		public function addSelection($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->add_selection($params,$u['id']);
		}
		public function delSelection($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->del_selection($params,$u['id']);
		}
		public function nbSelection($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->nb_selection($params,$u['id']);
		}
		public function addNbContacts($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->add_nb_contacts($params,$u['id']);
		}
		public function addNbCsv($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->add_nb_csv($params,$u['id']);
		}
		public function nonDoublonTexte($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Contacts->non_doublon_texte($params,$u['id']);
		}
		//MAILING
		public function addMail($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->add_mail($params,$u['id']);
		}
		public function delMail($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->del_mail($params,$u['id']);
		}
		public function modMail($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->mod_mail($params,$u['id']);
		}
		public function addNews($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->add_news($params,$u['id']);
		}
		public function modNews($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->mod_news($params,$u['id']);
		}
		public function delNews($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->del_news($params,$u['id']);
		}
		public function dupNews($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->dup_news($params,$u['id']);
		}
		public function addModele($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->add_modele($params,$u['id']);
		}
		public function modModele($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->mod_modele($params,$u['id']);
		}
		public function delModele($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->del_modele($params,$u['id']);
		}
		public function modNomCat($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->mod_nom_cat_modele($params,$u['id']);
		}
		public function delNewsPj($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->del_news_pj($params,$u['id']);
		}
		public function delMailPj($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->del_mail_pj($params,$u['id']);
		}
		public function envoyer($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->envoyer($params,$u['id']);
		}
		public function playEnvoi($params){
			$u=$this->WS->getSession($this->from,'user');
			$id_envoi=$params->id;
			return Mailing::start_envoi($id_envoi,$u['id']);
		}
		public function pauseEnvoi($params){
			$u=$this->WS->getSession($this->from,'user');
			$id_envoi=$params->id;
			return $this->Mailing->stop_envoi($id_envoi,$u['id']);
		}
		public function restartEnvoi($params){
			$u=$this->WS->getSession($this->from,'user');
			$id_envoi=$params->id;
			return $this->Mailing->restart_envoi($id_envoi,$u['id']);
		}
		public function videEnvoi($params){
			$u=$this->WS->getSession($this->from,'user');
			$id_envoi=$params->id;
			return $this->Mailing->vide_envoi($id_envoi,$u['id']);
		}
		public function addScheduleEnvoi($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->add_schedule($params,$u['id']);
		}
		public function delScheduleEnvoi($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->del_schedule($params,$u['id']);
		}
		public function modScheduleEnvoi($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Mailing->mod_schedule($params,$u['id']);
		}
		//PUBLIPOSTAGE
		public function addSupport($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Publipostage->add_support($params,$u['id']);
		}
		public function modSupport($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Publipostage->mod_support($params,$u['id']);
		}
		public function delSupport($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Publipostage->del_support($params,$u['id']);
		}
		public function addTemplate($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Publipostage->add_template($params,$u['id']);
		}
		public function modTemplate($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Publipostage->mod_template($params,$u['id']);
		}
		public function delTemplate($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Publipostage->del_template($params,$u['id']);
		}
		public function delTpl($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Publipostage->del_tpl($params,$u['id']);
		}
		//SUIVIS
		public function addSuivisThread($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Suivis->add_suivis_thread($params,$u['id']);
		}
		public function modSuivisThread($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Suivis->mod_suivis_thread($params,$u['id']);
		}
		public function delSuivisThread($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Suivis->del_suivis_thread($params,$u['id']);
		}
		public function addSuivi($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Suivis->add_suivi($params,$u['id']);
		}
		public function modSuivi($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Suivis->mod_suivi($params,$u['id']);
		}
		public function delSuivi($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Suivis->del_suivi($params,$u['id']);
		}
		//TRAITEMENTS
		public function checkImap($params){
			$u=$this->WS->getSession($this->from,'user');
			return Imap::start_check($u['id']);
		}
		//FORMS
		public function addForm($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Forms->add_form($params,$u['id']);
		}
		public function modForm($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Forms->mod_form($params,$u['id']);
		}
		public function delForm($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Forms->del_form($params,$u['id']);
		}
		public function addFormCas($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Forms->add_form_cas($params,$u['id']);
		}
		public function delFormCas($params){
			$u=$this->WS->getSession($this->from,'user');
			return $this->Forms->del_form_cas($params,$u['id']);
		}

	}
