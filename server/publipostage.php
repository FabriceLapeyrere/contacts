<?php
	class Publipostage
	{
		protected $WS;
		protected $from;
		public function __construct($WS,$from) {
	 	 	$this->WS= $WS;
	 	 	$this->from= $from;
		}
		//supports
		public static function get_support($id) {
			$db= new DB();
			$query = "SELECT * FROM supports WHERE id=$id";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row;
			}
			return $res[0];
		}
		public static function get_supports() {
			$db= new DB();
			$query = "SELECT * FROM supports ORDER BY id ASC";
			$supports=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$supports[$row['id']]=$row;
			}
			return $supports;
		}
		public function add_support($params,$id) {
			$t=Publipostage::do_add_support($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_support($params,$id) {
			$db= new DB();
			$insert= $db->database->prepare('INSERT INTO supports (nom, mp_gauche, mp_droite, offset, mp_haut, mp_bas, mc_gauche, mc_droite, mc_haut, mc_bas, modificationdate, modifiedby, creationdate, createdby) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
			$insert->execute(array(
				$params->support->nom,0,0,0,0,0,0,0,0,0,
				millisecondes(),
				$id,
				millisecondes(),
				$id
				)
			);
			$id_support=$db->database->lastInsertId();
			return array('maj'=>array("supports"),'res'=>$id_support);
		}
		public function mod_support($params,$id) {
			$t=Publipostage::do_mod_support($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_support($params,$id) {
			$db= new DB();
			$update= $db->database->prepare('UPDATE supports SET
				nom=?,
				h_page=?,
				l_page=?,
				nb_lignes=?,
				nb_colonnes=?,
				offset=?,
				mp_gauche=?,
				mp_droite=?,
				mp_haut=?,
				mp_bas=?,
				mc_gauche=?,
				mc_droite=?,
				mc_haut=?,
				mc_bas=?,
				police=?,
				modificationdate=?,
				modifiedby=?
				WHERE id=?');
			$update->execute(array(
				$params->support->nom,
				$params->support->h_page,
				$params->support->l_page,
				$params->support->nb_lignes,
				$params->support->nb_colonnes,
				$params->support->offset,
				$params->support->mp_gauche,
				$params->support->mp_droite,
				$params->support->mp_haut,
				$params->support->mp_bas,
				$params->support->mc_gauche,
				$params->support->mc_droite,
				$params->support->mc_haut,
				$params->support->mc_bas,
				$params->support->police,
				millisecondes(),
				$id,
				$params->support->id
				)
			);
			return array('maj'=>array("support/".$params->support->id),'res'=>Publipostage::get_support($params->support->id));
		}
		public function del_support($params,$id) {
			$t=Publipostage::do_del_support($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_support($params,$id) {
			$db= new DB();
			$id_support=$params->support->id;
			$support=Publipostage::get_support($id_support);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id,'support',json_encode($support),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM supports WHERE id=?');
			$delete->execute(array($id_support));
			return array('maj'=>array('supports'), 'res'=>1);
		}
		//templates
		public static function get_template($id) {
			$db= new DB();
			$query = "SELECT * FROM templates WHERE id=$id";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				foreach(glob("../data/files/template/".$row['id']."/*") as $f){
					if (is_file($f)) {
						clearstatcache();
						$row['template'][]=array(
							"path"=>$f,
							"filename"=>basename($f),
							"mime"=>finfo_file($finfo, $f),
							"modified"=>filemtime($f)*1000
						);
					}
				}
				$res[]=$row;
			}
			return $res[0];
		}
		public static function get_templates() {
			$db= new DB();
			$query = "SELECT * FROM templates ORDER BY id ASC";
			$templates=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$templates[$row['id']]=$row;
			}
			return $templates;
		}
		public function touch_template($id_template,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE templates SET modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array(millisecondes(), $id, $id_template));
			return 1;
		}
		public function add_template($params,$id) {
			$t=Publipostage::do_add_template($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_template($params,$id) {
			$db= new DB();
			$insert= $db->database->prepare('INSERT INTO templates (nom, modificationdate, modifiedby, creationdate, createdby) VALUES (?,?,?,?,?)');
			$insert->execute(array(
				$params->template->nom,
				millisecondes(),
				$id,
				millisecondes(),
				$id
				)
			);
			$id_template=$db->database->lastInsertId();
			return array('maj'=>array("templates"),'res'=>$id_template);
		}
		public function mod_template($params,$id) {
			$t=Publipostage::do_mod_template($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_template($params,$id) {
			$db= new DB();
			$update= $db->database->prepare('UPDATE templates SET
				nom=?,
				modificationdate=?,
				modifiedby=?
				WHERE id=?');
			$update->execute(array(
				$params->template->nom,
				millisecondes(),
				$id,
				$params->template->id
				)
			);
			return array('maj'=>array("template/".$params->template->id),'res'=>Publipostage::get_template($params->template->id));
		}
		public function del_template($params,$id) {
			$t=Publipostage::do_del_template($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_template($params,$id) {
			$db= new DB();
			$id_template=$params->template->id;
			$template=Publipostage::get_template($id_template);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id,'template',json_encode($template),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM templates WHERE id=?');
			$delete->execute(array($id_template));
			return array('maj'=>array('templates'), 'res'=>1);
		}
		public function del_tpl($params,$id) {
			$t=Publipostage::do_del_tpl($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_tpl($params,$id)
		{
			$id_template=$params->id;
			$tpl=$params->tpl;
			unlink("../data/files/template/$id_template/".$tpl->filename);
			Publipostage::touch_template($id_template,$id);
			return array('maj'=>array("template/$id_template"),'res'=>1);
		}

	}
?>
