<?php
	class Suivis
	{
		public static function get_suivis($id)
		{
			$db= new DB();
			$query = "SELECT t1.*,
				'[' || Group_Concat(DISTINCT t2.id_acces)||']' as groups
			FROM suivis as t1
			left outer join acl as t2 on t2.id_ressource=t1.id AND t2.type_ressource='suivis' AND t2.type_acces='group'
			where
			$id=1 OR
			createdby=$id OR
			id IN (SELECT id_ressource from acl where type_ressource='suivis' AND type_acces='user' AND id_acces=6) OR
			id IN (SELECT id_ressource from acl where type_ressource='suivis' AND type_acces='group' AND id_acces IN
					(select id_group from user_group where id_user=$id)
				)
			group by t1.id
			ORDER BY t1.id ASC";
			$suivis=array();
			$tab=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['verrou']=WS::get_verrou("suivi/".$row['id']);
				$row['date']=0+$row['date'];
				$row['groups']= is_array(json_decode($row['groups'])) ? json_decode($row['groups']) : array();
				$suivis[$row['id']]=$row;
				$tab[]=$row['id_casquette'];
			}
			$casquettes=Contacts::get_nom_casquettes(array_unique($tab));
			foreach($suivis as $k=>$v){
				$suivis[$k]['cas']=$casquettes[$v['id_casquette']];
			}
			return $suivis;
		}
		public static function get_suivi($id_suivi,$id)
		{
			$db= new DB();
			$query = "SELECT * FROM suivis WHERE id=$id_suivi AND (
				$id=1 OR
				createdby=$id OR
				id IN (SELECT id_ressource from acl where type_ressource='suivis' AND type_acces='user' AND id_acces=$id) OR
				id IN (SELECT id_ressource from acl where type_ressource='suivis' AND type_acces='group' AND id_acces IN
					(select id_group from user_group where id_user=$id)
				)
			)
			";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['verrou']=WS::get_verrou("suivi/".$id_suivi);
				$row['acl']=User::get_acl('suivis',$id_suivi,$id);
				$row['cas']=Contacts::get_casquette($row['id_casquette'],false,$id);
				$row['date']=0+$row['date'];
				$res=$row;
			}
			return $res;
		}
		public static function get_suivis_casquette($id_cas,$id)
		{
			$db= new DB();
			$query = "SELECT * FROM suivis WHERE id_casquette=$id_cas";  
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[$row['id']]=$row;
			}
			return $res;
		}
		public static function get_casquette_suivi($id_suivi)
		{
			$db= new DB();
			$query = "SELECT id_casquette FROM suivis WHERE id=$id_suivi";
			$res=0;
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row['id'];
			}
			return $res;
		}
		public static function add_suivi($params,$id) {
			$db= new DB();
			$titre=$params->suivi->titre;
			$desc=$params->suivi->desc;
			$date=$params->suivi->date;
			$statut=$params->suivi->statut;
			$id_casquette=$params->suivi->id_casquette;
			$insert = $db->database->prepare('INSERT INTO suivis (id_casquette, titre, desc, date, statut, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?,?,?,?)');
			$insert->execute(array($id_casquette, $titre, $desc, $date, $statut, millisecondes(), $id, millisecondes(), $id));
			$id_suivi = $db->database->lastInsertId();
			if ($params->suivi->id_precedent>0) {
				$acls=User::get_acl('suivis',$params->suivi->id_precedent,$id);
				foreach($acls['all'] as $acl) {
					User::add_acl('suivis',$id_suivi,$acl['type_acces'],$acl['id_acces'],$acl['level'],$id);
				}
			}
			CR::maj(array("suivis"));
			return $id_suivi;
		}
		public static function mod_suivi($params,$id) {
			$db= new DB();
			$titre=$params->suivi->titre;
			$desc=$params->suivi->desc;
			$date=$params->suivi->date;
			$statut=$params->suivi->statut;
			$id_suivi=$params->suivi->id;
			$id_casquette=$params->suivi->id_casquette;
			$update = $db->database->prepare('UPDATE suivis SET id_casquette=?, titre=?, desc=?, date=?, statut=?, modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array($id_casquette,$titre,$desc,$date,$statut,millisecondes(),$id,$id_suivi));
			CR::maj(array("suivi/$id_suivi"));
			return 1;
		}
		public static function del_suivi($params,$id) {
			$db= new DB();
			$id_suivi=$params->id;
			$suivi=Suivis::get_suivi($id_suivi,$id);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date, by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id_suivi,'suivi',json_encode($suivi),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM suivis WHERE id=? ');
			$delete->execute(array($id_suivi));
			CR::maj(array('suivis'));
			return 1;
		}
	}	
