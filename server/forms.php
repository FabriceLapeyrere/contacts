<?php
	class Forms
	{
		protected $WS;
		protected $from;
		public function __construct($WS,$from) {
	 	 	$this->WS= $WS;
	 	 	$this->from= $from;
		}
		//forms
		public static function get_form($id_form,$id) {
			$db= new DB();
			$query = "SELECT * FROM forms WHERE id=$id_form";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['schema']=json_decode($row['schema']);
				$res[]=$row;
			}
			return $res[0];
		}
		public static function get_form_instance($id_form,$id_cas,$id) {
			$db= new DB();
			$query = "SELECT * FROM forms_data WHERE id_form=$id_form AND type_lien='casquette' AND id_lien=$id_cas";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[$row['id_schema']]=$row;
			}
			return (object) $res;
		}
		public function mod_form_instance($params,$id) {
			$t=Forms::do_mod_form_instance($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_form_instance($params,$id) {
			$db_instance=Forms::get_form_instance($params->id_form,$params->id_cas,$id);
			$new=array();
			$mod=array();
			foreach($params->instance as $k=>$e){
				$e->id_schema=$k;
				$test=false;
				foreach($db_instance as $oe){
					if ($e->id_schema==$oe['id_schema']) {
						$test=true;
						if ($e->valeur!=$oe['valeur']) $mod[]=$e;
					}
				}
				if (!$test) $new[]=$e;
			}
			error_log(var_export($mod,true),3,"/tmp/fab.log");
			$db= new DB();
			$db->database->beginTransaction();
			foreach($new as $n) {
				$insert= $db->database->prepare('INSERT INTO forms_data (id_form, id_lien, type_lien, id_schema, valeur, modificationdate, modifiedby, creationdate, createdby) VALUES (?,?,?,?,?,?,?,?,?)');
				$insert->execute(array(
					$params->id_form,
					$params->id_cas,
					"casquette",
					$n->id_schema,
					$n->valeur,
					millisecondes(),
					$id,
					millisecondes(),
					$id
					)
				);
			}
			foreach($mod as $m) {
				$insert= $db->database->prepare('UPDATE forms_data SET valeur=?, modificationdate=?, modifiedby=? WHERE id_form=? AND type_lien=? AND id_lien=? AND id_schema=?');
				$insert->execute(array(
					$m->valeur,
					millisecondes(),
					$id,
					$params->id_form,
					"casquette",
					$params->id_cas,
					$m->id_schema,
					)
				);
			}
			$db->database->commit();
			return array('maj'=>array("form_instance/".$params->id_form."/".$params->id_cas),'res'=>1);
		}
		public static function get_forms() {
			$db= new DB();
			$query = "SELECT * FROM forms ORDER BY id ASC";
			$forms=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['schema']=json_decode($row['schema']);
				$forms[$row['id']]=$row;
			}
			return $forms;
		}
		public static function get_forms_casquette($id_cas, $id) {
			$db= new DB();
			$query = "SELECT id_form FROM form_casquette WHERE id_casquette=$id_cas ORDER BY id_form ASC;";
			$forms=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$forms[]=$row['id_form'];
			}
			return $forms;
		}
		public function add_form($params,$id) {
			$t=Forms::do_add_form($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_form($params,$id) {
			$db= new DB();
			$insert= $db->database->prepare('INSERT INTO forms (nom, schema, modificationdate, modifiedby, creationdate, createdby) VALUES (?,?,?,?,?,?)');
			$insert->execute(array(
				$params->form->nom,
				'{}',
				millisecondes(),
				$id,
				millisecondes(),
				$id
				)
			);
			$id_form=$db->database->lastInsertId();
			return array('maj'=>array("forms"),'res'=>$id_form);
		}
		public function mod_form($params,$id) {
			$t=Forms::do_mod_form($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_form($params,$id) {
			$db= new DB();
			$update= $db->database->prepare('UPDATE forms SET
				nom=?,
				schema=?,
				modificationdate=?,
				modifiedby=?
				WHERE id=?');
			$update->execute(array(
				$params->form->nom,
				json_encode($params->form->schema),
				millisecondes(),
				$id,
				$params->form->id
				)
			);
			return array('maj'=>array("form/".$params->form->id),'res'=>Forms::get_form($params->form->id));
		}
		public function del_form($params,$id) {
			$t=Forms::do_del_form($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_form($params,$id) {
			$db= new DB();
			$id_form=$params->form->id;
			$form=Forms::get_form($id_form);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id,'form',json_encode($form),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM forms WHERE id=?');
			$delete->execute(array($id_form));
			return array('maj'=>array('forms'), 'res'=>1);
		}
		public function add_form_cas($params,$id) {
			$t=Forms::do_add_form_cas($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_form_cas($params,$id) {
			$db= new DB();
			$cas=Contacts::get_casquette($params->id_cas,false,$id);
			$insert= $db->database->prepare('INSERT INTO form_casquette (id_form,id_casquette) VALUES (?,?)');
			$insert->execute(array($params->id_form,$params->id_cas));
			return array('maj'=>array("forms","contact/".$cas['id_contact'],"form_casquettes/".$params->id_form),'res'=>1);
		}
		public function del_form_cas($params,$id) {
			$t=Forms::do_del_form_cas($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_form_cas($params,$id) {
			$db= new DB();
			$cas=Contacts::get_casquette($params->id_cas,false,$id);
			$insert= $db->database->prepare('DELETE FROM form_casquette WHERE id_form=? AND id_casquette=?');
			$insert->execute(array($params->id_form,$params->id_cas));
			return array('maj'=>array("forms","contact/".$cas['id_contact'],"form_casquettes/".$params->id_form),'res'=>1);
		}
	}
?>
