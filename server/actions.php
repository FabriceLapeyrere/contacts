<?php
	class Actions
	{
        //ADMIN
        public static function addUser($params){
            global $S;
	        if ($S['user']['id']==1) {
			        return User::create($params->login,$params->name,$params->pwd);
	        } else {
		        return 0;
	        }
        }
        public static function addUserGroup($params){
            global $S;
	        if ($S['user']['id']==1) {
			        return User::add_user_group($params->id_user,$params->id_group,$S['user']['id']);
	        } else {
		        return 0;
	        }
        }
        public static function delUserGroup($params){
            global $S;
	        if ($S['user']['id']==1) {
			        return User::del_user_group($params->id_user,$params->id_group,$S['user']['id']);
	        } else {
		        return 0;
	        }
        }
        public static function addGroup($params){
            global $S;
	        if ($S['user']['id']==1) {
			        return User::add_group($params->nom,$S['user']['id']);
	        } else {
		        return 0;
	        }
        }
        public static function delGroup($params){
            global $S;
	        if ($S['user']['id']==1) {
			        return User::del_group($params->id,$S['user']['id']);
	        } else {
		        return 0;
	        }
        }
        public static function modGroup($params){
            global $S;
	        if ($S['user']['id']==1) {
			        return User::mod_group($params->id,$params->nom,$S['user']['id']);
	        } else {
		        return 0;
	        }
        }
        public static function modUser($params){
            global $S;
	        if (isset($S['user']) && ($S['user']['id']==1 || $S['user']['id']==$params->id)) {
		        $pwd=isset($params->pwd) ? $params->pwd : '';
		        $u=User::update($params->id,$params->login,$params->name,$pwd);
		        if ($S['user']['id']==$params->id) {
			        $S['user']=$u;
		        }
		        return $u;
	        } else {
		        return 0;
	        }
        }
        public static function delUser($params){
            global $S;
            if (isset($S['user']) && $S['user']['id']==1) {
		        User::del($params->id, $S['user']['id']);
	        } else {
		        return 0;
	        }
        }
        public static function modPanier($params){
	        global $S;
	        return User::mod_prefs($params,$S['user']['id']);
        }
        public static function addPanier($params){
	        global $S;
	        return User::add_panier($params,$S['user']['id']);
        }
        public static function panierAll($params){
	        global $S;
	        return User::panier_all($params,$S['user']['id']);
        }
        public static function delPanier($params){
	        global $S;
	        return User::del_panier($params,$S['user']['id']);
        }
        public static function setConfig($params){
            global $S,$config;
         	if (isset($S['user']) && $S['user']['id']==1) {
		        $config->set_config($params->config);
	        } else {
		        return 0;
	        }
        }
        public static function addAcl($params){
	        global $S;
         	User::add_acl($params->type_ressource,$params->id_ressource,$params->type_acces,$params->id_acces,$params->level,$S['user']['id']);
        }
        public static function delAcl($params){
	        global $S;
         	User::del_acl($params->type_ressource,$params->id_ressource,$params->type_acces,$params->id_acces,$S['user']['id']);
        }
        //CHAT
        public static function sendMessage($params){
	        global $S;
            if ($params->id_from == $S['user']['id']) {
	            return Chat::send_message($params);
            }
        }
        public static function modMessage($params){
	        global $S;
            if ($params->message->id_from == $S['user']['id']) {
	            return Chat::mod_message($params);
            }
        }
        public static function setLus($params){
	        global $S;
            if ($params->id_user == $S['user']['id']) {
	            return Chat::set_lus($params);
            }
        }
        //CONTACTS
        public static function addContact($params){
	        global $S;
	        return Contacts::add_contact($params,$S['user']['id']);
        }
        public static function modContact($params){
	        global $S;
	        return Contacts::mod_contact($params,$S['user']['id']);
        }
        public static function delContact($params){
	        global $S;
	        return Contacts::del_contact($params,$S['user']['id']);
        }
        public static function modCasquette($params){
	        global $S;
	        return Contacts::mod_casquette($params,$S['user']['id']);
        }
        public static function addCasquette($params){
	        global $S;
	        return Contacts::add_casquette($params,$S['user']['id']);
        }
        public static function delCasquette($params){
	        global $S;
	        return Contacts::del_casquette($params,$S['user']['id']);
        }
        public static function addCasTag($params){
	        global $S;
	        return Contacts::add_cas_tag($params,$S['user']['id']);	
        }
        public static function addPanierTag($params){
	        global $S;
	        return Contacts::add_panier_tag($params,$S['user']['id']);	
        }
        public static function delPanierTag($params){
	        global $S;
	        return Contacts::del_panier_tag($params,$S['user']['id']);	
        }
        public static function delCasTag($params){
	        global $S;
	        return Contacts::del_cas_tag($params,$S['user']['id']);	
        }
        public static function movTag($params){
	        global $S;
	        return Contacts::move_tag($params,$S['user']['id']);	
        }
        public static function modTag($params){
	        global $S;
	        return Contacts::mod_tag($params,$S['user']['id']);	
        }
        public static function addTag($params){
	        global $S;
	        return Contacts::add_tag($params,$S['user']['id']);	
        }
        public static function delTag($params){
	        global $S;
	        return Contacts::del_tag($params,$S['user']['id']);	
        }
        public static function modSelection($params){
	        global $S;
	        return Contacts::mod_selection($params,$S['user']['id']);	
        }
        public static function addSelection($params){
	        global $S;
	        return Contacts::add_selection($params,$S['user']['id']);	
        }
        public static function delSelection($params){
	        global $S;
	        return Contacts::del_selection($params,$S['user']['id']);	
        }
        public static function nbSelection($params){
	        global $S;
	        return Contacts::nb_selection($params,$S['user']['id']);	
        }
        public static function addNbContacts($params){
	        global $S;
            return Contacts::add_nb_contacts($params,$S['user']['id']);	
        }
        public static function addNbCsv($params){
	        global $S;
            return Contacts::add_nb_csv($params,$S['user']['id']);	
        }
        //MAILING
        public static function addMail($params){
            global $S;
            return Mailing::add_mail($params,$S['user']['id']);
        }
        public static function delMail($params){
            global $S;
            return Mailing::del_mail($params,$S['user']['id']);
        }
        public static function modMail($params){
            global $S;
            return Mailing::mod_mail($params,$S['user']['id']);
        }
        public static function addNews($params){
            global $S;
            return Mailing::add_news($params,$S['user']['id']);
        }
        public static function modNews($params){
            global $S;
            return Mailing::mod_news($params,$S['user']['id']);
        }
        public static function delNews($params){
            global $S;
            return Mailing::del_news($params,$S['user']['id']);
        }
        public static function addModele($params){
            global $S;
            return Mailing::add_modele($params,$S['user']['id']);
        }
        public static function modModele($params){
            global $S;
            return Mailing::mod_modele($params,$S['user']['id']);
        }
        public static function modNomCat($params){
            global $S;
            return Mailing::mod_nom_cat_modele($params,$S['user']['id']);
        }
        public static function delNewsPj($params){
            global $S;
            return Mailing::del_news_pj($params,$S['user']['id']);
        }
        public static function delMailPj($params){
            global $S;
            return Mailing::del_mail_pj($params,$S['user']['id']);
        }
        public static function envoyer($params){
            global $S;
            return Mailing::envoyer($params,$S['user']['id']);
        }
        public static function playEnvoi($params){
            global $S;
            $id_envoi=$params->id;
            return Mailing::start_envoi($id_envoi,$S['user']['id']);
        }
        public static function pauseEnvoi($params){
            global $S;
            $id_envoi=$params->id;
            return Mailing::stop_envoi($id_envoi,$S['user']['id']);
        }
        public static function restartEnvoi($params){
            global $S;
            $id_envoi=$params->id;
            return Mailing::restart_envoi($id_envoi,$S['user']['id']);
        }
        public static function videEnvoi($params){
            global $S;
            $id_envoi=$params->id;
            return Mailing::vide_envoi($id_envoi,$S['user']['id']);
        }
        //PUBLIPOSTAGE
        public static function addSupport($params){
	        global $S;
            return Publipostage::add_support($params,$S['user']['id']);
        }
        public static function modSupport($params){
	        global $S;
            return Publipostage::mod_support($params,$S['user']['id']);
        }
        public static function delSupport($params){
	        global $S;
            return Publipostage::del_support($params,$S['user']['id']);
        }
        //SUIVIS
        public static function addSuivi($params){
	        global $S;
            return Suivis::add_suivi($params,$S['user']['id']);
        }
        public static function modSuivi($params){
	        global $S;
            return Suivis::mod_suivi($params,$S['user']['id']);
        }
        public static function delSuivi($params){
	        global $S;
            return Suivis::del_suivi($params,$S['user']['id']);
        }
        //TRAITEMENTS
        public static function checkImap($params){
            global $S;
	        return IMAP::start_check($S['user']['id']);
        }
    }
