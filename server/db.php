<?php
	class DB
	{
		// class object constructor
		function __construct($check=false)
		{
			if (!file_exists('./data/db')) mkdir('./data/db', 0777, true);
			// file location for the user database
			$dbfile = "./data/db/db.db";

			$this->schema = array(
				array(
					'type'=>'table',
					'nom'=>'trash',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'id_item','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'type','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'json','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'by','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'acl',
					'champs'=>array(
						array('nom'=>'id_ressource','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'type_ressource','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'id_acces','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'type_acces','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'level','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'unique index',
					'nom'=>'acl_idx',
					'on'=>'acl',
					'champs'=>array('id_ressource', 'type_ressource', 'id_acces', 'type_acces')
				),
				array(
					'type'=>'table',
					'nom'=>'contacts',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'sort','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'nom','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'prenom','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'type','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'casquettes',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'nom','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'donnees','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'id_contact','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_etab','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'emails','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'email_erreur','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'fonction','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'cp','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'gps_x','type'=>'REAL','defaut'=>'','options'=>''),
						array('nom'=>'gps_y','type'=>'REAL','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'fts table',
					'nom'=>'casquettes_fts',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'idx','type'=>'TEXT','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'tags',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'nom','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'type','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'options','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'color','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'id_parent','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'selections',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'nom','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'query','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'tag_cas',
					'champs'=>array(
						array('nom'=>'id_tag','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_cas','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'index',
					'nom'=>'contacts_idx',
					'on'=>'contacts',
					'champs'=>array('sort')
				),
				array(
					'type'=>'index',
					'nom'=>'casquettes_idx',
					'on'=>'casquettes',
					'champs'=>array('id_contact', 'id_etab', 'cp', 'email_erreur')
				),
				array(
					'type'=>'unique index',
					'nom'=>'tag_cas_idx',
					'on'=>'tag_cas',
					'champs'=>array('id_tag', 'id_cas')
				),

				array(
					'type'=>'table',
					'nom'=>'chat',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'id_from','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_to','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'message','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'lus',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_corresp','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_user','type'=>'TEXT','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'supports',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'nom','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'h_page','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'l_page','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'nb_lignes','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'nb_colonnes','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'offset','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'mp_gauche','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'mp_droite','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'mp_haut','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'mp_bas','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'mc_gauche','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'mc_droite','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'mc_haut','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'mc_bas','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'police','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'tpl','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'templates',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'nom','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'boite_envoi',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'id_cas','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_envoi','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'i','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'erreurs','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'by','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'emails',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'sujet','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'html','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'news',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'sujet','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'id_newsletter','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'blocs','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'background_img','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'background_color','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'publie','type'=>'INTEGER','defaut'=>'0','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'news_modeles',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'nom','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'modele','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'modeles_news',
					'champs'=>array(
						array('nom'=>'id_news','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_modele','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'unique index',
					'nom'=>'modeles_news_idx',
					'on'=>'modeles_news',
					'champs'=>array('id_news', 'id_modele')
				),
				array(
					'type'=>'table',
					'nom'=>'envois',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'sujet','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'html','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'query','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'type','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'id_type','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'pjs','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'expediteur','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'log','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'nb','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'statut','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'by','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'index',
					'nom'=>'envois_idx',
					'on'=>'envois',
					'champs'=>array('type', 'id_type')
				),
				array(
					'type'=>'table',
					'nom'=>'envoi_cas',
					'champs'=>array(
						array('nom'=>'id_envoi','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_cas','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'emails','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'unique index',
					'nom'=>'envoi_cas_idx',
					'on'=>'envoi_cas',
					'champs'=>array('id_envoi', 'id_cas', 'emails')
				),
				array(
					'type'=>'table',
					'nom'=>'r',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'id_cas','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_envoi','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'url','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'suivis',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'id_thread','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'titre','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'desc','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'statut','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'suivis_threads',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'id_casquette','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'nom','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'desc','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'creationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'createdby','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modificationdate','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'modifiedby','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'users',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'login','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'name','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'password','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'prefs','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'active','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'groups',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'nom','type'=>'TEXT','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'user_group',
					'champs'=>array(
						array('nom'=>'id_user','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_group','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'unique index',
					'nom'=>'user_group_idx',
					'on'=>'user_group',
					'champs'=>array('id_user', 'id_group')
				),
				array(
					'type'=>'table',
					'nom'=>'doublons_email',
					'champs'=>array(
						array('nom'=>'email','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'id_casquette','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'index',
					'nom'=>'doublons_email_idx',
					'on'=>'doublons_email',
					'champs'=>array('email','id_casquette')
				),
				array(
					'type'=>'table',
					'nom'=>'doublons_texte',
					'champs'=>array(
						array('nom'=>'id_doublon','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_contact','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'non_doublons_texte',
					'champs'=>array(
						array('nom'=>'id_doublon','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'id_contact','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),
				array(
					'type'=>'table',
					'nom'=>'schedule',
					'champs'=>array(
						array('nom'=>'id','type'=>'INTEGER','defaut'=>'','options'=>'PRIMARY KEY AUTOINCREMENT'),
						array('nom'=>'id_item','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'type','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'json','type'=>'TEXT','defaut'=>'','options'=>''),
						array('nom'=>'date','type'=>'INTEGER','defaut'=>'','options'=>''),
						array('nom'=>'by','type'=>'INTEGER','defaut'=>'','options'=>'')
					)
				),

			);

			// do we need to build a new database?
			$fill = false;
			if(!file_exists($dbfile)) { $fill = true; }

			// bind the database handler
			$this->database = new PDO("sqlite:" . $dbfile);

			// If we need to rebuild, the file will have been automatically made by the PDO call,
			// but we'll still need to define the user table before we can use the database.
			if ($check) $this->check_database();
			if ($fill) { $this->fill(); }
		}

		// this function rebuilds the database if there is no database to work with yet
		function check_database()
		{

			$sql=array();
			foreach($this->schema as $item){
				if ($item['type']=='table') {
					$sql_test="PRAGMA table_info({$item['nom']})";
					$cs=array();
					foreach($this->database->query($sql_test) as $row){
						$cs[]=$row['name'];
					}
					$test=true;
					if (count($cs)==0) {
						$champs=array();
						foreach($item['champs'] as $c) {
							$champ=trim("{$c['nom']} {$c['type']} {$c['options']}");
							if ($c['defaut']!="") $champ.=" default {$c['defaut']}";
							$champs[]=$champ;
						}
						$champs=implode(', ',$champs);
						$sql[]="CREATE TABLE {$item['nom']} ($champs)";
					} else {
						foreach($item['champs'] as $c) {
							if (!in_array($c['nom'],$cs)){
								$champ=trim("{$c['nom']} {$c['type']} {$c['options']}");
								if ($c['defaut']!="") $champ.=" default {$c['defaut']}";
								$sql[]="ALTER TABLE ".$item['nom']." ADD COLUMN $champ";
								$test=false;
							}
						}
					}
				}
				if ($item['type']=='fts table') {
					$sql_test="PRAGMA table_info({$item['nom']})";
					$cs=array();
					foreach($this->database->query($sql_test) as $row){
						$cs[]=$row['name'];
					}
					$test=true;
					if (count($cs)==0) {
						$champs=array();
						foreach($item['champs'] as $c) {
							$champ=trim("{$c['nom']} {$c['type']} {$c['options']}");
							if ($c['defaut']!="") $champ.=" default {$c['defaut']}";
							$champs[]=$champ;
						}
						$champs=implode(', ',$champs);
						$sql[]="CREATE VIRTUAL TABLE {$item['nom']} USING fts3($champs)";
					} else {
						foreach($item['champs'] as $c) {
							if (!in_array($c['nom'],$cs)){
								$champ=trim("{$c['nom']} {$c['type']} {$c['options']}");
								if ($c['defaut']!="") $champ.=" default {$c['defaut']}";
								$sql[]="ALTER TABLE ".$item['nom']." ADD COLUMN $champ";
								$test=false;
							}
						}
					}
				}
				if ($item['type']=='index') {
					$sql_test="PRAGMA index_info({$item['nom']})";
					$cs=array();
					foreach($this->database->query($sql_test) as $row){
						$cs[]=$row['name'];
					}
					$test=true;
					$champs=implode(', ',$item['champs']);
					if (count($cs)==0) {
						$sql[]="CREATE INDEX {$item['nom']} on {$item['on']}($champs)";
					} else {
						foreach($item['champs'] as $c) {
							if (!in_array($c,$cs)){
								$test=false;
							}
						}
						if (!$test) {
							$sql[]="DROP INDEX {$item['nom']}";
							$sql[]="CREATE INDEX {$item['nom']} on {$item['on']}($champs)";
						}
					}
				}
				if ($item['type']=='unique index') {
					$sql_test="PRAGMA index_info({$item['nom']})";
					$cs=array();
					foreach($this->database->query($sql_test) as $row){
						$cs[]=$row['name'];
					}
					$test=true;
					$champs=implode(', ',$item['champs']);
					if (count($cs)==0) {
						$sql[]="CREATE UNIQUE INDEX {$item['nom']} on {$item['on']}($champs)";
					} else {
						foreach($item['champs'] as $c) {
							if (!in_array($c,$cs)){
								$test=false;
							}
						}
						if (!$test) {
							$sql[]="DROP INDEX {$item['nom']}";
							$sql[]="CREATE UNIQUE INDEX {$item['nom']} on {$item['on']}($champs)";
						}
					}
				}
			}
			if (count($sql)>0) {
				error_log(date('d/m/Y h:i:s')." modification de la base : \n",3,'data/log/db.log');
				error_log(implode("\n",$sql)."\n\n",3,'data/log/db.log');
				$this->database->beginTransaction();
				foreach($sql as $s){
					$this->database->exec($s);
				}
				$this->database->commit();
			}
			//on verifie les NULL dans la table tag_cas
			$sql="SELECT count(*) as nb from tag_cas WHERE id_tag IS NULL or id_cas IS NULL;";
			$nb=0;
			foreach($this->database->query($sql) as $row){
				$nb=$row['nb'];
			}
			if ($nb>0) {
				error_log(date('d/m/Y h:i:s')." modification de la base : \n",3,'data/log/db.log');
				$delete=$this->database->prepare("DELETE from tag_cas WHERE id_tag IS NULL or id_cas IS NULL;");
				$delete->execute(array());
				error_log("DELETE from tag_cas WHERE id_tag IS NULL or id_cas IS NULL;\n\n",3,'data/log/db.log');
			}
			//on supprime les contacts sans casquette
			$sql="SELECT count(*) as nb from contacts WHERE id not in (SELECT id_contact from casquettes group by id_contact)";
			$nb=0;
			foreach($this->database->query($sql) as $row){
				$nb=$row['nb'];
			}
			if ($nb>0) {
				error_log(date('d/m/Y h:i:s')." modification de la base : \n",3,'data/log/db.log');
				$delete=$this->database->prepare("DELETE from contacts WHERE id not in (SELECT id_contact from casquettes group by id_contact)");
				$delete->execute(array());
				error_log("DELETE from contacts WHERE id not in (SELECT id_contact from casquettes group by id_contact);\n\n",3,'data/log/db.log');
			}

		}
		function fill()
		{
			$password=md5('adminadmin');
			$prefs=array();
			$prefs['panier']=array();
			$insert = $this->database->prepare("INSERT INTO users (login, name, password, prefs, active) VALUES (?,?,?,?,?)");
			$insert->execute(array('admin', 'Admin', $password, json_encode($prefs),1));
		}
	}
