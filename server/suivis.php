<?php
	class Suivis
	{
		protected $WS;
		protected $from;
		public function __construct($WS,$from) {
	 	 	$this->WS= $WS;
	 	 	$this->from= $from;
		}
	 	public static function get_suivis($params,$id)
		{
			$db= new DB();

			if ($params->group==0) $cg="1";
			else $cg="t2.id IN (
				SELECT id_ressource from acl WHERE type_acces='group' AND id_acces=".$params->group."
			)";
			$tab=array();

			$t=millisecondes()-24*3600000;
			$query_count_pr = "SELECT count(*) as nb
			FROM suivis as t1
			inner join suivis_threads as t2 on t2.id=t1.id_thread
			where
			$cg AND
			t1.statut=0 AND
			t1.date>$t AND (
				$id=1 OR
				t2.createdby=$id OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='user' AND id_acces=6) OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='group' AND id_acces IN
					(select id_group from user_group where id_user=$id)
				)
			)";
			foreach($db->database->query($query_count_pr, PDO::FETCH_ASSOC) as $row){
				$total_pr=$row['nb']+0;
			}
			$query_pr = "SELECT t1.id,
			t1.id,
			t1.id_thread,
			t1.titre,
			t1.desc,
			t1.date,
			t1.statut,
			t1.creationdate,
			t1.createdby,
			t1.modificationdate,
			t1.modifiedby,
			t2.nom as nom_thread,
			t2.id_casquette as id_casquette,
			'[' || Group_Concat(DISTINCT t3.id_acces)||']' as groups
			FROM suivis as t1
			inner join suivis_threads as t2 on t2.id=t1.id_thread
			left outer join acl as t3 on t3.id_ressource=t2.id AND t3.type_ressource='suivis_threads' AND t3.type_acces='group'
			where
			$cg AND
			t1.statut=0 AND
			t1.date>$t AND (
				$id=1 OR
				t2.createdby=$id OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='user' AND id_acces=6) OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='group' AND id_acces IN
					(select id_group from user_group where id_user=$id)
				)
			)
			group by t1.id
			ORDER BY t1.date ASC
			LIMIT ".(($params->pagePr-1)*$params->nb).", ".$params->nb;
			$suivis_pr=array();
			foreach($db->database->query($query_pr, PDO::FETCH_ASSOC) as $row){
				$row['date']=0+$row['date'];
				$row['groups']= is_array(json_decode($row['groups'])) ? json_decode($row['groups']) : array();
				$suivis_pr[$row['id']]=$row;
				$tab[]=$row['id_casquette'];
			}

			$query_count_re = "SELECT count(*) as nb
			FROM suivis as t1
			inner join suivis_threads as t2 on t2.id=t1.id_thread
			where
			$cg AND
			t1.statut=0 AND
			t1.date<=$t AND (
				$id=1 OR
				t2.createdby=$id OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='user' AND id_acces=6) OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='group' AND id_acces IN
					(select id_group from user_group where id_user=$id)
				)
			)";
			foreach($db->database->query($query_count_re, PDO::FETCH_ASSOC) as $row){
				$total_re=$row['nb']+0;
			}
			$query_re = "SELECT t1.id,
			t1.id_thread,
			t1.titre,
			t1.desc,
			t1.date,
			t1.statut,
			t1.creationdate,
			t1.createdby,
			t1.modificationdate,
			t1.modifiedby,
			t2.nom as nom_thread,
			t2.id_casquette as id_casquette,
			'[' || Group_Concat(DISTINCT t3.id_acces)||']' as groups
			FROM suivis as t1
			inner join suivis_threads as t2 on t2.id=t1.id_thread
			left outer join acl as t3 on t3.id_ressource=t2.id AND t3.type_ressource='suivis_threads' AND t3.type_acces='group'
			where
			$cg AND
			t1.statut=0 AND
			t1.date<=$t AND (
				$id=1 OR
				t2.createdby=$id OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='user' AND id_acces=6) OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='group' AND id_acces IN
					(select id_group from user_group where id_user=$id)
				)
			)
			group by t1.id
			ORDER BY t1.date ASC
			LIMIT ".(($params->pageRe-1)*$params->nb).", ".$params->nb;
			$suivis_re=array();
			foreach($db->database->query($query_re, PDO::FETCH_ASSOC) as $row){
				$row['date']=0+$row['date'];
				$row['groups']= is_array(json_decode($row['groups'])) ? json_decode($row['groups']) : array();
				$suivis_re[$row['id']]=$row;
				$tab[]=$row['id_casquette'];
			}


			$query_count_te = "SELECT count(*) as nb
			FROM suivis as t1
			inner join suivis_threads as t2 on t2.id=t1.id_thread
			where
			$cg AND
			t1.statut=1 AND (
				$id=1 OR
				t2.createdby=$id OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='user' AND id_acces=6) OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='group' AND id_acces IN
					(select id_group from user_group where id_user=$id)
				)
			)";
			foreach($db->database->query($query_count_te, PDO::FETCH_ASSOC) as $row){
				$total_te=$row['nb']+0;
			}
			$query_te = "SELECT t1.id,
			t1.id_thread,
			t1.titre,
			t1.desc,
			t1.date,
			t1.statut,
			t1.creationdate,
			t1.createdby,
			t1.modificationdate,
			t1.modifiedby,
			t2.nom as nom_thread,
			t2.id_casquette as id_casquette,
			'[' || Group_Concat(DISTINCT t3.id_acces)||']' as groups
			FROM suivis as t1
			inner join suivis_threads as t2 on t2.id=t1.id_thread
			left outer join acl as t3 on t3.id_ressource=t2.id AND t3.type_ressource='suivis_threads' AND t3.type_acces='group'
			where
			$cg AND
			t1.statut=1 AND (
				$id=1 OR
				t2.createdby=$id OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='user' AND id_acces=6) OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='group' AND id_acces IN
					(select id_group from user_group where id_user=$id)
				)
			)
			group by t1.id
			ORDER BY t1.date DESC
			LIMIT ".(($params->pageTe-1)*$params->nb).", ".$params->nb;
			$suivis_te=array();
			foreach($db->database->query($query_te, PDO::FETCH_ASSOC) as $row){
				$row['date']=0+$row['date'];
				$row['groups']= is_array(json_decode($row['groups'])) ? json_decode($row['groups']) : array();
				$suivis_te[$row['id']]=$row;
				$tab[]=$row['id_casquette'];
			}
			$tab=array_unique($tab);
			$casquettes=Contacts::get_nom_casquettes(array_unique($tab));
			foreach($suivis_pr as $k=>$v){
				if (isset($casquettes[$v['id_casquette']])) {
					$suivis_pr[$k]['cas']=$casquettes[$v['id_casquette']];
				} else {
					$suivis_pr[$k]['cas']=array('id'=>$v['id_casquette'],'nom'=>'Contact supprimé','prenom'=>'','type'=>'1');
				}
			}
			foreach($suivis_re as $k=>$v){
				if (isset($casquettes[$v['id_casquette']])) {
					$suivis_re[$k]['cas']=$casquettes[$v['id_casquette']];
				} else {
					$suivis_re[$k]['cas']=array('id'=>$v['id_casquette'],'nom'=>'Contact supprimé','prenom'=>'','type'=>'1');
				}
			}
			foreach($suivis_te as $k=>$v){
				if (isset($casquettes[$v['id_casquette']])) {
					$suivis_te[$k]['cas']=$casquettes[$v['id_casquette']];
				} else {
					$suivis_te[$k]['cas']=array('id'=>$v['id_casquette'],'nom'=>'Contact supprimé','prenom'=>'','type'=>'1');
				}
			}
			return array('params'=>$params,'collection'=>array(
				'prochains'=>array('suivis'=>$suivis_pr,'page'=>$params->pagePr, 'nb'=>$params->nb, 'total'=>$total_pr),
				'retards'=>array('suivis'=>$suivis_re,'page'=>$params->pageRe, 'nb'=>$params->nb, 'total'=>$total_re),
				'termines'=>array('suivis'=>$suivis_te,'page'=>$params->pageTe, 'nb'=>$params->nb, 'total'=>$total_te)
			));
		}
		public static function get_suivi($id_suivi,$id)
		{
			$db= new DB();
			$query = "SELECT t1.*, t2.nom, t2.id_casquette
				FROM suivis as t1
				inner join suivis_threads as t2 on t2.id=t1.id_thread
				WHERE t1.id=$id_suivi AND (
				$id=1 OR
				t2.createdby=$id OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='user' AND id_acces=$id) OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='group' AND id_acces IN
					(select id_group from user_group where id_user=$id)
				)
			)
			";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['cas']=Contacts::get_casquette($row['id_casquette'],true,$id);
				$row['date']=0+$row['date'];
				$res=$row;
			}
			return $res;
		}
		public static function get_suivis_casquette($id_cas,$id=1)
		{
			$db= new DB();
			$query = "SELECT t1.*,
			t2.id as id_thread,
			t2.id_casquette as id_casquette,
			t2.nom as nom_thread,
			t2.desc as desc_thread,
			t2.createdby as createdby_thread,
			t2.creationdate as creationdate_thread,
			t2.modifiedby as modifiedby_thread,
			t2.modificationdate as modificationdate_thread,
			'[' || Group_Concat(DISTINCT t3.id_acces)||']' as groups
			FROM suivis_threads as t2
			left outer join suivis as t1 on t2.id=t1.id_thread
			left outer join acl as t3 on t3.id_ressource=t2.id AND t3.type_ressource='suivis_threads' AND t3.type_acces='group'
			where
			t2.id_casquette=$id_cas AND (
				$id=1 OR
				t2.createdby=$id OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='user' AND id_acces=6) OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='group' AND id_acces IN
						(select id_group from user_group where id_user=$id)
					)
			)
			group by t1.id
			ORDER BY t1.id ASC";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				if (!array_key_exists($row['id_thread'],$res)) $res[$row['id_thread']]=array(
					'nom'=>$row['nom_thread'],
					'desc'=>$row['desc_thread'],
					'id'=>$row['id_thread'],
					'id_casquette'=>$row['id_casquette'],
					'groups'=>json_decode($row['groups']),
					'createdby'=>$row['createdby_thread'],
					'creationdate'=>$row['creationdate_thread'],
					'modifiedby'=>$row['modifiedby_thread'],
					'modificationdate'=>$row['modificationdate_thread'],
					'suivis'=>array()
				);
				if ($row['id']>0) $res[$row['id_thread']]['suivis'][$row['id']]=$row;
			}
			return $res;
		}
		public static function get_suivis_thread($id_thread,$id=1)
		{
			$db= new DB();
			$query = "SELECT t1.*,
			t2.id as id_thread,
			t2.id_casquette as id_casquette,
			t2.nom as nom_thread,
			t2.desc as desc_thread,
			t2.createdby as createdby_thread,
			t2.creationdate as creationdate_thread,
			t2.modifiedby as modifiedby_thread,
			t2.modificationdate as modificationdate_thread
			FROM suivis_threads as t2
			left outer join suivis as t1 on t2.id=t1.id_thread
			where
			t2.id=$id_thread AND (
				$id=1 OR
				t2.createdby=$id OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='user' AND id_acces=6) OR
				t2.id IN (SELECT id_ressource from acl where type_ressource='suivis_threads' AND type_acces='group' AND id_acces IN
						(select id_group from user_group where id_user=$id)
					)
			)
			group by t1.id
			ORDER BY t1.id ASC";
			$res=array();
			$res['collection']=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res['id']=$row['id_thread'];
				$res['id_casquette']=$row['id_thread'];
				$res['nom']=$row['nom_thread'];
				$res['desc']=$row['desc_thread'];
				$res['createdby']=$row['createdby_thread'];
				$res['creationdate']=$row['creationdate_thread'];
				$res['modifiedby']=$row['modifiedby_thread'];
				$res['modificationdate']=$row['modificationdate_thread'];
				$res['collection'][$row['id']]=$row;
			}
			$res['acl']=User::get_acl('suivis_threads',$id_thread,$id);
			return $res;
		}
		public function add_suivi($params,$id) {
			$t=Suivis::do_add_suivi($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_suivi($params,$id) {
			$db= new DB();
			$titre=$params->suivi->titre;
			$desc=$params->suivi->desc;
			$date=$params->suivi->date;
			$statut=$params->suivi->statut;
			$id_thread=$params->suivi->id_thread;
			$insert = $db->database->prepare('INSERT INTO suivis (id_thread, titre, desc, date, statut, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?,?,?,?)');
			$insert->execute(array($id_thread, $titre, $desc, $date, $statut, millisecondes(), $id, millisecondes(), $id));
			$id_suivi = $db->database->lastInsertId();
			Suivis::touch_suivis_thread($id_thread,$id);
			$cas=Contacts::get_casquette_thread($id_thread,$id);
			return array('maj'=>array("suivis","suivis_thread/$id_thread","contact/".$cas['id_contact']), 'res'=>$id_suivi);
		}
		public function add_suivis_thread($params,$id) {
			$t=Suivis::do_add_suivis_thread($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_suivis_thread($params,$id) {
			$db= new DB();
			$nom=$params->suivis_thread->nom;
			$desc=$params->suivis_thread->desc;
			$id_casquette=$params->suivis_thread->id_casquette;
			$insert = $db->database->prepare('INSERT INTO suivis_threads (id_casquette, nom, desc, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?,?)');
			$insert->execute(array($id_casquette, $nom, $desc, millisecondes(), $id, millisecondes(), $id));
			$id_suivis_thread = $db->database->lastInsertId();
			$acl_maj=array();
			$cas=Contacts::get_casquette_thread($id_suivis_thread,$id);
			return array('maj'=>array_merge(array("suivis","suivis_thread/".$id_suivis_thread,"contact/".$cas['id_contact']),$acl_maj), 'res'=>$id_suivis_thread);
		}
		public static function touch_suivis_thread($id_thread,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE suivis_threads SET modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array(millisecondes(),$id,$id_thread));
			return 1;
		}
		public function mod_suivi($params,$id) {
			$t=Suivis::do_mod_suivi($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_suivi($params,$id) {
			$db= new DB();
			$titre=$params->suivi->titre;
			$desc=$params->suivi->desc;
			$date=$params->suivi->date;
			$statut=$params->suivi->statut;
			$id_suivi=$params->suivi->id;
			$id_thread=$params->suivi->id_thread;
			$update = $db->database->prepare('UPDATE suivis SET titre=?, desc=?, date=?, statut=?, modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array($titre,$desc,$date,$statut,millisecondes(),$id,$id_suivi));
			Suivis::touch_suivis_thread($id_thread,$id);
			$cas=Contacts::get_casquette_thread($id_thread,$id);
			return array('maj'=>array("suivi/$id_suivi","suivis_thread/$id_thread","contact/".$cas['id_contact']), 'res'=>1);
		}
		public function mod_suivis_thread($params,$id) {
			$t=Suivis::do_mod_suivis_thread($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_suivis_thread($params,$id) {
			$db= new DB();
			$nom=$params->suivis_thread->nom;
			$desc=$params->suivis_thread->desc;
			$id_casquette=$params->suivis_thread->id_casquette;
			$id_suivis_thread=$params->suivis_thread->id;
			error_log(var_export($params,true),3,"/tmp/fab.log");
			$update = $db->database->prepare('UPDATE suivis_threads SET nom=?, desc=?, id_casquette=?, modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array($nom,$desc,$id_casquette,millisecondes(),$id,$id_suivis_thread));
			$cas=Contacts::get_casquette_thread($id_suivis_thread,$id);
			return array('maj'=>array("suivis_thread/$id_suivis_thread","contact/".$cas['id_contact']), 'res'=>1);
		}
		public function move_suivis_casquette($params,$id) {
			$t=Suivis::do_move_suivis_casquette($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_move_suivis_casquette($params,$id) {
			$db= new DB();
			$sid=$params->s->id;
			$did=$params->d->id;
			$update = $db->database->prepare('UPDATE suivis_threads SET id_casquette=? WHERE id_casquette=?');
			$update->execute(array($did,$sid));
			$s=Contacts::get_casquette($sid,false,$id);
			$d=Contacts::get_casquette($did,false,$id);
			return array('maj'=>array("suivis","suivis_thread/*","contact/".$s['id_contact'],"contact/".$d['id_contact']), 'res'=>1);
		}
		public function del_suivi($params,$id) {
			$t=Suivis::do_del_suivi($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_suivi($params,$id) {
			$db= new DB();
			$id_suivi=$params->id;
			$suivi=Suivis::get_suivi($id_suivi,$id);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date, by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id_suivi,'suivi',json_encode($suivi),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM suivis WHERE id=? ');
			$delete->execute(array($id_suivi));
			$suivis_thread=Suivis::get_suivis_thread($suivi['id_thread'],$id);
			$ids=0;
			$date=0;
			foreach($suivis_thread['collection'] as $k=>$s) {
				$date=max($s['date'],$date);
				if ($s['date']==$date) $ids=$s['id'];
			}
			return array('maj'=>array("suivis","suivis_thread/*"), 'res'=>$ids);
		}
		public function del_suivis_thread($params,$id) {
			$t=Suivis::do_del_suivis_thread($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_suivis_thread($params,$id) {
			$db= new DB();
			$id_suivis_thread=$params->id;
			$cas=Contacts::get_casquette_thread($id_suivis_thread,$id);
			$suivis_thread=Suivis::get_suivis_thread($id_suivis_thread,$id);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date, by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id_suivis_thread,'suivis_threads',json_encode($suivis_thread),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM suivis_threads WHERE id=? ');
			$delete->execute(array($id_suivis_thread));
			$delete = $db->database->prepare('DELETE FROM suivis WHERE id_thread=? ');
			$delete->execute(array($id_suivis_thread));
			return array('maj'=>array("suivis","contact/".$cas['id_contact']), 'res'=>1);
		}
	}
