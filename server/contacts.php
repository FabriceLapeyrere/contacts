<?php
	class Contacts
	{
		public static function get_casquettes_contact($id)
		{
			$db= new DB();
			$query = "SELECT id FROM casquettes WHERE id_contact=$id";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row['id'];
			}
			return $res;
		}
		public static function get_contact($id_contact,$full=false,$id)
		{
			$db= new DB();
			$query = "SELECT id FROM casquettes WHERE id_contact=$id_contact";
			$res=array('casquettes'=>array());
			
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$cas=Contacts::get_casquette($row['id'],$full,$id);
				$cas['verrou']=WS::get_verrou("casquette/".$cas['id']);
				$res['casquettes'][$cas['id']]=$cas;
			}
			$res['verrou_contact']=WS::get_verrou("contact/".$cas['id_contact']);
			$res['nom']=$cas['nom'];
			$res['prenom']=$cas['prenom'];
			$res['type']=$cas['type'];
			$res['creationdate']=$cas['creationdate'];
			$res['modificationdate']=$cas['modificationdate'];
			$res['createdby']=$cas['createdby'];
			$res['modifiedby']=$cas['modifiedby'];
			return $res;
		}
		public static function get_contact_casquette($id_cas)
		{
			$db= new DB();
			$query = "SELECT id_contact FROM casquettes WHERE id=$id_cas";
			$res=0;
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row['id_contact'];
			}
			return $res;
		}
		public static function get_etabs_contact($id_contact)
		{
			$db= new DB();
			$query = "SELECT t2.id_contact as id_contact FROM casquettes as t1 INNER JOIN casquettes as t2 ON t1.id_etab=t2.id WHERE t1.id_contact=$id_contact";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row['id_contact'];
			}
			return $res;
		}
		public static function get_cols_etab($id_contact)
		{
			$db= new DB();
			$query = "SELECT t1.id_contact as id_contact FROM casquettes as t1 INNER JOIN casquettes as t2 ON t1.id_etab=t2.id  WHERE t2.id_contact=$id_contact";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row['id_contact'];
			}
			return $res;
		}
		public static function get_casquette($id_cas,$full=false,$id)
		{
			$db= new DB();
			$casquette=Contacts::get_casquettes(array(),$id_cas,$id);
			$cas=$casquette['collection'][0];
			if ($full && $cas['id_etab']>0) {
				$cas['etab']=Contacts::get_casquette($cas['id_etab'],false,$id);
			}
			if ($full) {
				$cas['envois']=Mailing::get_envois_casquette($id_cas,$id);
			}
			return $cas;
		}
		public static function get_idcasquette_email($email) {
			$db= new DB();
			$res=array();
			$query = "SELECT
				id FROM casquettes WHERE emails LIKE '%$email%'
				";
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row['id'];
			}
			return $res;
		}
		public static function get_nom_casquette($id) {
			$db= new DB();
			$res='';
			$query = "SELECT
				t1.id as id_contact,
				t1.nom as nom,
				t1.prenom as prenom,
				t1.type as type
				FROM contacts as t1
				left outer join casquettes as t2 on t1.id=t2.id_contact
				WHERE t2.id=$id
				";
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row;
			}
			return $res;
		}
		public static function get_nom_casquettes($tab) {
			$db= new DB();
			$res=array();
			$query = "SELECT
				t2.id as id,
				t1.id as id_contact,
				t1.nom as nom,
				t1.prenom as prenom,
				t1.type as type
				FROM contacts as t1
				left outer join casquettes as t2 on t1.id=t2.id_contact
				WHERE t2.id in (".implode(',',$tab).")
				";
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[$row['id']]=$row;
			}
			return $res;
		}
		// get all contacts
		public static function get_casquettes($params=array('query'=>'','page'=>1,'nb'=>10),$id_cas=0,$id)
		{
			$db= new DB();
			$t0=millisecondes();
			$params=(object) $params;
			if (isset($params->query)) {
				$query=$params->query;
				$page=$params->page;
				$first=($params->page-1)*$params->nb;
				$nb=$params->nb;
			}
			if ($id_cas!=0) {
				$query='';
				$page=1;
				$first=0;
				$nb=50;
			}
			$query=Contacts::build_query($query,$id);
			$t1=millisecondes();
			if ($id_cas==0) {
				$sql = "SELECT
					count(*) as nb
					FROM contacts as t1
					inner join casquettes as t2 on t1.id=t2.id_contact
					";
				$sql .= "WHERE $query";
				$total=0;
				$t2=millisecondes();
				foreach($db->database->query($sql, PDO::FETCH_ASSOC) as $row){
					$total=$row['nb']+0;
				}
			} else {
				$t2=millisecondes();
				$total=0;
			}
			$t3=millisecondes();
			$sql = "SELECT
				t1.id as id_contact,
				t1.nom as nom,
				t1.prenom as prenom,
				t1.type as type,
				t1.creationdate as creationdate,
				t1.createdby as createdby,
				t1.modificationdate as modificationdate,
				t1.modifiedby as modifiedby,
				t2.id as id,
				t2.nom as nom_cas,
				t2.id_etab as id_etab,
				t2.donnees as donnees,
				t2.emails as emails,
				t2.fonction as fonction,
				t2.email_erreur as email_erreur,
				t2.cp as cp,
				t2.gps_x as gps_x,
				t2.gps_y as gps_y,
				t2.creationdate as cas_creationdate,
				t2.createdby as cas_createdby,
				t2.modificationdate as cas_modificationdate,
				t2.modifiedby as cas_modifiedby,
				Group_Concat(DISTINCT t3.id_tag) as tags,
				t4.id as id_etab,
				t4.donnees as donnees_etab,
				t5.id as id_contact_etab,
				t5.nom as nom_etab,
				'[' || Group_Concat(DISTINCT '{\"id\":\"'||t6.id||'\",\"id_contact\":\"'||t6.id_contact||'\",\"nom\":\"'||replace(t7.nom,'\"','\\\"')||'\",\"prenom\":\"'||replace(t7.prenom,'\"','\\\"')||'\",\"fonction\":\"'||replace(t6.fonction,'\"','\\\"')||'\"}')||']' as cols
				FROM contacts as t1
				left outer join casquettes as t2 on t1.id=t2.id_contact
				left outer join tag_cas as t3 on t2.id=t3.id_cas
				left outer join casquettes as t4 on t4.id=t2.id_etab
				left outer join contacts as t5 on t5.id=t4.id_contact
				left outer join casquettes as t6 on t6.id_etab=t2.id
				left outer join contacts as t7 on t7.id=t6.id_contact";
			if ($id_cas!=0) {
				$sql .= "
				WHERE t2.id=$id_cas";
			} else {
				$sql .= "
					WHERE t2.id in (
						SELECT
						t2.id
						FROM contacts as t1
						inner join casquettes as t2 on t1.id=t2.id_contact
				";
				$sql .= "WHERE $query";
				$sql .= " ORDER BY t1.sort ASC, t1.id ASC, t2.id ASC
				";
				if (!isset($params->all)) $sql .= " LIMIT $first,$nb
				";
				$sql .= ")
					GROUP BY t2.id
					ORDER BY t1.sort ASC, t1.id ASC, t2.id ASC";
			}
			$casquettes=array();
			$t4=millisecondes();
			foreach($db->database->query($sql, PDO::FETCH_ASSOC) as $row){
				$row['emails']=json_decode($row['emails']);
				$row['donnees']=json_decode($row['donnees']);
				if ($row['id_etab']>0) $row['donnees_etab']=json_decode($row['donnees_etab']);
				$row['cols']=json_decode($row['cols']);
				$row['tags']=$row['tags']!="" ? explode(',',$row['tags']) : array();
				$casquettes[]=$row;
			}
			$t5=millisecondes();
			return array('params'=>$params,'collection'=>$casquettes,'page'=>$page, 'nb'=>$nb, 'total'=>$total,'query'=>$query,'times'=>array($t1-$t0,$t2-$t1,$t3-$t2,$t4-$t3,$t5-$t4,$sql));
		}
		public static function build_query($query,$id){
			$tags=Contacts::get_tags();
			$pattern = "/::([^::]*)::/";
			preg_match_all($pattern, $query, $matches, PREG_OFFSET_CAPTURE);
			foreach($matches[0] as $key=>$value){
				$code=$matches[0][$key][0];
				$tab=explode('/',$matches[1][$key][0]);
				$type=$tab[0];
				$param=isset($tab[1]) ? $tab[1] : '';
				$valeur='';
				switch ($type) {
					case 'contacts':
						$valeur="t2.id IN (".$param.")";
						break;
					case 'etab':
						$valeur="t2.id_etab=$param OR t2.id=$param";
						break;
					case 'mailerreur':
						$valeur="t2.email_erreur=1";
						break;
					case 'email':
						$valeur="t2.emails!='[]'";
						break;
					case 'adresse':
						$valeur="( (t2.cp!='' AND t2.cp!='E') OR t2.id_etab in (SELECT id FROM casquettes WHERE (cp!='' AND cp!='E')))";
						break;
					case 'panier':
						$panier=User::get_panier($id);
						$valeur="t2.id IN (".implode(',',$panier).")";
						break;
					case 'type':
						$valeur="t1.type=$param";
						break;
					case 'cp':
						$valeur="(t2.cp='".strtoupper($param)."' OR t2.cp='' AND t2.id_etab in (SELECT id FROM casquettes WHERE cp='".strtoupper($param)."'))";
						break;
					case 'cps':
						$t=explode(',',strtoupper($param));
						$cps=array();
						foreach($t as $cp) {
							$cps[]="'$cp'";
						}
						$valeur="(t2.cp in (".implode(',',$cps).") OR t2.cp='' AND t2.id_etab in (SELECT id FROM casquettes WHERE cp in (".implode(',',$cps).")))";
						break;
					case 'text':
						$valeur="t2.id IN (SELECT id from casquettes_fts WHERE idx MATCH '".str_replace("'","''",normalizeChars($param))."')";
						break;
					case 'tag':
						$children=Contacts::get_whole_tag($param,$tags);
						$valeur= "t2.id IN (SELECT id_cas FROM tag_cas WHERE id_tag IN (".implode(', ',$children)."))";
						break;
					case 'envoi':
						$valeur= "t2.id IN (SELECT id_cas FROM envoi_cas WHERE id_envoi=$param)";
						break;
					case 'news':
						$valeur= "t2.id IN (SELECT id_cas FROM envoi_cas as t10 INNER JOIN envois as t11 ON t10.id_envoi=t11.id WHERE t11.type='news' AND t11.id_type=$param)";
						break;
					case 'mail':
						$valeur= "t2.id IN (SELECT id_cas FROM envoi_cas as t10 INNER JOIN envois as t11 ON t10.id_envoi=t11.id WHERE t11.type='mail' AND t11.id_type=$param)";
						break;
					case 'seulement_tags':	
						$ts=explode(',',$param);
						sort($ts);
						$param=implode(',',$ts);
						$valeur= "t2.id IN (select id_cas FROM (select * from tag_cas order by id_tag) WHERE id_cas in (select id_cas from tag_cas where id_tag in (".$param.")) group by id_cas having group_concat(id_tag)='".$param."')";
						break;
					case 'aucun_tag':	
						$valeur= "0 = (select count(*) from tag_cas where id_cas=t2.id)";
						break;
				}
				$query=str_replace($code,$valeur,$query);
			}
			return $query;
		}				
		public static function get_id_cass_filtre($query,$id){
			$db= new DB();
			$sql = "SELECT
					t2.id as id
					FROM contacts as t1
					inner join casquettes as t2 on t1.id=t2.id_contact ";
			$condition=Contacts::build_query($query,$id);
			$sql .= "
				WHERE $condition ";
			$sql .= " ORDER BY t1.nom ASC, t2.id ASC";
			$res=array();
			foreach($db->database->query($sql, PDO::FETCH_ASSOC) as $row){
				$res[]=$row['id'];
			}
			return $res;
		}
		public static function get_cols($id) {
			$db= new DB();
			$query = "SELECT
				t1.id as id_contact,
				t1.nom as nom,
				t1.prenom as prenom,
				t2.id as id,
				t2.nom as nom_cas,
				t2.donnees as donnees
				FROM contacts as t1
				inner join casquettes as t2 on t1.id=t2.id_contact
				WHERE t2.id_etab=$id
			";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['donnees']=json_decode($row['donnees']);
				$res[]=$row;
			}
			return $res;
		}
		public static function get_tags() {
			$db= new DB();
			$query = "SELECT * FROM tags";
			$tags=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				if (strlen($row['color'])==0) $row['color']='#333333';
				$row['verrou']=WS::get_verrou('tag/'.$row['id']);
				$tags[$row['id']]=$row;
			}
			return $tags;
		}
		public static function get_selections() {
			$db= new DB();
			$query = "SELECT * FROM selections";
			$selections=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['verrou']=WS::get_verrou('selection/'.$row['id']);
				$selections[$row['id']]=$row;
			}
			return $selections;
		}
		public static function get_tag($id) {
			$db= new DB();
			$query = "SELECT * FROM tags WHERE id=$id";
			$tag=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				if (strlen($row['color'])==0) $row['color']='#333333';
				$tag=$row;
			}
			return $tag;
		}
		public static function get_whole_tag($id,$tab){
			$db= new DB();
			$children=array();
			$children[]=$id;
			foreach($tab as $e){
				if($e['id_parent']==$id) {
					$children=array_merge($children,Contacts::get_whole_tag($e['id'],$tab));
				}
			}
			return $children;
		}
		public static function del_tag($params,$id) {
			$db= new DB();
			$tag=$params->tag;
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($tag->id,'tag',json_encode($tag),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM tags WHERE id=? ');
			$delete->execute(array($tag->id));
			$tab=array();
			foreach(Contacts::get_cass_tag($tag->id) as $id_cas){
				$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
				$insert->execute(array($tag->id."|".$id_cas,'tag_cas',json_encode(array('id_tag'=>$tag->id,'id_cas'=>$id_cas)),millisecondes(),$id));
				ldap_update($id_cas);
				$tab[]='contact/'.Contacts::get_contact_casquette($id_cas);
			}
			$delete = $db->database->prepare('DELETE FROM tag_cas WHERE id_tag=?');
			$delete->execute(array($tag->id));
			$tab[]='tags';
			CR::maj($tab);
			return 1;
		}
		public static function del_selection($params,$id) {
			$db= new DB();
			$selection=$params->selection;
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($selection->id,'selection',json_encode($selection),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM selections WHERE id=? ');
			$delete->execute(array($selection->id));
			CR::maj(array('selections'));
			return 1;
		}
		public static function get_tags_cas($id_cas) {
			$db= new DB();
			$query = "SELECT t1.id_tag FROM tag_cas as t1 inner join tags as t2 on t1.id_tag=t2.id WHERE id_cas=$id_cas ORDER BY t2.nom";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row['id_tag'];
			}
			return $res;
		}
		public static function get_cass_tag($id_tag) {
			$db= new DB();
			$query = "SELECT id_cas FROM tag_cas WHERE id_tag=$id_tag ";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row['id_cas'];
			}
			return $res;
		}
		public static function get_cass_tag_rec($id_tag) {
			$db= new DB();
			$tags=Contacts::get_tags();
			$children=Contacts::get_whole_tag($id_tag,$tags);
			$query = "SELECT id_cas FROM tag_cas WHERE id_tag IN (".implode(',',$children).") ";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row['id_cas'];
			}
			return $res;
		}
		public static function mod_contact($params,$id) {
			$db= new DB();
			$nom=$params->nom;
			$prenom="";
			if (isset($params->prenom)) $prenom=$params->prenom;
			$sort=filter2("$nom $prenom");
			if ($sort=="") $sort="zzzzzz";
			$id_contact=$params->id;
			$update = $db->database->prepare('UPDATE contacts SET sort=?, nom=?, prenom=?, modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array($sort, $nom,$prenom,millisecondes(),$id,$id_contact));
			$cass=array();
			foreach(Contacts::get_casquettes_contact($id_contact) as $id_cas) {
				$cass[]=$id_cas;
				$cas=Contacts::get_casquette($id_cas,true,$id);
				$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
				$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']))." ".idx( (object) $cas['donnees']),$cas['id']));
			}
			if (count($cass)>0) ldap_update_array($cass);
			CR::maj(array("contact/$id_contact"));
			return 1;
		}
		public static function del_contact($params,$id) {
			$db= new DB();
			$cas=$params->cas;
			$id_contact=$params->cas->id_contact;
			$contact=Contacts::get_contact($id_contact,true,$id);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id_contact,'contact',json_encode($contact),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM contacts WHERE id=? ');
			$delete->execute(array($id_contact));
			foreach($contact['casquettes'] as $c){
				$params=json_decode(json_encode(array('cas'=>$c)));
				Contacts::del_casquette($params,$id);
			}
			return 1;
		}
		public static function touch_contact($id_contact,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE contacts SET modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array(millisecondes(),$id,$id_contact));
			return 1;
		}
		public static function set_mail_erreur($cas_id,$email,$id) {
			$db= new DB();
			$cas=Contacts::get_casquette($cas_id,false,$id);
			$donnees=$cas['donnees'];
			$t=millisecondes();
			foreach($donnees as $k=>$d) {
				if ($d->type=='email' && strpos($d->value,$email)!==false) {
					$donnees[$k]->type='email_erreur';
					$donnees[$k]->date=$t;
					$donnees[$k]->by=$id;
				}
			}
			$update = $db->database->prepare('UPDATE casquettes SET donnees=?, emails=?, email_erreur=? WHERE id=?');
			$update->execute(array(json_encode($donnees),emails($donnees),1,$cas_id));
			$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
			$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']))." ".idx($donnees),$cas_id));
			Contacts::touch_contact($cas['id_contact'],$id);
			ldap_update($cas_id);
			CR::maj(array('contact/'.$cas['id_contact']));
		}
		public static function remove_mail($email,$id) {
			$db= new DB();
			$query="select id from casquettes where emails like '%$email%'";
			$casquette_ids=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$casquette_ids[]=$row['id'];
			}
			foreach($casquette_ids as $cas_id) {
				$cas=Contacts::get_casquette($cas_id,false,$id);
				$donnees=$cas['donnees'];
				foreach($donnees as $k=>$d) {
					if ($d->type=='email' && strpos($d->value,$email)!==false) {
						unset($donnees[$k]);
					}
				}
				$donnees=array_values($donnees);
				$test=true;
				foreach($donnees as $k=>$d) {
					if ($d->type=='note') {
						$test=false;
						$donnees[$k]->value=$donnees[$k]->value."\nL'adresse $email a été désinscrite.";
					}
				}
				if ($test) {
					$t=millisecondes();
					$d=json_decode('{}');
					$d->label='Note';
					$d->type='note';
					$d->date=$t;
					$d->by=$id;
					$d->value="L'adresse $email a été désinscrite.";
					$donnees[]=$d;
				}
				$update = $db->database->prepare('UPDATE casquettes SET donnees=?, emails=?, email_erreur=? WHERE id=?');
				$update->execute(array(json_encode($donnees),emails($donnees),0,$cas_id));	
				$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
				$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']))." ".idx($donnees),$cas_id));
				Contacts::touch_contact($cas['id_contact'],$id);
				ldap_update($cas_id);
				CR::maj(array('contact/'.$cas['id_contact']));
			}
			return count($casquette_ids);
		}
		public static function mod_casquette($params,$id) {
			$db= new DB();
			$cas=Contacts::get_casquette($params->cas->id,false,$id);
			$prev_etabs=Contacts::get_etabs_contact($params->cas->id_contact);
                   	$avant=$cas['donnees'];
			$apres=$params->cas->donnees;
			$t=millisecondes();
			$gps=array('x'=>1000,'y'=>1000);
			foreach($apres as $k=>$d) {
				$new=true;
				foreach($avant as $a) {
					if ($a->label==$d->label && $a->type==$d->type) $new=false;
					if ($a->label==$d->label && $a->type==$d->type && $a->value!=$d->value) {
						$apres[$k]->date=$t;
						$apres[$k]->by=$id;
						if ($d->type=='adresse') $gps=get_gps($d->value);
					}
				}
				if ($new) {
					$apres[$k]->date=$t;
					$apres[$k]->by=$id;
					if ($d->type=='adresse') $gps=get_gps($d->value);
				}
			}
			$id_etab= isset($params->cas->id_etab) ? $params->cas->id_etab : 0 ;
			$update = $db->database->prepare('UPDATE casquettes SET nom=?, donnees=?, id_etab=?, modificationdate=?, modifiedby=?, emails=?, email_erreur=?, fonction=?, cp=?, gps_x=?, gps_y=? WHERE id=?');
			$update->execute(array($params->cas->nom_cas,json_encode($apres),$id_etab,$t,$id,emails($apres),email_erreur($apres),fonction($apres),cp($apres),$gps['x'],$gps['y'],$params->cas->id));
			$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
			$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']))." ".idx($apres),$params->cas->id));
			Contacts::touch_contact($params->cas->id_contact,$id);
			ldap_update($params->cas->id_contact,$id);
			$res=array('contact/'.$params->cas->id_contact);
			foreach($prev_etabs as $idetab) {
				$res[]='contact/'.$idetab;
			}
			CR::maj($res);
		}
		public static function add_contact($params,$id) {
			$db= new DB();
			$type=$params->contact->type;
			$nom=$params->contact->nom;
			$prenom=isset($params->contact->prenom) ? $params->contact->prenom : '';
			$sort=filter2("$nom $prenom");
			if ($sort=="") $sort="zzzzzz";
			$insert = $db->database->prepare('INSERT INTO contacts (sort, nom, prenom, type, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?,?,?)');
			$insert->execute(array($sort, $nom, $prenom, $type, millisecondes(), $id, millisecondes(), $id));
			$id_contact = $db->database->lastInsertId();
			//on ajoute une casquette
			$nom_cas= $type==1 ? 'Perso' : 'Siège';
			$insert = $db->database->prepare('INSERT INTO casquettes (nom,donnees,emails,email_erreur,fonction,cp,gps_x,gps_y,id_etab,id_contact,creationdate,createdby,modificationdate,modifiedby) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
			$insert->execute(array($nom_cas,'[]','[]',0,'','',1000,1000,0,$id_contact,millisecondes(),$id,millisecondes(),$id));
			$id_cas = $db->database->lastInsertId();
			$insert = $db->database->prepare('INSERT INTO casquettes_fts (id,idx) VALUES (?,?)');
			$insert->execute(array($id_cas,strtolower(normalizeChars($nom." ".$prenom))));
			ldap_update($id_cas);
			CR::maj(array('casquettes'));
			return $id_contact;
		}
		public static function add_casquette($params,$id) {
			$db= new DB();
			$insert = $db->database->prepare('INSERT INTO casquettes (nom,donnees,emails,email_erreur,fonction,cp,gps_x,gps_y,id_etab,id_contact,creationdate,createdby,modificationdate,modifiedby) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
			$insert->execute(array($params->cas->nom_cas,'[]','[]',0,'','',1000,1000,0,$params->cas->id_contact,millisecondes(),$id,millisecondes(),$id));
			$params->cas->id = $db->database->lastInsertId();
			$insert = $db->database->prepare('INSERT INTO casquettes_fts (id,idx) VALUES (?,?)');
			$insert->execute(array($params->cas->id,strtolower(normalizeChars($params->cas->nom." ".$params->cas->prenom))));
			Contacts::mod_casquette($params,$id);
			ldap_update($params->cas->id);
			CR::maj(array('contact/'.$params->cas->id_contact));
			return 1;
		}
		public static function move_tag($params,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE tags SET id_parent=?, modificationdate=?, modifiedby=? WHERE id=? ');
			$update->execute(array($params->parent->id,millisecondes(),$id,$params->tag->id));
			CR::maj(array('casquettes','tags'));
			return 1;
		}
		public static function mod_tag($params,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE tags SET nom=?, color=?, modificationdate=?, modifiedby=? WHERE id=? ');
			$update->execute(array($params->tag->nom,$params->tag->color,millisecondes(),$id,$params->tag->id));
			CR::maj(array('tags'));
			return 1;
		}
		public static function mod_selection($params,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE selections SET nom=?, query=?, modificationdate=?, modifiedby=? WHERE id=? ');
			$update->execute(array($params->selection->nom,$params->selection->query,millisecondes(),$id,$params->selection->id));
			CR::maj(array('selections'));
			return 1;
		}
		public static function add_tag($params,$id) {
			$db= new DB();
			$insert = $db->database->prepare('INSERT INTO tags (nom, color, id_parent, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?,?) ');
			$insert->execute(array($params->tag->nom,$params->tag->color,0,millisecondes(),$id,millisecondes(),$id));
			CR::maj(array('tags'));
			return 1;
		}
		public static function add_selection($params,$id) {
			$db= new DB();
			$insert = $db->database->prepare('INSERT INTO selections (nom, query, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?) ');
			$insert->execute(array($params->selection->nom,$params->selection->query,millisecondes(),$id,millisecondes(),$id));
			CR::maj(array('selections'));
			return 1;
		}
		public static function add_cas_tag($params,$id) {
			$db= new DB();
			$insert = $db->database->prepare('INSERT OR REPLACE INTO tag_cas (id_tag,id_cas,date) VALUES (?,?,?)');
			$insert->execute(array($params->tag->id,$params->cas->id,millisecondes()));
			ldap_update($params->cas->id);
		   	CR::maj(array('contact/'.$params->cas->id_contact));
			return 1;
		}
		public static function del_cas_tag($params,$id) {
			$db= new DB();
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($params->tag->id."|".$params->cas->id,'tag_cas',json_encode(array('id_tag'=>$params->tag->id,'id_cas'=>$params->cas->id,'id_contact'=>$params->cas->id_contact)),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM tag_cas WHERE id_tag=? AND id_cas=? ');
			$delete->execute(array($params->tag->id,$params->cas->id));
			ldap_update($params->cas->id);
			CR::maj(array('contact/'.$params->cas->id_contact));
			return 1;
		}
		public static function add_panier_tag($params, $id) {
			$db= new DB();
			$panier=User::get_panier($id);
			$t=millisecondes();
			$db->database->beginTransaction();
			foreach($panier as $id_cas){
				$insert = $db->database->prepare('INSERT OR REPLACE INTO tag_cas (id_tag,id_cas,date) VALUES (?,?,?)');
				$insert->execute(array($params->tag->id,$id_cas,$t));
			}
			$db->database->commit();
			CR::maj(array("casquettes","contact/*","suivis"));
			return count($panier);
		}
		public static function del_panier_tag($params,$id) {
			$db= new DB();
			$panier=User::get_panier($id);
			$t=millisecondes();
			$db->database->beginTransaction();
			foreach($panier as $id_cas){
				$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
				$insert->execute(array($params->tag->id."|".$id_cas,'tag_cas',json_encode(array(
						'id'=>$params->tag->id."|".$id_cas,
						'id_tag'=>$params->tag->id,
						'id_cas'=>$id
					)),millisecondes(),$id));
			}
			$delete = $db->database->exec('DELETE FROM tag_cas WHERE id_tag='.$params->tag->id.' AND id_cas IN ('.implode(', ', $panier).')');
			$db->database->commit();
			CR::maj(array("casquettes","contact/*","suivis"));
			return count($panier);
		}
		public static function del_casquette($params,$id) {
			$db= new DB();
			$params->cas->suivis=Suivis::get_suivis_casquette($params->cas->id,$id);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($params->cas->id,'casquette',json_encode($params->cas),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM casquettes WHERE id=? ');
			$delete->execute(array($params->cas->id));
			$delete = $db->database->prepare('DELETE FROM tag_cas WHERE id_cas=? ');
			$delete->execute(array($params->cas->id));
			$delete = $db->database->prepare('DELETE FROM suivis WHERE id_casquette=? ');
			$delete->execute(array($params->cas->id));
			$update = $db->database->prepare('UPDATE casquettes SET id_etab=0, modificationdate=?, modifiedby=? WHERE id_etab=?');
			$update->execute(array(millisecondes(),$id,$params->cas->id));
			Contacts::touch_contact($params->cas->id_contact,$id);
			$p= (object) array('nouveaux'=>array($params->cas->id));
			User::del_panier($p,$id);
			CR::maj(array("casquettes","contact/*","suivis","panier"));
			return 1;
		}
		public static function del_casquettes_panier($params,$id) {
			$db= new DB();
			$cass=Contacts::get_casquettes(array('query'=>'::panier::','page'=>1,'nb'=>10,'all'=>1),0,$id);
			$db->database->beginTransaction();
			$panier=array();
			foreach($cass['collection'] as $cas){
				$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
				$insert->execute(array( $cas['id'],'casquette',json_encode($cas),millisecondes(),$id));
				$delete = $db->database->prepare('DELETE FROM casquettes WHERE id=? ');
				$delete->execute(array( $cas['id']));
				$delete = $db->database->prepare('DELETE FROM tag_cas WHERE id_cas=? ');
				$delete->execute(array( $cas['id']));
				$delete = $db->database->prepare('DELETE FROM suivis WHERE id_casquette=? ');
				$delete->execute(array( $cas['id']));
				$update = $db->database->prepare('UPDATE casquettes SET id_etab=0, modificationdate=?, modifiedby=? WHERE id_etab=?');
				$update->execute(array(millisecondes(),$id, $cas['id']));
				$update = $db->database->prepare('UPDATE contacts SET modificationdate=?, modifiedby=? WHERE id=?');
				$update->execute(array(millisecondes(),$cas['id_contact']));
				$panier[]=$cas['id'];
			}
			$delete = $db->database->exec('DELETE FROM contacts WHERE 0=(select count(*) from casquettes where id_contact=contacts.id)');
			$db->database->commit();
			$p= (object) array('nouveaux'=>$panier);
			User::del_panier($p,$id);
			CR::maj(array("casquettes","contact/*","suivis","panier"));
			return 1;
		}
		public static function cas_has_tag($id_cas,$id_tag)
		{
			$db= new DB();
			$query = "SELECT id_cas FROM tag_cas WHERE id_tag=$id_tag AND id_cas=$id_cas";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row['id_cas'];
			}
			return count($res)>0;
		}
		public static function get_cass_email($email) {
			$db= new DB();
			$query = "SELECT id FROM casquettes WHERE emails LIKE '%$email%' ";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row['id'];
			}
			return $res;
		}
		public static function index_gps(){
			$command = "nohup /usr/bin/php exec.php get_gps > /dev/null 2>&1 &";
			exec($command);
		}	
		public static function add_nb_contacts($params,$id){
			$db= new DB();
			$contacts=array();
			$deja=array();
			foreach($params->contacts as $c){
				$cass=Contacts::get_cass_email($c->mail);
				if (count($cass)==0) $contacts[]=$c;
				else $deja=array_merge($deja,$cass);
			}
			$dejaMod=array();
			foreach($deja as $id_cas){
				foreach($params->tags as $id_tag){
					if (!Contacts::cas_has_tag($id_cas,$id_tag)) {
						$dejaMod[]=$id_cas;
					}
				}
			}
			$cass=array();
			$db->database->beginTransaction();
			foreach($contacts as $c) {
				$nom=isset($c->nom) ? trim($c->nom) : '';
				$sort=filter2($nom);
				if ($sort=="") $sort="zzzzzz";
				$type=1;
				$donnees= array();
				$donnees[]= new stdClass;
				$donnees[0]->type='email';
				$donnees[0]->label='E-mail';
				$donnees[0]->value=$c->mail;
				//on ajoute le contact
				$insert = $db->database->prepare('INSERT INTO contacts (sort, nom, prenom, type, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?,?,?)');
				$insert->execute(array($sort, $nom, '', $type, millisecondes(), $id, millisecondes(), $id));
				$id_contact = $db->database->lastInsertId();
				//on ajoute une casquette
				$nom_cas= 'Perso';
				$insert = $db->database->prepare('INSERT INTO casquettes (nom,donnees,emails,email_erreur,fonction,cp,gps_x,gps_y,id_etab,id_contact,creationdate,createdby,modificationdate,modifiedby) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
				$insert->execute(array($nom_cas,json_encode($donnees),emails($donnees),0,fonction($donnees),cp($donnees),1000,1000,0,$id_contact,millisecondes(),$id,millisecondes(),$id));
				$id_cas = $db->database->lastInsertId();
				$insert = $db->database->prepare('INSERT INTO casquettes_fts (id,idx) VALUES (?,?)');
				$insert->execute(array($id_cas,strtolower(normalizeChars($nom)).idx($donnees)));
				//on associe les tags
				foreach($params->tags as $id_tag) {
					$insert = $db->database->prepare('INSERT OR REPLACE INTO tag_cas (id_tag,id_cas,date) VALUES (?,?,?)');
					$insert->execute(array($id_tag,$id_cas,millisecondes()));
				}
				$cass[]=$id_cas;
			}
			foreach($dejaMod as $id_cas) {
				$cass[]=$id_cas;
				foreach($params->tags as $id_tag) {
					$insert = $db->database->prepare('INSERT OR REPLACE INTO tag_cas (id_tag,id_cas,date) VALUES (?,?,?)');
					$insert->execute(array($id_tag,$id_cas,millisecondes()));
				}
			}	
			$db->database->commit();
			if (count($cass)>0) ldap_update_array($cass);
			CR::maj(array('*'));
		}
		public static function add_nb_csv($params,$id){
			$db= new DB();
			$tags=$params->tags;
			$map=$params->map;
			$hash=$params->hash;
			$i=0;
			if (($handle = fopen("data/tmp/$hash", "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					if ($i>0) $rows[]=$data;
					$i++;
				}
				fclose($handle);
			}
			$contacts=array();
			foreach($rows as $row) {
				$note="";
				$contact=array();
				$donnees=array();
				$adresse=array();
				foreach($map as $type=>$keys) {
					$i=0;
					foreach($keys as $k=>$label) {
						if ($row[$k]!='') {
							if ($i==0) {
								if ($type=='id') $contact['id']=$row[$k]; 
								if ($type=='idstr') $contact['idstr']=$row[$k]; 
								if ($type=='nom') $contact['nom']=$row[$k]; 
								if ($type=='prenom') $contact['prenom']=$row[$k]; 
								if ($type=='type') $contact['type']=$row[$k]; 
								if ($type=='note') $note.="\n".$row[$k];
								if ($type=='fonction') $donnees[]=array('type'=>'fonction','label'=>$label,'value'=>$row[$k]);
								if ($type=='adresse') $adresse['adresse']=$row[$k]; 
								if ($type=='cp') $adresse['cp']=$row[$k]; 
								if ($type=='ville') $adresse['ville']=$row[$k]; 
								if ($type=='pays') $adresse['pays']=$row[$k];
							}
							if ($type=='email') {
								foreach(extractEmailsFromString($row[$k]) as $m) {
									$donnees[]=array('type'=>'email','label'=>$label,'value'=>$m);
								}
							}
							if ($type=='tel') $donnees[]=array('type'=>'tel','label'=>$label,'value'=>$row[$k]);
						}
						$i++;
					}
				}
				if ($note!='') {
					$donnees[]=array('type'=>'note','label'=>'Note','value'=>trim($note));
				}
				if (array_key_exists('cp',$map) && $adresse['cp']!='') {
					$donnees[]=array('type'=>'adresse','label'=>'Adresse','value'=>$adresse);
				}
				if (!array_key_exists('type',$map)) {
					$contact['type']=1;
				}
				$contact['donnees']=json_decode(json_encode($donnees));
				$contacts[]=$contact;
			}
			$cass=array();
			$db->database->beginTransaction();
			foreach($contacts as $index=>$contact) {
				$nom=$contact['nom'];
				$prenom = isset($contact['prenom']) ? $contact['prenom'] : '';
				$sort=filter2("$nom $prenom");
				if ($sort=="") $sort="zzzzzz";
				//on ajoute le contact
				$insert = $db->database->prepare('INSERT INTO contacts (sort, nom, prenom, type, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?,?,?)');
				$insert->execute(array($sort, $nom, $prenom, $contact['type'], millisecondes(), $id, millisecondes(), $id));
				$id_contact = $db->database->lastInsertId();
				//on ajoute une casquette
				$donnees=$contact['donnees'];
				$nom_cas= 'Perso';
				if ($contact['type']==2) $nom_cas= 'Siège Social';
				$insert = $db->database->prepare('INSERT INTO casquettes (nom,donnees,emails,email_erreur,fonction,cp,gps_x,gps_y,id_etab,id_contact,creationdate,createdby,modificationdate,modifiedby) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
				$insert->execute(array($nom_cas,json_encode($donnees),emails($donnees),0,fonction($donnees),cp($donnees),1000,1000,0,$id_contact,millisecondes(),$id,millisecondes(),$id));
				$id_cas = $db->database->lastInsertId();
				$contacts[$index]['id_cas']=$id_cas;
				$insert = $db->database->prepare('INSERT INTO casquettes_fts (id,idx) VALUES (?,?)');
				$insert->execute(array($id_cas,strtolower(normalizeChars($nom." ".$prenom)).idx($donnees)));
				//on associe les tags
				foreach($tags as $id_tag) {
					$insert = $db->database->prepare('INSERT INTO tag_cas (id_tag,id_cas,date) VALUES (?,?,?)');
					$insert->execute(array($id_tag,$id_cas,millisecondes()));
				}
				$cass[]=$id_cas;
			}
			foreach($contacts as $c) {
				if (array_key_exists('idstr',$c)) {
					foreach($contacts as $s) {
						if (array_key_exists('id',$s) && $s['id']==$c['idstr'] && $s['type']==2) {
							$update = $db->database->prepare('UPDATE casquettes SET id_etab=? WHERE id=?');
							$update->execute(array($s['id_cas'],$c['id_cas']));
						}
					}
				}
			}
			$db->database->commit();
			if (count($cass)>0) ldap_update_array($cass);
			CR::maj(array('*'));
			Contacts::index_gps();
		}
	}
?>
