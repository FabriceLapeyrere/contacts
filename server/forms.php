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
			$query = "SELECT *, (SELECT count(*)>0 FROM form_instances WHERE id_form=$id_form) as has_instance, (SELECT max(modificationdate) FROM forms_data WHERE hash IN (SELECT hash FROM form_instances WHERE id_form=$id_form)) as i_modificationdate FROM forms as t1 WHERE id=$id_form";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['schema']=json_decode($row['schema']);
				$row['from_date']=$row['from_date']+0;
				$row['to_date']=$row['to_date']+0;
				$row['docs']=array();
				foreach (glob("./data/files/form/$id_form/*.odt") as $f)
				{
					$row['docs'][]=array('nom'=>basename($f),'path'=>$f,'modificationdate'=>(filemtime($f)*1000)."");
				}
				foreach (glob("./data/files/form/$id_form/*.pdf") as $f)
				{
					$row['docs'][]=array('nom'=>basename($f),'path'=>$f,'modificationdate'=>(filemtime($f)*1000)."");
				}
				$res[]=$row;
			}

			return $res[0];
		}
		public static function get_id_schema_idx($id_form,$idx,$id){
			$form=Forms::get_form($id_form,$id);
			$i=1;
			foreach($form['schema']->pages as $p){
				foreach($p->elts as $e){
					if ($idx==$i) return $e->id;
					$i++;
				}
			}
		}
		public static function get_form_instance($hash,$id,$form=true) {
			$res=Forms::get_form_instances(array($hash),$id,$form);
			return $res[$hash];
		}
		public static function get_form_instances($hashs,$id,$form=true) {
			$db= new DB();
			foreach ($hashs as $key => $value) {
				$hashs[$key]="'".$value."'";
			}
			$query = "SELECT
				t1.hash as main_hash,
				t1.id_form as main_id_form,
				t1.id_lien as main_id_lien,
				t1.type_lien as main_type_lien,
				t1.state as state,
				t2.*,
				t21.schema as schema,
				t21.nom as nom_form,
				t3.id_contact as id_contact,
				t3.donnees as donnees,
				t4.nom as nom_contact,
				t4.prenom as prenom_contact,
				t31.donnees as donnees_etab,
				t41.nom as nom_etab
				FROM form_instances as t1
					left join forms_data as t2 on t1.hash=t2.hash
					left join forms as t21 on t1.id_form=t21.id
					left join casquettes as t3 on t1.type_lien='casquette' AND t1.id_lien=t3.id
					left join casquettes as t31 on t31.id=t3.id_etab
					left join contacts as t4 on t3.id_contact=t4.id
					left join contacts as t41 on t31.id_contact=t41.id
					where t1.hash IN (".implode(',',$hashs).")";
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$I=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				if (!array_key_exists($row['main_hash'],$I)){
					$I[$row['main_hash']]=array('collection'=>array());
					$I[$row['main_hash']]['hash']=$row['main_hash'];
					$I[$row['main_hash']]['id_form']=$row['main_id_form'];
					$I[$row['main_hash']]['nom_form']=$row['nom_form'];
					$I[$row['main_hash']]['id_lien']=$row['main_id_lien'];
					$I[$row['main_hash']]['type_lien']=$row['main_type_lien'];
					$I[$row['main_hash']]['id_contact']=$row['id_contact'];
					$I[$row['main_hash']]['nom_contact']=$row['nom_contact'];
					$I[$row['main_hash']]['prenom_contact']=$row['prenom_contact'];
					$I[$row['main_hash']]['donnees']=json_decode($row['donnees']);
					$I[$row['main_hash']]['nom_etab']=$row['nom_etab'];
					$I[$row['main_hash']]['donnees_etab']=json_decode($row['donnees_etab']);
					$I[$row['main_hash']]['state']=$row['state'];
					$I[$row['main_hash']]['schema']=json_decode($row['schema']);
				}
				if ($row['type']!=''){
					 if ($row['type']=='upload') $row['valeur']=json_decode($row['valeur']);
					 $I[$row['main_hash']]['collection'][$row['id_schema']]=array(
					 	"type"=>$row['type'],
						"valeur"=>$row['valeur'],
						"modificationdate"=>$row['modificationdate'],
						"modifiedby"=>$row['modifiedby']
					);
				}
			};
			foreach ($I as $h => $instance) {
				$md=0;
				foreach ($I[$h]['collection'] as $k => $v) {
					$md=max($md,$v['modificationdate']);
				}
				$I[$h]['modificationdate']=$md;
				$I[$h]['collection']=(object)$I[$h]['collection'];
				$I[$h]['docs']=array();
				foreach (glob("./data/files/form_upload/$h/*.odt") as $f)
				{
					$I[$h]['docs'][]=array('nom'=>basename($f),'path'=>$f,'modificationdate'=>(filemtime($f)*1000)."");
				}
				foreach (glob("./data/files/form_upload/$h/*.pdf") as $f)
				{
					$I[$h]['docs'][]=array('nom'=>basename($f),'path'=>$f,'modificationdate'=>(filemtime($f)*1000)."");
				}
				if ($form) $I[$h]['form']=Forms::get_form($I[$h]['id_form'],$id);
			}
			return $I;
		}
		public function close_form_instance($params,$id) {
			$t=Forms::do_close_form_instance($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_close_form_instance($params,$id) {
			$C=Config::get();
			$hash=$params->hash;
			$db= new DB();
			$update= $db->database->prepare('UPDATE form_instances SET state=? WHERE hash=?');
			$update->execute(array(
				'closed',
				$hash
				)
			);
			$db_instance=Forms::get_form_instance($hash,$id);
			$maj=array("casquettes");
			$maj[]="form_instance/".$hash;
			$maj[]="form_instances_form/".$db_instance['id_form'];
			if ($db_instance['type_lien']=='casquette') {
				$maj[]="form_instances_cas_form/".$db_instance['id_lien']."/".$db_instance['id_form'];
			}
			$p=new stdClass;
			$p->hash=$hash;
			$generate_res=Forms::do_generate_docs($p,$id);
			$docs=$generate_res['res'];
			$message="Le formulaire a été validé : \nodt -> ".$C->app->url->value."/".str_replace('./','',$docs['odt'])."\npdf -> ".$C->app->url->value."/".str_replace('./','',$docs['pdf']);
			foreach(explode(",",$C->app->mails_notification->value) as $dest){
				mail_utf8(trim($dest),"Formulaire validé ".$db_instance['form']['nom'],$message,'From: '.$C->app->mails_notification_from->value);
			}
			return array('maj'=>$maj,'res'=>1);
		}
		public function open_form_instance($params,$id) {
			$t=Forms::do_open_form_instance($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_open_form_instance($params,$id) {
			$hash=$params->hash;
			$db= new DB();
			$update= $db->database->prepare('UPDATE form_instances SET state=? WHERE hash=?');
			$update->execute(array(
				'open',
				$hash,
				)
			);
			$db_instance=Forms::get_form_instance($hash,$id);
			$maj=array("casquettes");
			$maj[]="form_instance/".$hash;
			$maj[]="form_instances_form/".$db_instance['id_form'];
			if ($db_instance['type_lien']=='casquette') {
				$maj[]="form_instances_cas_form/".$db_instance['id_lien']."/".$db_instance['id_form'];
			}
			return array('maj'=>$maj,'res'=>1);
		}
		public function mod_form_instance($params,$id) {
			$t=Forms::do_mod_form_instance($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_form_instance($params,$id) {
			$db_instance=Forms::get_form_instance($params->instance->hash,$id);
			$new=array();
			$mod=array();
			//error_log(var_export($params->instance,true),3,"/tmp/fab.log");
			foreach($params->instance->collection as $k=>$e){
				$e->id_schema=$k;
				$test=false;
				foreach($db_instance['collection'] as $oe){
					if ($e->id_schema==$oe['id_schema']) {
						$test=true;
						if ($e->valeur!=$oe['valeur']) $mod[]=$e;
					}
				}
				if (!$test) $new[]=$e;
			}
			//error_log(var_export($new,true),3,"/tmp/fab.log");
			//error_log(var_export($mod,true),3,"/tmp/fab.log");
			$maj=array();
			if ($db_instance['type_lien']=='casquette'){
				foreach($params->instance->collection as $k=>$e) {
					if ($e->type=='tag'){
						$id_tag=0;
						foreach ($db_instance['form']['schema']->pages as $page) {
							foreach ($page->elts as $elt) {
								if ($elt->id==$k) $id_tag=$elt->idTag;
							}
						}
						$p= new stdClass;
						$p->tag=new stdClass;
						$p->tag->id=$id_tag;
						$p->cas=new stdClass;
						$p->cas->id=$db_instance['id_lien'];
						$p->cas->id_contact=$db_instance['id_contact'];
						if ($e->valeur==1) {
							$c=Contacts::do_add_cas_tag($p,$id);
							$maj=array_merge($maj,$c['maj']);
						} else {
							$c=Contacts::do_del_cas_tag($p,$id);
							$maj=array_merge($maj,$c['maj']);
						}
					}
				}
			}
			$db= new DB();
			$db->database->beginTransaction();
			foreach($new as $n) {
				if(is_array($n->valeur)) $n->valeur=json_encode($n->valeur);
				$insert= $db->database->prepare('INSERT INTO forms_data (hash, id_schema, type, valeur, modificationdate, modifiedby, creationdate, createdby) VALUES (?,?,?,?,?,?,?,?)');
				$insert->execute(array(
					$params->instance->hash,
					$n->id_schema,
					$n->type,
					$n->valeur,
					millisecondes(),
					$id,
					millisecondes(),
					$id
					)
				);
			}
			foreach($mod as $m) {
				if(is_array($m->valeur)) $m->valeur=json_encode($m->valeur);
				$update= $db->database->prepare('UPDATE forms_data SET valeur=?, modificationdate=?, modifiedby=? WHERE hash=? AND id_schema=?');
				$update->execute(array(
					$m->valeur,
					millisecondes(),
					$id,
					$params->instance->hash,
					$m->id_schema,
					)
				);
			}
			$db->database->commit();
			$maj=array("casquettes");
			$maj[]="form/".$db_instance['id_form'];
			$maj[]="form_instance/".$params->instance->hash;
			$maj[]="form_instances_form/".$db_instance['id_form'];
			if ($db_instance['type_lien']=='casquette') {
				$maj[]="form_instances_cas_form/".$db_instance['id_lien']."/".$db_instance['id_form'];
			}
			return array('maj'=>$maj,'res'=>1);
		}
		public static function do_add_form_file($params,$id) {
			$t=millisecondes();
			$file=$params->file;
			$id_elt=$params->id_elt;
			$hash=$params->hash;
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$f=array(
				"modificationdate"=>$t,
				"modifiedby"=>$id,
				"nom"=>basename($file),
				"url"=>"data/files/form_upload/$hash/$id_elt/".basename($file),
				"mime"=>finfo_file($finfo, $file)
			);
			$db= new DB();
			$query = "select t1.*, t2.id_form as id_form, t2.type_lien as type_lien, t2.id_lien as id_lien from forms_data as t1 inner join form_instances as t2 on t1.hash=t2.hash WHERE t1.hash='$hash' and t1.id_schema='$id_elt'";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row;
			}
			if (count($res)>0) {
				$v=json_decode($res['valeur']);
				if (!is_array($v)) $v=array();
				$v[]=$f;
				$update= $db->database->prepare('UPDATE forms_data SET valeur=?, modificationdate=?, modifiedby=? WHERE hash=? AND id_schema=?');
				$update->execute(array(
					json_encode($v),
					millisecondes(),
					$id,
					$hash,
					$id_elt
					)
				);
			}
			$maj=array("casquettes");
			$maj[]="form_instance/".$params->instance->hash;
			if ($res['type_lien']=='casquette') {
				$maj[]="form_instances_cas_form/".$res['id_lien']."/".$res['id_form'];
			}
			return array('maj'=>$maj,'res'=>1);
		}
		public function del_form_file($params,$id) {
			$t=Forms::do_del_form_file($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_form_file($params,$id) {
			$file=$params->file;
			$id_elt=$params->id_elt;
			$hash=$params->hash;
			$db= new DB();
			$query = "select t1.*, t2.id_form as id_form, t2.type_lien as type_lien, t2.id_lien as id_lien from forms_data as t1 inner join form_instances as t2 on t1.hash=t2.hash WHERE t1.hash='$hash' and t1.id_schema='$id_elt'";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row;
			}
			if (count($res)>0) {
				unlink("./data/files/form_upload/$hash/$id_elt/$file");
				$v=json_decode($res['valeur']);
				$nv=array();
				foreach ($v as $key => $value) {
					if ($value->nom!=$file) $nv[]=$value;
				}
				$update= $db->database->prepare('UPDATE forms_data SET valeur=?, modificationdate=?, modifiedby=? WHERE hash=? AND id_schema=?');
				$update->execute(array(
					json_encode($nv),
					millisecondes(),
					$id,
					$hash,
					$id_elt
					)
				);
			}
			$maj=array("casquettes");
			$maj[]="form_instance/".$hash;
			if ($res['type_lien']=='casquette') $maj[]="form_instances_cas_form/".$res['id_lien']."/".$res['id_form'];
			return array('maj'=>$maj,'res'=>1);
		}
		public static function get_forms() {
			$db= new DB();
			$query = "SELECT *, (SELECT count(*)>0 FROM form_instances WHERE id_form=t1.id) as has_instance FROM forms as t1 ORDER BY id ASC";
			$forms=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['schema']=json_decode($row['schema']);
				$forms[$row['id']]=$row;
			}
			return $forms;
		}
		public static function get_form_instances_cas($id_cas, $id) {
			$db= new DB();
			$query = "SELECT id_form, hash FROM form_instances WHERE type_lien='casquette' AND id_lien=$id_cas ORDER BY id_form ASC;";
			$forms=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$forms[]=$row;
			}
			return $forms;
		}
		public static function get_form_instances_cas_form($id_cas, $id_form, $id) {
			$db= new DB();
			$query = "SELECT hash FROM form_instances WHERE type_lien='casquette' AND id_lien=$id_cas AND id_form=$id_form ORDER BY id_form ASC;";
			$instances=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$instances[]=$row['hash'];
			}
			$res=array();
			foreach ($instances as $i) {
				$res[$i]=Forms::get_form_instance($i,$id,false);
			}
			return $res;
		}
		public static function get_form_instances_form($id_form, $params, $id) {
			$db= new DB();
			$page=$params->pageTout;
			$first_tout=($params->pageTout-1)*$params->nb;
			$first_ok=($params->pageOk-1)*$params->nb;
			$first_encours=($params->pageEncours-1)*$params->nb;
			$nb=$params->nb;
			//tout
			$query_tout = "SELECT count(*) as nb FROM form_instances as t1 left join casquettes as t2 on t1.type_lien='casquette' AND t1.id_lien=t2.id left join contacts as t3 on t2.id_contact=t3.id WHERE id_form=$id_form ORDER BY id_form ASC;";
			$total_tout=0;
			foreach($db->database->query($query_tout, PDO::FETCH_ASSOC) as $row){
				$total_tout=$row['nb'];
			}
			$query_tout = "SELECT t1.id_form, t1.hash, t1.type_lien, t1.id_lien, t1.state, t3.nom, t3.prenom, t3.type FROM form_instances as t1 left join casquettes as t2 on t1.type_lien='casquette' AND t1.id_lien=t2.id left join contacts as t3 on t2.id_contact=t3.id WHERE id_form=$id_form ORDER BY id_form ASC LIMIT $first_tout, $nb;";
			$forms_tout=array();
			foreach($db->database->query($query_tout, PDO::FETCH_ASSOC) as $row){
				$forms_tout[]=$row;
			}
			$res_tout=array('collection'=>$forms_tout,'total'=>$total_tout);
			//ok
			$query_ok = "SELECT count(*) as nb FROM form_instances as t1 left join casquettes as t2 on t1.type_lien='casquette' AND t1.id_lien=t2.id left join contacts as t3 on t2.id_contact=t3.id WHERE id_form=$id_form AND state='closed' ORDER BY id_form ASC;";
			$total_ok=0;
			foreach($db->database->query($query_ok, PDO::FETCH_ASSOC) as $row){
				$total_ok=$row['nb'];
			}
			$query_ok = "SELECT t1.id_form, t1.hash, t1.type_lien, t1.id_lien, t1.state, t3.nom, t3.prenom, t3.type FROM form_instances as t1 left join casquettes as t2 on t1.type_lien='casquette' AND t1.id_lien=t2.id left join contacts as t3 on t2.id_contact=t3.id WHERE id_form=$id_form AND state='closed' ORDER BY id_form ASC LIMIT $first_ok, $nb;";
			$forms_ok=array();
			foreach($db->database->query($query_ok, PDO::FETCH_ASSOC) as $row){
				$forms_ok[]=$row;
			}
			//encours
			$query_encours = "SELECT count(*) as nb FROM form_instances as t1 left join casquettes as t2 on t1.type_lien='casquette' AND t1.id_lien=t2.id left join contacts as t3 on t2.id_contact=t3.id WHERE id_form=$id_form AND state='open' ORDER BY id_form ASC;";
			$total_encours=0;
			foreach($db->database->query($query_encours, PDO::FETCH_ASSOC) as $row){
				$total_encours=$row['nb'];
			}
			$query_encours = "SELECT t1.id_form, t1.hash, t1.type_lien, t1.id_lien, t1.state, t3.nom, t3.prenom, t3.type FROM form_instances as t1 left join casquettes as t2 on t1.type_lien='casquette' AND t1.id_lien=t2.id left join contacts as t3 on t2.id_contact=t3.id WHERE id_form=$id_form AND state='open' ORDER BY id_form ASC LIMIT $first_encours, $nb;";
			$forms_encours=array();
			foreach($db->database->query($query_encours, PDO::FETCH_ASSOC) as $row){
				$forms_encours[]=$row;
			}
			$res_tout=array('collection'=>$forms_tout,'total'=>$total_tout);
			$res_ok=array('collection'=>$forms_ok,'total'=>$total_ok);
			$res_encours=array('collection'=>$forms_encours,'total'=>$total_encours);
			return array(
				'params'=>$params,
				'tout'=>$res_tout,
				'encours'=>$res_encours,
				'ok'=>$res_ok
			);
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
				state=?,
				from_date=?,
				to_date=?,
				modificationdate=?,
				modifiedby=?
				WHERE id=?');
			$update->execute(array(
				$params->form->nom,
				json_encode($params->form->schema),
				$params->form->state,
				$params->form->from_date,
				$params->form->to_date,
				millisecondes(),
				$id,
				$params->form->id
				)
			);
			return array('maj'=>array("form/".$params->form->id),'res'=>Forms::get_form($params->form->id,$id));
		}
		public function del_form($params,$id) {
			$t=Forms::do_del_form($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_form($params,$id) {
			$db= new DB();
			$id_form=$params->form->id;
			$form=Forms::get_form($id_form,$id);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id,'form',json_encode($form),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM forms WHERE id=?');
			$delete->execute(array($id_form));
			return array('maj'=>array('forms'), 'res'=>1);
		}
		public function add_form_instance_cas($params,$id) {
			$t=Forms::do_add_form_instance_cas($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_form_instance_cas($params,$id) {
			$db= new DB();
			$cas=Contacts::get_casquette($params->id_cas,false,$id);
			$hash=md5(rand(0,10000)."-".millisecondes()."-".$params->id_cas."-".$params->id_form);
			$insert= $db->database->prepare('INSERT INTO form_instances (id_form,type_lien,id_lien,hash) VALUES (?,?,?,?)');
			$insert->execute(array($params->id_form,'casquette',$params->id_cas,$hash));
			return array('maj'=>array("form_instances_cas_form/".$params->id_cas."/".$params->id_form,"contact/".$cas['id_contact'],"form/".$params->id_form,"form_instances_form/".$params->id_form),'res'=>$hash);
		}
		public function del_form_instance($params,$id) {
			$t=Forms::do_del_form_instance($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_form_instance($params,$id) {
			$hash=$params->hash;
			$db= new DB();
			$query = "SELECT id_lien, id_form from form_instances WHERE type_lien='casquette' AND hash='$hash'";
			$id_cas=0;
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$id_cas=$row['id_lien'];
				$id_form=$row['id_form'];
			}
			if ($id_cas>0) {
				$cas=Contacts::get_casquette($id_cas,false,$id);
			}
			$instance=Forms::get_form_instance($hash,$id);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($hash,'form_instance',json_encode($instance),millisecondes(),$id));
			$delete= $db->database->prepare('DELETE FROM form_instances WHERE hash=?');
			$delete->execute(array($hash));
			$delete= $db->database->prepare('DELETE FROM forms_data WHERE hash=?');
			$delete->execute(array($hash));
			$tab=array("form_instances_form/".$id_form);
			if ($id_cas>0) {
				$tab[]="contact/".$cas['id_contact'];
				$tab[]="form_instances_cas_form/".$id_cas."/".$id_form;
			}
			return array('maj'=>$tab,'res'=>1);
		}
		public function associer($params,$id) {
			$t=Forms::do_associer($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_associer($params,$id){
			$db= new DB();
			$selection=array();
			$query=$params->res->query;
			$casquettes=Contacts::get_casquettes(array('query'=>$query,'page'=>1,'nb'=>10,'all'=>1),0,$id);
			$selection=$casquettes['collection'];
			$db->database->beginTransaction();
			foreach($selection as $c){
				$hash=md5(rand(0,10000)."-".millisecondes()."-".$c['id']."-".$params->form->id);
				$insert = $db->database->prepare('INSERT INTO form_instances (id_form,type_lien,id_lien,hash) VALUES (?,?,?,?) WHERE 0=(SELECT count(*) FROM form_instances WHERE id_lien=? AND type_lien=?)');
				$insert->execute(array($params->form->id,'casquette',$c['id'],$hash,$c['id'],'casquette'));
			}
			$db->database->commit();
			return array('maj'=>array("contact/*","form/".$params->id_form,"form_casquettes/".$params->id_form,"form_instances_cas_form/*"),'res'=>1);
		}
		public function generate_docs($params,$id) {
			$t=Forms::do_generate_docs($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_generate_docs($params,$id){
			$hash=$params->hash;
			include_once('./server/lib/tbs_class.php');
			include_once('./server/lib/tbs_plugin_opentbs.php');
			set_time_limit(0);
			$template="./templates/form_tpl.odt";
			$instance=Forms::get_form_instance($hash,$id);
			if (!file_exists("./data/files/form_upload/$hash/")) mkdir("./data/files/form_upload/$hash/", 0777, true);
			if (file_exists("./data/formulaires/".$instance['form']['id']."/form_tpl.odt")) $template="./data/formulaires/".$instance['form']['id']."/form_tpl.odt";

			foreach (glob("./data/files/form_upload/$hash/*.odt") as $f)
			{
				unlink($f);
			}
			foreach (glob("./data/files/form_upload/$hash/*.pdf") as $f)
			{
				unlink($f);
			}
			$instances=array($instance);
			$TBS=Forms::tbs_instance($template,$instances,$id);
			$contact=Contacts::get_contact($instance['id_contact'],true,$id);
			$t=millisecondes();
			mkdir("/tmp/LibO_Conversion-$hash-$t");
			$filename="form-".$instance['form']['id']."-".$instance['id_contact']."-".filter2($contact['nom']);
			$TBS->Show(OPENTBS_FILE, "./data/files/form_upload/$hash/$filename.odt");
			exec("libreoffice -env:UserInstallation=\"file:///tmp/LibO_Conversion-$hash-$t\" --headless --invisible --convert-to pdf ./data/files/form_upload/$hash/$filename.odt --outdir ./data/files/form_upload/$hash/");
			deleteDirectory("/tmp/LibO_Conversion-$hash-$t");
			return array('res'=>array('odt'=>"./data/files/form_upload/$hash/$filename.odt",'pdf'=>"./data/files/form_upload/$hash/$filename.pdf"),'maj'=>array("form_instance/".$hash));
		}
		public function generate_docs_liste($params,$id) {
			$t=Forms::do_generate_docs_liste($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_generate_docs_liste($params,$id){
			$db= new DB();
			$id_form=$params->id_form;
			set_time_limit(0);
			$template="./templates/form_tpl.odt";
			if (file_exists("./data/formulaires/$id_form/form_tpl.odt")) $template="./data/formulaires/$id_form/form_tpl.odt";
			if (!file_exists("./data/files/form/$id_form/")) mkdir("./data/files/form/$id_form/", 0777, true);

			$query_ok = "SELECT hash FROM form_instances WHERE id_form=$id_form AND state='closed' ORDER BY id_form;";
			$hashs=array();
			foreach($db->database->query($query_ok, PDO::FETCH_ASSOC) as $row){
				$hashs[]=$row['hash'];
			}
			foreach (glob("./data/files/form/$id_form/*.odt") as $f)
			{
				unlink($f);
			}
			foreach (glob("./data/files/form/$id_form/*.pdf") as $f)
			{
				unlink($f);
			}
			$instances=Forms::get_form_instances($hashs,$id,false);
			$TBS=Forms::tbs_instance($template,$instances,$id);
			$t=millisecondes();
			mkdir("/tmp/LibO_Conversion-$id_form-$t");
			$filename="formulaires-$id_form";
			$TBS->Show(OPENTBS_FILE, "./data/files/form/$id_form/$filename.odt");
			exec("libreoffice -env:UserInstallation=\"file:///tmp/LibO_Conversion-$id_form-$t\" --headless --invisible --convert-to pdf ./data/files/form/$id_form/$filename.odt --outdir ./data/files/form/$id_form/");
			deleteDirectory("/tmp/LibO_Conversion-$id_form-$t");
			return array('res'=>array('odt'=>"./data/files/form/$id_form/$filename.odt",'pdf'=>"./data/files/form/$id_form/$filename.pdf"),'maj'=>array("form/".$id_form));
		}
		public static function tbs_instance($template,$instances,$id){
			include_once('./server/lib/tbs_class.php');
			include_once('./server/lib/tbs_plugin_opentbs.php');
			$TBS = new clsTinyButStrong;
			$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
			$TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);
			$TBS->SetOption('noerr', false);
			$I=array();
			foreach ($instances as $key => $instance) {
				$donnees=array();
				$donnees_etab=array();
				if (is_array($instance['donnees'])) {
					foreach($instance['donnees'] as $dk=>$d){
						$v=$d->value;
						if ($d->type=='adresse') {
							if (!isset($d->value->adresse)) $d->value->adresse='';
							if (!isset($d->value->cp)) $d->value->cp='';
							if (!isset($d->value->ville)) $d->value->ville='';
							if (!isset($d->value->pays)) $d->value->pays='';
							$v=$d->value->adresse."\n".$d->value->cp." ".$d->value->ville."\n".$d->value->pays;
						}
						if ($d->type!='note') {
							$nd=new stdClass;
							$nd->value=$v;
							$donnees[]=$nd;
						}
					}
					if (is_array($instance['donnees_etab'])) {
						foreach($instance['donnees_etab'] as $dk=>$d){
							$v=$d->value;
							if ($d->type=='adresse') {
								if (!isset($d->value->adresse)) $d->value->adresse='';
								if (!isset($d->value->cp)) $d->value->cp='';
								if (!isset($d->value->ville)) $d->value->ville='';
								if (!isset($d->value->pays)) $d->value->pays='';
								$v=$d->value->adresse."\n".$d->value->cp." ".$d->value->ville."\n".$d->value->pays;
							}
							if ($d->type!='note') {
								$nd=new stdClass;
								$nd->value=$v;
								$donnees_etab[]=$nd;
							}
						}
					}
				}
				$data=array();
				foreach ($instance['schema']->pages as $kp => $p) {
					foreach ($p->elts as $key => $champ) {
						$valeur='###';
						$tab=explode('|',$champ->label);
						$label=trim($tab[0]);
						if ($champ->type=='texte') {
							$label=strip_tags($label);
							$label=str_replace("\n\n","\n",html_entity_decode($label));
						}
						if (isset($instance['collection']->{$champ->id})) {
							$instance_elt=$instance['collection']->{$champ->id};
							$valeur=$instance['collection']->{$champ->id}['valeur'];
							if ($champ->type=='multiples') {
								$res=array();
								foreach (explode(',',$valeur) as $v) {
									$res[]=$v;
								}
								$valeur=implode(', ',$res);
							}
							if ($champ->type=='upload') {
								$res=array();
								foreach ($valeur as $v) {
									$res[]=$v->nom;
								}
								$valeur=implode("\n",$res);
							}
							if ($champ->type=='checkbox' || $champ->type=='tag') {
								$tab_true=explode('|',$champ->trueValue);
								$tab_false=explode('|',$champ->falseValue);
								$valeur= $valeur=='1' ? $tab_true[0] : $tab_false[0];
							}
						}
						$instance['schema']->pages[$kp]->elts[$key]->label=$label;
						$instance['schema']->pages[$kp]->elts[$key]->valeur=str_replace("'","''",utf8_for_xml($valeur));
						if(isset($champ->condition)) $champ->condition->valeur;
						if(isset($champ->condition)) $instance['collection']->{$champ->condition->id}['valeur'];
						if(isset($champ->condition) && !in_array($champ->condition->valeur,explode(',',$instance['collection']->{$champ->condition->id}['valeur'])))
							unset($instance['schema']->pages[$kp]->elts[$key]);
					}
				}
				$myinstance=[];
				$myinstance["contact_nom"]=$instance['nom_contact'];
				$myinstance["contact_prenom"]=$instance['prenom_contact'];
				$myinstance["etab_nom"]=$instance["nom_etab"];
				$myinstance["donnees"]=$donnees;
				$myinstance['donnees_etab']=$donnees_etab;
				$myinstance["pages"]=$instance['schema']->pages;
				$I[]=$myinstance;
			}

			$TBS->MergeBlock('instances',$I);
			return $TBS;
		}
	}
?>
