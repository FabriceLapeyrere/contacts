<?php
class Config {
		protected $WS;
		protected $from;
		// class object constructor
		const FILE = "../data/db/config.json";
		public function __construct($WS,$from)
		{
		 	$this->WS= $WS;
	 	 	$this->from= $from;
			if (!file_exists('../data/db')) mkdir('../data/db', 0777, true);
			if (!file_exists('../data/tmp')) mkdir('../data/tmp', 0777, true);
			// file location for the user database

			// do we need to build a new database?
			$rebuild = false;
			if(!file_exists(Config::FILE)) { $rebuild = true; }

			// If we need to rebuild, the file will have been automatically made by the PDO call,
			// but we'll still need to define the user table before we can use the database.
			$this->base_config=array(
				'app'=>array(
					'brand'=>array('value'=>'','label'=>'Nom de l\'application','type'=>'texte_court'),
					'url'=>array('value'=>'','label'=>'Url de l\'application','type'=>'texte_court'),
					'mails_notification'=>array('value'=>'','label'=>'E-mails qui doivent recevoir les notifications (séparés par des virgules)','type'=>'texte_court'),
					'mails_notification_from'=>array('value'=>'','label'=>'Expéditeur des notifications','type'=>'texte_court'),
					'champs_personnalises'=>array('value'=>array(
						array(
							'label'=>array('value'=>'','label'=>'Label','type'=>'texte_court'),
							'type'=>array('value'=>'','label'=>'Type','type'=>'liste','choices'=>array(
									array('label'=>'Texte long','value'=>'note'),
									array('label'=>'Texte court','value'=>'text'),
									array('label'=>'Téléphone','value'=>'tel'),
									array('label'=>'E-mail','value'=>'email'),
								),
							)
						)
					),'label'=>'Champs personnalisés','type'=>'array')
				),
				'mailing'=>array(
					'nbmail'=>array('value'=>100,'label'=>'Nombre de mail par tranche','type'=>'integer'),
					't_pause'=>array('value'=>30,'label'=>'Durée de la pause en secondes','type'=>'integer'),
					'use_redirect'=>array('value'=>0,'label'=>'Activer / Désactiver la redirection des liens','type'=>'bool'),
					'remote_imgs'=>array('value'=>0,'label'=>'Activer / Désactiver les images distantes dans les news','type'=>'bool'),
					'redirect_notification'=>array('value'=>0,'label'=>'Envoyer les notifications de clic à l\'expéditeur ?','type'=>'bool','show'=>'use_redirect'),
					'expediteurs'=>array('value'=>array(
						array(
							'nom'=>array('value'=>'','label'=>'Nom','type'=>'texte_court'),
							'email'=>array('value'=>'','label'=>'E-mail','type'=>'email'),
							'smtp_host'=>array('value'=>'','label'=>'Serveur SMTP','type'=>'texte_court'),
							'smtp_port'=>array('value'=>25,'label'=>'Port SMTP','type'=>'integer'),
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
					'base'=>array('value'=>'','label'=>'OU de base pour tous les contacts','type'=>'texte_court','show'=>'active'),
					'tags'=>array('value'=>array(
						array(
							'idtag'=>array('value'=>'','label'=>'N° du tag','type'=>'integer'),
							'base'=>array('value'=>'','label'=>'OU de base','type'=>'texte_court')
						)
					),'label'=>'OU de base par tag','type'=>'array')
			   ),
				'news'=>array(
					'newsletters'=>array('value'=>array(
						array(
							'nom'=>array('value'=>'','label'=>'Nom','type'=>'texte_court'),
							'id_tag'=>array('value'=>'','label'=>'N° du tag','type'=>'integer'),
							'html'=>array('value'=>'','label'=>'Entête pour la newsletter en ligne','type'=>'texte_long'),
							'wrapper'=>array('value'=>"",'label'=>'Code du conteneur de newsletter','type'=>'texte_long')
						)
					),'label'=>'Newsletters','type'=>'array'),
					'main_wrapper'=>array('value'=>"<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
	<title>::sujet::</title>
	<style>::css::</style>
</head>
<body>
	<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
		<tr>
			<td height=\"1\" class=\"hide\" style=\"min-width:600px; font-size:0px;line-height:0px;\">
				<img height=\"1\" src=\"img/spacer.gif\" style=\"min-width: 700px; text-decoration: none; border: none; -ms-interpolation-mode: bicubic;\"/>
			</td>
		</tr>
		<!-- Close spacer Gmail Android -->
		<!-- iphone gmail fix -->
		<tr>
			<td>
				<div style=\"display:none; white-space:nowrap; font:15px courier; line-height:0;\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</div>
			</td>
		</tr>
		<tr>
			<td>
				::html::
			</td>
		</tr>
	</table>
</body>",'label'=>'Code du conteneur global de newsletter','type'=>'texte_long'),
					'wrapper'=>array('value'=>"<table id=\"::nBloc::\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">
	<tr>
		<td align=\"center\">
			<table width=\"700\" cellspacing=\"0\" cellpadding=\"0\">
				<tr>
					<td>
						::code::
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>",'label'=>'Code du conteneur de newsletter','type'=>'texte_long'),
					'css'=>array('value'=>"",'label'=>'CSS','type'=>'texte_long')
				),
				'email'=>array(
					'main_wrapper'=>array('value'=>"<head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
	<title>::sujet::</title>
	<style>::css::</style>
</head>
<body>
	::html::
</body>",'label'=>'Code du conteneur global de mail','type'=>'texte_long'),
					'css'=>array('value'=>"",'label'=>'CSS','type'=>'texte_long')
				),
				'carte'=>array(
					'mapbox_accessToken'=>array('value'=>"",'label'=>'Mapbox Access Token','type'=>'texte_court')
				)
			);
			if($rebuild) { $this->rebuild_config(); }
		}

		// this public function rebuilds the database if there is no database to work with yet
		public function rebuild_config()
		{
		   		file_put_contents(Config::FILE,json_encode($this->base_config));
		}
		public function get_default(){
			return $this->base_config;
		}
		public function get_config(){
			$old=json_decode(file_get_contents(Config::FILE));
			$current=json_decode(file_get_contents(Config::FILE));
			$base=$this->base_config;
			foreach($base as $key=>$tab){
				if (!isset($current->$key)) $current->$key=json_decode(json_encode($tab));
				$j=0;
				foreach($tab as $k=>$v){
					if(!isset($current->$key->$k)) $current->$key->$k=json_decode(json_encode($v));
					if ($v['type']!='array'){
						$current->$key->$k->label=$v['label'];
						$current->$key->$k->type=$v['type'];
						if (isset($v['choices'])) $current->$key->$k->show=$v['choices'];
						if (isset($v['show'])) $current->$key->$k->show=$v['show'];
						else unset($current->$key->$k->show);
						$current->$key->$k->num=$j;
					} else {
						foreach($v['value'] as $t){
							$i=0;
							foreach($t as $tk=>$tv){
								foreach($current->$key->$k->value as $ic=>$tc){
									if(isset($current->$key->$k->value[$ic]->$tk)){
										$current->$key->$k->value[$ic]->$tk->id=$ic;
										$current->$key->$k->value[$ic]->$tk->num=$i;
										$current->$key->$k->value[$ic]->$tk->label=$tv['label'];
										$current->$key->$k->value[$ic]->$tk->type=$tv['type'];
										if (isset($tv['choices'])) $current->$key->$k->value[$ic]->$tk->show=$tv['choices'];
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
						$current->$key->$k->num=$j;
					}
					$j++;
				}
			}
			foreach($current as $key=>$tab){
				if(!isset($base[$key])) {
					unset($current->$key);
				} else {
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
			}

			if($old!=$current) {
				file_put_contents(Config::FILE,json_encode($current));
			}
			$config['config']=$current;
			$config['base_config']=$base;
			return $config;
		}
		public static function get(){
			return json_decode(file_get_contents(Config::FILE));
		}
		public function set_config($params,$id) {
			$t=Config::do_set_config($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public function do_set_config($config,$id){
			file_put_contents(Config::FILE,json_encode($config));
			return array('maj'=>array('*'), 'res'=>1);
		}
}
