<?php
	class Publipostage
	{
		//supports
		public static function get_support($id) {
			$db= new DB();
			$query = "SELECT * FROM supports WHERE id=$id";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['verrou']=WS::get_verrou('support/'.$row['id']);
                $res[]=$row;
			}
			return $res[0];
		}
		public static function get_supports() {
			$db= new DB();
			$query = "SELECT * FROM supports ORDER BY id ASC";
			$supports=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['verrou']=WS::get_verrou('support/'.$row['id']);
                $supports[$row['id']]=$row;
			}
			return $supports;
		}
		public static function add_support($params,$id) {
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
			CR::maj(array("supports"));
		    return $id_support;
		}
		public static function mod_support($params,$id) {
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
			CR::maj(array("support/".$params->support->id));
		    return Publipostage::get_support($params->support->id);
		}
		public static function del_support($params,$id) {
			$db= new DB();
			$id_support=$params->support->id;
			$support=Publipostage::get_support($id_support);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id,'support',json_encode($support),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM supports WHERE id=?');
			$delete->execute(array($id_support));
		    CR::maj(array("supports"));
		}
	}
?>
