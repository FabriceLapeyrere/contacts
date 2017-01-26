<?php
	class DB
	{
		// class object constructor
		function __construct()
		{
			if (!file_exists('./data/db')) mkdir('./data/db', 0777, true);
			// file location for the user database
			$dbfile = "./data/db/db.db";

			// do we need to build a new database?
			$rebuild = false;
			if(!file_exists($dbfile)) { $rebuild = true; }

			// bind the database handler
			$this->database = new PDO("sqlite:" . $dbfile);

			// If we need to rebuild, the file will have been automatically made by the PDO call,
			// but we'll still need to define the user table before we can use the database.
			if($rebuild) { $this->rebuild_database($dbfile); }

		}

		// this function rebuilds the database if there is no database to work with yet
		function rebuild_database($dbfile)
		{
			//begin transaction
			$this->database->beginTransaction();
			//ALL
			$create = "CREATE TABLE trash (id INTEGER PRIMARY KEY AUTOINCREMENT, id_item INTEGER, type TEXT, json TEXT, date INTEGER, by INTEGER)";
			$this->database->exec($create);
			$create = "CREATE TABLE acl (id_ressource INTEGER, type_ressource TEXT, id_acces INTEGER, type_acces TEXT, level INTEGER);";
			$this->database->exec($create);
			$create = "CREATE UNIQUE INDEX acl_idx on acl(id_ressource, type_ressource, id_acces, type_acces)";
			$this->database->exec($create);		
			//CONTACTS
			$create = "CREATE TABLE contacts (id INTEGER PRIMARY KEY AUTOINCREMENT, sort TEXT, nom TEXT, prenom TEXT, type INTEGER, creationdate INTEGER, createdby INTEGER, modificationdate INTEGER, modifiedby INTEGER)";
			$this->database->exec($create);
			$create = "CREATE TABLE casquettes (id INTEGER PRIMARY KEY AUTOINCREMENT, nom TEXT, donnees TEXT, id_contact INTEGER, id_etab INTEGER, emails TEXT, email_erreur INTEGER, fonction TEXT, cp TEXT, gps_x REAL, gps_y REAL, creationdate INTEGER, createdby INTEGER, modificationdate INTEGER, modifiedby INTEGER)";
			$this->database->exec($create);
			$create = "CREATE VIRTUAL TABLE casquettes_fts USING fts3(id INTEGER PRIMARY KEY AUTOINCREMENT, idx TEXT)";
			$this->database->exec($create);
			$create = "CREATE TABLE tags (id INTEGER PRIMARY KEY AUTOINCREMENT, nom INTEGER, color TEXT, id_parent INTEGER, creationdate INTEGER, createdby INTEGER, modificationdate INTEGER, modifiedby INTEGER)";
			$this->database->exec($create);
			$create = "CREATE TABLE selections (id INTEGER PRIMARY KEY AUTOINCREMENT, nom INTEGER, query TEXT, creationdate INTEGER, createdby INTEGER, modificationdate INTEGER, modifiedby INTEGER)";
			$this->database->exec($create);
			$create = "CREATE TABLE tag_cas (id_tag INTEGER, id_cas INTEGER, date INTEGER)";
			$this->database->exec($create);
			$create = "CREATE INDEX contacts_idx on contacts(sort)";
			$this->database->exec($create);
			$create = "CREATE INDEX casquettes_idx on casquettes(id_contact, id_etab, cp, email_erreur)";
			$this->database->exec($create);
			$create = "CREATE UNIQUE INDEX tag_cas_idx on tag_cas(id_tag, id_cas)";
			$this->database->exec($create);		
			//CHAT
			$create = "CREATE TABLE chat (id INTEGER PRIMARY KEY AUTOINCREMENT, id_from INTEGER, id_to INTEGER, message TEXT, creationdate INTEGER, modificationdate INTEGER)";
			$this->database->exec($create);
			$create = "CREATE TABLE lus (id INTEGER PRIMARY KEY AUTOINCREMENT, date INTEGER, id_corresp INTEGER, id_user INTEGER)";
			$this->database->exec($create);
			//PUBLIPOSTAGE
			$create = "CREATE TABLE supports (id INTEGER PRIMARY KEY AUTOINCREMENT, nom TEXT, h_page INTEGER, l_page INTEGER, nb_lignes INTEGER, nb_colonnes INTEGER, offset INTEGER, mp_gauche INTEGER, mp_droite INTEGER, mp_haut INTEGER, mp_bas INTEGER, mc_gauche INTEGER, mc_droite INTEGER, mc_haut INTEGER, mc_bas INTEGER, police INTEGER, tpl TEXT, creationdate INTEGER, createdby INTEGER, modificationdate INTEGER, modifiedby INTEGER)";
			$this->database->exec($create);
			//MAILING
			$create = "CREATE TABLE boite_envoi (id INTEGER PRIMARY KEY AUTOINCREMENT, id_cas INTEGER, id_envoi INTEGER, i INTEGER, date INTEGER, erreurs TEXT, by INTEGER)";
			$this->database->exec($create);
			$create = "CREATE TABLE emails (id INTEGER PRIMARY KEY AUTOINCREMENT, sujet TEXT, html TEXT, creationdate INTEGER, createdby INTEGER, modificationdate INTEGER, modifiedby INTEGER)";
			$this->database->exec($create);
			$create = "CREATE TABLE news (id INTEGER PRIMARY KEY AUTOINCREMENT, sujet TEXT, blocs TEXT, creationdate INTEGER, createdby INTEGER, modificationdate INTEGER, modifiedby INTEGER)";
			$this->database->exec($create);
			$create = "CREATE TABLE news_modeles (id INTEGER PRIMARY KEY AUTOINCREMENT, nom TEXT, modele TEXT, creationdate INTEGER, createdby INTEGER, modificationdate INTEGER, modifiedby INTEGER)";
			$this->database->exec($create);
			$create = "CREATE TABLE envois (id INTEGER PRIMARY KEY AUTOINCREMENT, sujet TEXT, html TEXT, pjs TEXT, expediteur TEXT, log TEXT, nb INTEGER, statut INTEGER, date INTEGER, by INTEGER)";
			$this->database->exec($create);
			$create = "CREATE TABLE r (id INTEGER PRIMARY KEY AUTOINCREMENT, id_cas INTEGER, id_envoi INTEGER, url TEXT, date DATE);";
			$this->database->exec($create);
			//SUIVIS
			$create = "CREATE TABLE suivis (id INTEGER PRIMARY KEY AUTOINCREMENT, id_casquette INTEGER, titre TEXT, desc TEXT, date INTEGER, statut INTEGER, creationdate INTEGER, createdby INTEGER, modificationdate INTEGER, modifiedby INTEGER)";
			$this->database->exec($create);
			//USERS
			$create = "CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, login TEXT, name TEXT, password TEXT, prefs TEXT, active INTEGER);";
			$this->database->exec($create);
			$create = "CREATE TABLE groups (id INTEGER PRIMARY KEY AUTOINCREMENT, nom TEXT);";
			$this->database->exec($create);
			$create = "CREATE TABLE acl (id_ressource INTEGER, type_ressource TEXT, id_acces INTEGER, type_acces TEXT, level INTEGER);";
			$this->database->exec($create);
			$create = "CREATE TABLE user_group (id_user INTEGER, id_group INTEGER);";
			$this->database->exec($create);
			$create = "CREATE UNIQUE INDEX acl_idx on acl(id_ressource, type_ressource, id_acces, type_acces)";
			$this->database->exec($create);		
			$create = "CREATE UNIQUE INDEX user_group_idx on user_group(id_user, id_group)";
			$this->database->exec($create);		
			$password=md5('adminadmin');
			$prefs=array();
			$prefs['panier']=array();
			$select = $this->database->prepare("INSERT INTO users (login, name, password, prefs, active) VALUES (?,?,?,?,?)");
			$select->execute(array('admin', 'Admin', $password, json_encode($prefs),1));
			//commit
            $this->database->commit();
		}
	}
