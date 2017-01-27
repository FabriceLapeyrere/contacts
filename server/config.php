<?php
class Config {
        // class object constructor
		public function __construct()
		{
			if (!file_exists('./data/db')) mkdir('./data/db', 0777, true);
			if (!file_exists('./data/tmp')) mkdir('./data/tmp', 0777, true);
			// file location for the user database
			$file = "./data/db/config.json";

			// do we need to build a new database?
			$rebuild = false;
			if(!file_exists($file)) { $rebuild = true; }

			// bind the database handler
			$this->file = $file;

			// If we need to rebuild, the file will have been automatically made by the PDO call,
			// but we'll still need to define the user table before we can use the database.
            $this->base_config=array(
                'app'=>array(
                    'brand'=>array('value'=>'','label'=>'Nom de l\'application','type'=>'texte_court'),
                    'url'=>array('value'=>'','label'=>'Url de l\'application','type'=>'texte_court'),
                    'mails_notification'=>array('value'=>'','label'=>'E-mails qui doivent recevoir les notifications (séparés par des virgules)','type'=>'texte_court'),
                    'mails_notification_from'=>array('value'=>'','label'=>'Expéditeur des notifications','type'=>'texte_court')
                ),
                'mailing'=>array(
                    'nbmail'=>array('value'=>100,'label'=>'Nombre de mail par tranche','type'=>'integer'),
                    't_pause'=>array('value'=>30,'label'=>'Durée de la pause en secondes','type'=>'integer'),
                    'use_redirect'=>array('value'=>0,'label'=>'Activer / Désactiver la redirection des liens','type'=>'bool'),
                    'expediteurs'=>array('value'=>array(
                        array(
                            'nom'=>array('value'=>'','label'=>'Nom','type'=>'texte_court'),
                            'email'=>array('value'=>'','label'=>'E-mail','type'=>'email'),
                            'smtp_host'=>array('value'=>'','label'=>'Serveur SMTP','type'=>'texte_court'),
                            'smtp_port'=>array('value'=>25,'label'=>'Port IMAP','type'=>'integer'),
                            'smtp_auth'=>array('value'=>1,'label'=>'Active / désactive l\'authentification SMTP','type'=>'bool'),
                            'smtp_username'=>array('value'=>'','label'=>'Utilisateur SMTP','type'=>'texte_court','show'=>'smtp_auth'),
                            'smtp_pwd'=>array('value'=>'','label'=>'Mot de passe SMTP','type'=>'passwd','show'=>'smtp_auth'),
                            'imap_check'=>array('value'=>0,'label'=>'Activer / désactiver la vérification des messages d\'erreur','type'=>'bool'),
                            'imap_host'=>array('value'=>'','label'=>'Serveur IMAP','type'=>'texte_court','show'=>'imap_check'),
                            'imap_port'=>array('value'=>143,'label'=>'Port IMAP','type'=>'integer','show'=>'imap_check'),
                            'imap_username'=>array('value'=>'','label'=>'Utilisateur IMAP','type'=>'texte_court','show'=>'imap_check'),
                            'imap_pwd'=>array('value'=>'','label'=>'Mot de passe IMAP','type'=>'passwd','show'=>'imap_check')
                        )
                    ),'label'=>'Expéditeurs','type'=>'array')
                ),
                'ldap'=>array(  
                    'active'=>array('value'=>0,'label'=>'Active / désactive la synchronisation LDAP','type'=>'bool'),
                    'srv'=>array('value'=>'','label'=>'Serveur LDAP','type'=>'texte_court','show'=>'active'),
                    'rdn'=>array('value'=>'','label'=>'Utilisateur LDAP','type'=>'texte_court','show'=>'active'),
                    'pwd'=>array('value'=>'','label'=>'Mot de passe','type'=>'passwd','show'=>'active'),
                    'base'=>array('value'=>'','label'=>'OU de base','type'=>'texte_court','show'=>'active')
                ),
                'news'=>array(
                    'wrapper'=>array('value'=>"<div style='margin: 0 auto;width:700px;font-family: verdana, sans-serif;font-size:12px;text-align:left;background:#fff;'>::code::</div>",'label'=>'Code du conteneur de newsletter','type'=>'texte_long'),
                    'css'=>array('value'=>"",'label'=>'CSS','type'=>'texte_long')
                )
            );
			if($rebuild) { $this->rebuild_config(); }
		}

		// this public function rebuilds the database if there is no database to work with yet
		public function rebuild_config()
		{
            file_put_contents($this->file,json_encode($this->base_config));
		}
        public function get_default(){
            return $this->base_config;
        }
        public function get_config(){
            $current=json_decode(file_get_contents($this->file));
            $old=$current;
            $base=$this->base_config;
            foreach($base as $key=>$tab){
				$j=0;                                
                foreach($tab as $k=>$v){
                    if ($v['type']!='array'){
                        if(isset($current->$key->$k)) {
                            $current->$key->$k->label=$v['label'];
                            $current->$key->$k->type=$v['type'];
                            if (isset($v['show'])) $current->$key->$k->show=$v['show'];
                            else unset($current->$key->$k->show);
                        } else {
                            $current->$key->$k=$v;
                        }
                    } else {
                        if(isset($current->$key->$k) && $current->$key->$k->type=='array') {
                            foreach($v['value'] as $t){
                                $i=0;
                                foreach($t as $tk=>$tv){
                                    foreach($current->$key->$k->value as $ic=>$tc){
										if(isset($current->$key->$k->value[$ic]->$tk)){
                                            $current->$key->$k->value[$ic]->$tk->id=$ic;
                                            $current->$key->$k->value[$ic]->$tk->num=$i;
                                            $current->$key->$k->value[$ic]->$tk->label=$tv['label'];
                                            $current->$key->$k->value[$ic]->$tk->type=$tv['type'];
                                            if (isset($tv['show'])) $current->$key->$k->value[$ic]->$tk->show=$tv['show'];
                                            else unset($current->$key->$k->value[$ic]->$tk->show);
                                        } else {
                                            $tv['num']=$i;
                                            $current->$key->$k->value[$ic]->$tk=$tv;
                                        }
                                    }
                                    $i++; 
                                }
                            }
                        } else {
                            $current->$key->$k=$v;
                        }
					    $current->$key->$k->num=$j;                        
                    }
					$j++;
                }
            }
            foreach($current as $key=>$tab){
                foreach($tab as $k=>$v){
                    if(!isset($base[$key][$k])) unset($current->$key->$k);
                    else {
                        if($current->$key->$k->type=='array') {
                            foreach($current->$key->$k->value as $ic=>$tc){
                                foreach($tc as $tk=>$tv) {
                                    if (!isset($base[$key][$k]['value'][0][$tk])) unset($current->$key->$k->value[$ic]->$tk);
                                }
                            }
                        }
                    }
                }
            }
            if($old!=$current) CR::maj(array('config'));
            $config['config']=$current;
            $config['base_config']=$base;
            $config['verrou']=WS::get_verrou('config');
            return $config;
        }
        public function get(){
            return json_decode(file_get_contents($this->file));
        }
        public function set_config($config){
            file_put_contents($this->file,json_encode($config));
            CR::maj(array('*'));
        }
}
