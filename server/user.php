<?php
	class User
	{
		protected $WS;
		protected $from;
		public function __construct($WS,$from) {
	 	 	$this->WS= $WS;
	 	 	$this->from= $from;
		}
	 	// check login/password
		public static function check($login,$password)
		{
			$db= new DB();
			$password=md5($login.$password);
			$query = "SELECT id, login, name FROM users WHERE login='$login' and password='$password' and active=1";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row;
			}
			return $res;
		}
		// create new user
		public function create($login,$name,$password)
		{
			$db= new DB();
			$password=md5($login.$password);
			$prefs=array();
			$prefs['panier']=array();
			$insert = $db->database->prepare('INSERT INTO users (login, name, password, prefs, active) VALUES (?,?,?,?,?) ');
			$insert->execute(array($login,$name,$password,json_encode($prefs),1));
			$id=$db->database->lastInsertId();
			$this->WS->maj(array('users'));
			return $id;
		}
		public function update($id,$login,$name,$password)
		{
			$db= new DB();
			if ($password=='') {
				$update = $db->database->prepare('UPDATE users set name=? WHERE id=?');
				$update->execute(array($name,$id));
			} else {
				$password=md5($login.$password);
				$update = $db->database->prepare('UPDATE users set name=?, password=? WHERE id=?');
				$update->execute(array($name,$password,$id));
			} 
			$this->WS->maj(array("user/$id"));
			return User::get_user($id);
		}
		public function add_group($nom,$id)
		{
			if ($id==1) {
				$db= new DB();
				$insert = $db->database->prepare('INSERT INTO groups (nom) VALUES (?)');
				$insert->execute(array($nom));
				$id_group=$db->database->lastInsertId();
				$this->WS->maj(array('groups'));
				return $id_group;
			}
		}
		public static function is_owner($type_ressource,$id_ressource,$id) {
			if ($id==1) {
				return true;
			} else {
				$db= new DB();
				$query = "SELECT createdby FROM $type_ressource WHERE id=$id_ressource";
				$res=0;
				foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
					$res=$row;
				}
				return $res['createdby']==$id;
			}
			return false;
		}
		public static function has_group($id_group,$id) {
			if ($id==1) {
				return true;
			} else {
				$db= new DB();
				$query = "SELECT count(*) as n FROM user_group WHERE id_group=$id_group AND id_user=$id";
				$res=0;
				foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
					$res=$row['n'];
				}
				return $res==1;
			}
			return false;
		}
		public static function get_acl($type_ressource,$id_ressource,$id)
		{
			$db= new DB();
			$query = "SELECT * FROM acl WHERE type_ressource='$type_ressource' AND id_ressource=$id_ressource";
			$res=array('user'=>array(),'group'=>array(),'all'=>array());
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[$row['type_acces']][]=$row['id_acces'];
				$res['all'][]=array('type_acces'=>$row['type_acces'],'id_acces'=>$row['id_acces'],'level'=>$row['level']);
			}
			return $res;
		}
		public function add_acl($type_ressource,$id_ressource,$type_acces,$id_acces,$level,$id) {
			$t=User::do_add_acl($type_ressource,$id_ressource,$type_acces,$id_acces,$level,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_acl($type_ressource,$id_ressource,$type_acces,$id_acces,$level,$id)
		{
			$res=array('maj'=>array(),'res'=>1);
			if (User::is_owner($type_ressource,$id_ressource,$id) && ($type_acces!='group' || User::has_group($id_acces,$id) )) {
		  		$db= new DB();
				$insert = $db->database->prepare('INSERT OR REPLACE INTO acl (type_ressource, id_ressource, type_acces, id_acces, level) VALUES (?,?,?,?,?)');
				$insert->execute(array($type_ressource,$id_ressource,$type_acces,$id_acces,$level));
				if ($type_acces=='user') {
					$insert = $db->database->prepare('INSERT OR REPLACE INTO acl (type_ressource, id_ressource, type_acces, id_acces, level) VALUES (?,?,?,?,?)');
					$insert->execute(array($type_ressource,$id_ressource,$type_acces,$id,$level));
				}
				$res['maj']=array('*');
			}
			return $res;
		}
		public function del_acl($type_ressource,$id_ressource,$type_acces,$id_acces,$id) {
			$t=User::do_del_acl($type_ressource,$id_ressource,$type_acces,$id_acces,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_acl($type_ressource,$id_ressource,$type_acces,$id_acces,$id)
		{
			$res=array('maj'=>array(),'res'=>1);
			if (User::is_owner($type_ressource,$id_ressource,$id) && ($type_acces!='group' || User::has_group($id_acces,$id) )) {
		  		$db= new DB();
				$delete = $db->database->prepare('DELETE FROM acl WHERE type_ressource=? AND id_ressource=? AND type_acces=? AND id_acces=?');
				$delete->execute(array($type_ressource,$id_ressource,$type_acces,$id_acces));
				if ($type_acces=='user') {
					$query = "SELECT count(*) as n FROM acl WHERE type_ressource='$type_ressource' AND id_ressource=$id_ressource AND type_acces='user' AND id_acces!=$id";
					$res=1;
					foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
						$res=$row['n'];
					}
					if ($res==0) {
						$delete = $db->database->prepare('DELETE FROM acl WHERE type_ressource=? AND id_ressource=? AND type_acces=? AND id_acces=?');
						$delete->execute(array($type_ressource,$id_ressource,$type_acces,$id));
					}
				}
				$res['maj']=array('*');
 			}
			return $res;
		}
		public function add_user_group($id_user,$id_group,$id)
		{
			$db= new DB();
			$insert = $db->database->prepare('INSERT OR REPLACE INTO user_group (id_user,id_group) VALUES (?,?) ');
			$insert->execute(array($id_user,$id_group));
			$this->WS->maj(array('*'));
		}
		public function del_user_group($id_user,$id_group,$id)
		{
			$db= new DB();
			$delete = $db->database->prepare('DELETE FROM user_group WHERE id_user=? AND id_group=?');
			$delete->execute(array($id_user,$id_group));
			$this->WS->maj(array('*'));
		}
		public function mod_group($id_group,$nom,$id)
		{
			$db= new DB();
			$update = $db->database->prepare('UPDATE groups set nom=? WHERE id=?');
			$update->execute(array($nom,$id_group));
			$this->WS->maj(array('*'));
		}
		public function del_group($id_group,$id)
		{
			$db= new DB();
			$del = $db->database->prepare('DELETE FROM groups WHERE id=?');
			$del->execute(array($id_group));
			$del = $db->database->prepare('DELETE FROM user_group WHERE id_group=?');
			$del->execute(array($id_group));
			$this->WS->maj(array('*'));
		}
		public function mod_prefs($params,$id)
		{
			$db= new DB();
			$query = "SELECT prefs FROM users WHERE id=$id";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row['prefs'];
			}
			$prefs=json_decode($res);
			if (isset($params->panier)) {
				$prefs->panier=$params->panier;
			}
			$update = $db->database->prepare('UPDATE users set prefs=? WHERE id=?');
			$update->execute(array(json_encode($prefs),$id));
			$this->WS->maj(array("user","casquettes",'carte'));
			return 1;
		}
		public function add_panier($params,$id)
		{
			$db= new DB();
			$query = "SELECT prefs FROM users WHERE id=$id";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row['prefs'];
			}
			$prefs=json_decode($res);
			if (isset($params->nouveaux)) {
				$prefs->panier=array_merge($prefs->panier, $params->nouveaux);
			}
			$update = $db->database->prepare('UPDATE users set prefs=? WHERE id=?');
			$update->execute(array(json_encode($prefs),$id));
			$this->WS->maj(array("user",'casquettes','carte'));
			return 1;
		}
		public function panier_all($params,$id)
		{
			$db= new DB();
			$query = "SELECT prefs FROM users WHERE id=$id";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row['prefs'];
			}
			$prefs=json_decode($res);
			$tab=Contacts::get_id_cass_filtre($params->query,$id);
			if (count($tab)>0) {
				$prefs->panier=array_values(array_unique(array_merge($prefs->panier, $tab)));
			}
			$update = $db->database->prepare('UPDATE users set prefs=? WHERE id=?');
			$update->execute(array(json_encode($prefs),$id));
			$this->WS->maj(array("user",'casquettes','carte'));
			return 1;
		}
		public function del_panier($params,$id) {
			$t=User::do_del_panier($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_panier($params,$id)
		{
			$db= new DB();
			$query = "SELECT prefs FROM users WHERE id=$id";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row['prefs'];
			}
			$prefs=json_decode($res);
			if (isset($params->nouveaux)) {
				$prefs->panier=array_values(array_diff($prefs->panier, $params->nouveaux));
			}
			$update = $db->database->prepare('UPDATE users set prefs=? WHERE id=?');
			$update->execute(array(json_encode($prefs),$id));
			return array('maj'=>array('user','casquettes','carte'), 'res'=>1);
		}
		public function del($id_user,$id)
		{
			if ($id==1 && $id_user!=1){
				$db= new DB();
				$del = $db->database->prepare('UPDATE users set active=0 WHERE id=?');
				$del->execute(array($id_user));
				$this->WS->maj(array('users'));
			}
		}
		public static function get_users()
		{
			$db= new DB();
			$query = "SELECT id, login, name FROM users WHERE active=1";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[$row['id']]=$row;
			}
			return $res;
		}
		public static function get_users_all()
		{
			$db= new DB();
			$query = "SELECT id, login, name FROM users";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[$row['id']]=$row;
			}
			return $res;
		}
		public static function get_groups()
		{
			$db= new DB();
			$query = "SELECT
				t1.id,
				t1.nom,
				'[' || Group_Concat(DISTINCT '\"' || t3.id || '\"') ||']' as users
				FROM groups as t1
				left outer join user_group as t2 on t1.id=t2.id_group
				left outer join users as t3 on t3.id=t2.id_user AND t3.active=1
				group by t1.id
			";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['users']= is_array(json_decode($row['users'])) ? json_decode($row['users']) : array();
				$res[$row['id']]=$row;
			}
			return $res;
		}
		public static function get_panier($id)
		{
			$db= new DB();
			$query = "SELECT prefs FROM users WHERE id=$id AND active=1";
			$res='';
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row['prefs'];
			}
			if ($res!='') $prefs=json_decode($res);
			else $prefs=(object) NULL;
			error_log(var_export($prefs->panier,true),3,"data/tmp/debug.log");
			return is_array($prefs->panier) ? $prefs->panier : array();
		}
		public static function get_users_list()
		{
			$db= new DB();
			$query = "SELECT id, name FROM users AND active=1";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[$row['id']]=$row;
			}
			return $res;
		}
		public static function get_user($id_user)
		{
			$db= new DB();
			$query = "SELECT id, login, name FROM users WHERE id=$id_user AND active=1";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row;
			}
			return $res;
		}
		public static function get_log(){
			$i=0;
			$tab=array();
			$lines = file('./data/log/log.txt');
			foreach (array_reverse($lines) as $line) {
				$tab[]=json_decode($line);
				$i++;
				if ($i>=100) break;
			}
			return $tab;
		}

	}
