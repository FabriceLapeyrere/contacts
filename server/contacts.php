<?php
	class Contacts
	{
		protected $WS;
		protected $from;
		public function __construct($WS,$from) {
	 	 	$this->WS= $WS;
	 	 	$this->from= $from;
		}
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
				$res['casquettes'][$cas['id']]=$cas;
			}
			$res['nom']= $cas['nom']!==NULL ? $cas['nom'] : '';
			$res['prenom']= $cas['prenom']!==NULL ? $cas['prenom'] : '';
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
				$cas['suivis']=(object)Suivis::get_suivis_casquette($id_cas,$id);
				$cas['envois']=Mailing::get_envois_casquette($id_cas,$id);
			}
			return $cas;
		}
		public static function get_casquette_thread($id_thread,$id)
		{
			$db= new DB();
			$query = "SELECT
				id_casquette FROM suivis_threads WHERE id=$id_thread
				";
			$id_casquette=0;
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$id_casquette=$row['id_casquette'];
			}
			$casquette=Contacts::get_casquette($id_casquette,false,$id);
			return $casquette;
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
		public static function get_carte($params=array('query'=>''),$id)
		{
			$db= new DB();
			$params=(object) $params;
			if (isset($params->query)) {
				$query=$params->query;
			} else {
				$query='';
			}
			$query=Contacts::build_query($query,$id);


			$lon1=1.0*$params->bounds->x0;
			$lon2=1.0*$params->bounds->x1;
			$lat1=1.0*$params->bounds->y0;
			$lat2=1.0*$params->bounds->y1;
			$lat_amp=$lat2-$lat1;
			$lon_amp=$lon2-$lon1;
			$markers=array();
			$map_query="SELECT t2.id, t2.gps_x as lon1, t2.gps_y as lat1, t3.gps_x as lon2, t3.gps_y as lat2
			FROM contacts as t1
			inner join casquettes as t2 on t1.id=t2.id_contact
			left join casquettes as t3 on t2.id_etab=t3.id
			WHERE $query AND (
				t2.gps_x>=$lon1 AND t2.gps_x<=$lon2 AND t2.gps_y>=$lat1 AND t2.gps_y<=$lat2
				OR t3.gps_x>=$lon1 AND t3.gps_x<=$lon2 AND t3.gps_y>=$lat1 AND t3.gps_y<=$lat2
			)";

			foreach($db->database->query($map_query, PDO::FETCH_ASSOC) as $row){
				$row['lon']=$row['lon1']<1000 ? 1.0*$row['lon1'] : 1.0*$row['lon2'];
				$row['lat']=$row['lon1']<1000 ? 1.0*$row['lat1'] : 1.0*$row['lat2'];
				$markers[]=$row;
			}

			$distance=20;
			$clustered = array();
			/* Loop until all markers have been compared. */
			while (count($markers)) {
				$marker  = array_pop($markers);
				$cluster = array();
				/* Compare against all markers which are left. */
				foreach ($markers as $key => $target) {
					$pixels = pixelDistance($marker['lat'], $marker['lon'],
											$target['lat'], $target['lon'],
											$params->zoom);
					/* If two markers are closer than given distance remove */
					/* target marker from array and add it to cluster.	  */
					if ($distance > $pixels) {
						unset($markers[$key]);
						$cluster[] = $target;
					}
				}

				/* If a marker has been added to cluster, add also the one  */
				/* we were comparing to and remove the original from array. */
				if (count($cluster) > 0) {
					$cluster[] = $marker;
					$clustered[$marker['id']] = $cluster;
				} else {
					$clustered[$marker['id']] = array($marker);
				}
			}
			$clustered_tab=array();
			foreach($clustered as $k=>$cluster) {
				$nb=count($cluster);
				$lats=0;
				$lons=0;
				$ids=[];
				foreach($cluster as $m) {
					$lats+=$m['lat'];
					$lons+=$m['lon'];
					$ids[]=$m['id'];
				}
				$lat=$lats/$nb;
				$lon=$lons/$nb;
				$clustered_tab[]=array('nb'=>$nb,'id'=>$k,'lon'=>$lon,'lat'=>$lat,'ids'=>$ids);
			}
			$clusters=json_decode('{
				"type": "FeatureCollection",
				"features": []
			}');
			foreach($clustered_tab as $c)
			{
				$m=json_decode('{
					"type":"Feature",
					"id":"",
					"properties":{},
					"geometry":{
						"type":"Point",
						"coordinates":[0,0]
					}
				}');
				$m->id=$c['id'];
				$m->geometry->coordinates=array($c['lon'],$c['lat']);
				$m->properties->nb=1*$c['nb'];
				$m->properties->ids=implode(',',$c['ids']);
				$clusters->features[]=$m;
			}
			return array('params'=>$params,'geojson_clusters'=>$clusters,'map_query'=>$map_query);
		}
		public static function get_cluster($params,$id)
		{
			$p= new stdClass;
			$p->query='::contacts/'.$params->ids.'::';
			$p->page=$params->page;
			$p->nb=$params->nb;
			return Contacts::get_casquettes($p,0,$id);
		}
		public static function get_contact_prev_next($id_contact,$params,$id)
		{
			$db= new DB();
			$condition=Contacts::build_query($params->query,$id);
			$sql = "create table temp.tmp as SELECT
			t2.id_contact as id_contact,
			t1.nom as nom
			FROM contacts as t1
			inner join casquettes as t2 on t1.id=t2.id_contact
			WHERE $condition
			ORDER BY t1.sort ASC, t2.id ASC";
			$db->database->exec($sql);
			$sql = "select rowid, id_contact, (select rowid from temp.tmp where id_contact=$id_contact) as courant, (select count(*) from temp.tmp) as total
			from temp.tmp where
				rowid=courant-1
				OR
				rowid=courant+1
			;	";
			$res=array();
			$current=0;
			$total=0;
			$prev=0;
			$next=0;
			foreach($db->database->query($sql, PDO::FETCH_ASSOC) as $row){
				$current=$row['courant'];
				$total=$row['total'];
				if ($row['rowid']<$row['courant']) $prev=$row['id_contact'];
				if ($row['rowid']>$row['courant']) $next=$row['id_contact'];
			}
			$sql = "drop table temp.tmp;";
			$db->database->exec($sql);
			return array($prev,$next,$current,$total);
		}
		public static function get_casquettes($params=array('query'=>'','page'=>1,'nb'=>10),$id_cas=0,$id)
		{
			$db= new DB();
			$params=(object) $params;
			if (isset($params->query)) {
				$query=$params->query;
				$page=$params->page;
				$first=($params->page-1)*$params->nb;
				$nb=$params->nb;
			} else {
				$query='';
			}
			if ($id_cas!=0) {
				$query='';
				$page=1;
				$first=0;
				$nb=50;
			}
			$query=Contacts::build_query($query,$id);
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
				t4.nom as nom_cas_etab,
				t4.donnees as donnees_etab,
				t5.id as id_contact_etab,
				t5.nom as nom_etab,
				'[' || Group_Concat(DISTINCT '{\"id\":\"'||t6.id||'\",\"type\":\"1\",\"id_contact\":\"'||t6.id_contact||'\",\"nom\":\"'||replace(t7.nom,'\"','\\\"')||'\",\"prenom\":\"'||replace(t7.prenom,'\"','\\\"')||'\",\"fonction\":\"'||replace(t6.fonction,'\"','\\\"')||'\"}')||']' as cols
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
			foreach($db->database->query($sql, PDO::FETCH_ASSOC) as $row){
				$row['emails']=json_decode($row['emails']);
				$row['donnees']=json_decode($row['donnees']);
				if ($row['id_etab']>0) $row['donnees_etab']=json_decode($row['donnees_etab']);
				$row['cols']=json_decode($row['cols']);
				$row['tags']= $row['tags']!="" ? explode(',',$row['tags']) : array();
				$casquettes[]=$row;
			}
			$query='';
			return array('params'=>$params,'collection'=>$casquettes,'page'=>$page, 'nb'=>$nb, 'total'=>$total,'query'=>$query);
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
						$valeur="( (t2.cp!='') OR t2.id_etab in (SELECT id FROM casquettes WHERE (cp!='')))";
						break;
					case 'gpserreur':
						$valeur="( (t2.gps_x=1001))";
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
					case 'tag-parent':
						$valeur= "t2.id IN (SELECT id_cas FROM tag_cas WHERE id_tag=$param)";
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
					case 'clic-envoi':
						$valeur= "t2.id IN (SELECT id_cas FROM r WHERE id_envoi=$param)";
						break;
					case 'clic-news':
						$valeur= "t2.id IN (SELECT id_cas FROM r as t10 INNER JOIN envois as t11 ON t10.id_envoi=t11.id WHERE t11.type='news' AND t11.id_type=$param)";
						break;
					case 'clic-mail':
						$valeur= "t2.id IN (SELECT id_cas FROM r as t10 INNER JOIN envois as t11 ON t10.id_envoi=t11.id WHERE t11.type='mail' AND t11.id_type=$param)";
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
			$query = "SELECT *, (select count(*) from tag_cas where id_tag=t1.id) as nbcas FROM tags as t1";
			$tags=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				if (strlen($row['color'])==0) $row['color']='#333333';
				$row['options']=json_decode($row['options']);
				$tags[$row['id']]=$row;
			}
			foreach($tags as $i=>$t) {
				$ta=typeAncestor($t['id'],$tags);
				$tags[$i]['typeAncestor']=$ta['type'];
				$tags[$i]['idAncestor']=$ta['id'];
			}
			return $tags;
		}
		public static function get_selections() {
			$db= new DB();
			$query = "SELECT * FROM selections";
			$selections=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
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
			return array_unique($children);
		}
		public function del_tag($params,$id) {
			$t=Contacts::do_del_tag($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_tag($params,$id) {
			$db= new DB();
			$tag=$params->tag;
			$contacts=Contacts::get_cass_tag($tag->id);
			$db->database->beginTransaction();
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($tag->id,'tag',json_encode($tag),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM tags WHERE id=? ');
			$delete->execute(array($tag->id));
			foreach($contacts as $id_cas){
				$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
				$insert->execute(array($tag->id."|".$id_cas,'tag_cas',json_encode(array('id_tag'=>$tag->id,'id_cas'=>$id_cas)),millisecondes(),$id));
			}
			$delete = $db->database->prepare('DELETE FROM tag_cas WHERE id_tag=?');
			$delete->execute(array($tag->id));
			$db->database->commit();
			//ldap_update_array($contacts);
			$tab=array();
			$tab[]='contact/*';
			$tab[]='casquettes';
			$tab[]='tags';
			return array('maj'=>$tab,'res'=>1);
		}
		public function del_selection($params,$id) {
			$t=Contacts::do_del_selection($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_selection($params,$id) {
			$db= new DB();
			$selection=$params->selection;
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($selection->id,'selection',json_encode($selection),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM selections WHERE id=? ');
			$delete->execute(array($selection->id));
			return array('maj'=>array('selections'),'res'=>1);
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
		public function mod_contact($params,$id) {
			$t=Contacts::do_mod_contact($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_contact($params,$id) {
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
			$maj=array("contact/$id_contact");
			foreach(Contacts::get_casquettes_contact($id_contact) as $id_cas) {
				$cass[]=$id_cas;
				$cas=Contacts::get_casquette($id_cas,true,$id);
				$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
				$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']." ".$cas['nom_cas']))." ".idx( (object) $cas['donnees']),$cas['id']));
				foreach(Contacts::get_cols($id_cas) as $col) {
					$cass[]=$col['id'];
					$maj[]="contact/".$col['id_contact'];
					$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
					$update->execute(array(strtolower(normalizeChars($col['nom']." ".$col['prenom']." ".$col['nom_cas']))." ".idx( (object) $col['donnees']),$col['id']));
				}
			}
			if (count($cass)>0) ldap_update_array($cass);
			check_doublon_texte($id_contact);
			return array('maj'=>$maj,'res'=>1);
		}
		public function del_contact($params,$id) {
			$t=Contacts::do_del_contact($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_contact($params,$id) {
			$db= new DB();
			$id_contact=$params->cas->id_contact;
			$contact=Contacts::get_contact($id_contact,true,$id);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id_contact,'contact',json_encode($contact),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM contacts WHERE id=? ');
			$delete->execute(array($id_contact));
			$tab=array();
			$casres=array();
			$emails=array();
			foreach($contact['casquettes'] as $c){
				$emails=array_merge($emails,$c['emails']);
				$params=json_decode(json_encode(array('cas'=>$c)));
				$casres[]=Contacts::do_del_casquette($params,$id);
			}
			$emails=array_unique($emails);
			check_doublon_emails($emails);
			foreach($casres as $cr) {
				$tab=array_merge($tab, $cr['maj']);
			}
			doublon_maj($id_contact);
			return array('maj'=>$tab,'res'=>1);
		}
		public static function touch_contact($id_contact,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE contacts SET modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array(millisecondes(),$id,$id_contact));
			return 1;
		}
		public function set_mail_erreur($params,$id) {
			$t=Contacts::do_set_mail_erreur($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_set_mail_erreur($cas_id,$email,$id) {
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
			$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']." ".$cas['nom_cas']))." ".idx($donnees),$cas_id));
			Contacts::touch_contact($cas['id_contact'],$id);
			ldap_update($cas_id);
			return array('maj'=>array('contact/'.$cas['id_contact']), 'res'=>1);
		}
		public function remove_mail($params,$id) {
			$t=Contacts::do_remove_mail($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_remove_mail($email,$id) {
			$db= new DB();
			$query="select id from casquettes where emails like '%$email%'";
			$casquette_ids=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$casquette_ids[]=$row['id'];
			}
			$tab=array();
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
				$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']." ".$cas['nom_cas']))." ".idx($donnees),$cas_id));
				Contacts::touch_contact($cas['id_contact'],$id);
				ldap_update($cas_id);
				$tab[]='contact/'.$cas['id_contact'];
			}
			return array('maj'=>$tab, 'res'=>count($casquette_ids));
		}
		public function del_email_casquette($params,$id) {
			$t=Contacts::do_del_email_casquette($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_email_casquette($params,$id) {
			$db= new DB();
			$id_cas=$params->cas->id;
			$email=$params->cas->email;
			$cas=Contacts::get_casquette($id_cas,false,$id);
			$donnees=$cas['donnees'];
			foreach($donnees as $k=>$d) {
				if ($d->type=='email' && strpos($d->value,$email)!==false) {
					unset($donnees[$k]);
				}
			}
			$donnees=array_values($donnees);
			$update = $db->database->prepare('UPDATE casquettes SET donnees=?, emails=?, email_erreur=? WHERE id=?');
			$update->execute(array(json_encode($donnees),emails($donnees),0,$id_cas));
			$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
			$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']." ".$cas['nom_cas']))." ".idx($donnees),$id_cas));
			Contacts::touch_contact($cas['id_contact'],$id);
			$tab[]='contact/'.$cas['id_contact'];
			check_doublon_emails(array($email));
			return array('maj'=>$tab, 'res'=>1);
		}
		public static function check_contact_vide($id_contact,$id) {
			echo "check contact vide \n";
			$db= new DB();
			$query = "SELECT count(*) as nb FROM casquettes WHERE id_contact=$id_contact ";
			$nb=0;
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$nb=$row['nb'];
			}
			if ($nb==0) {
				$params=new stdClass;
				$params->cas=new stdClass;
				$params->cas->id_contact=$id_contact;
				return Contacts::do_del_contact($params,$id);
			}
		}
		public function mod_casquette($params,$id) {
			$t=Contacts::do_mod_casquette($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_casquette($params,$id,$gps=true) {
			$db= new DB();
			$cas=Contacts::get_casquette($params->cas->id,false,$id);
			$prev_etabs=Contacts::get_etabs_contact($params->cas->id_contact);
                   	$avant=$cas['donnees'];
			$apres=$params->cas->donnees;
			$t=millisecondes();
			$gps= array(
				'x'=> isset($cas['gps_x']) ? $cas['gps_x'] : 1000,
				'y'=> isset($cas['gps_y']) ? $cas['gps_y'] : 1000
			);
			foreach($apres as $k=>$d) {
				$new=true;
				foreach($avant as $a) {
					if ($a->label==$d->label && $a->type==$d->type) $new=false;
					if ($a->label==$d->label && $a->type==$d->type && $a->value!=$d->value) {
						$apres[$k]->date=$t;
						$apres[$k]->by=$id;
						if ($gps && $d->type=='adresse') $gps=get_gps($d->value);
					}
				}
				if ($new) {
					$apres[$k]->date=$t;
					$apres[$k]->by=$id;
					if ($gps && $d->type=='adresse') $gps=get_gps($d->value);
				}
			}
			$id_etab= isset($params->cas->id_etab) ? $params->cas->id_etab : 0 ;
			$update = $db->database->prepare('UPDATE casquettes SET nom=?, donnees=?, id_etab=?, modificationdate=?, modifiedby=?, emails=?, email_erreur=?, fonction=?, cp=?, gps_x=?, gps_y=? WHERE id=?');
			$emails=emails($apres);
			$update->execute(array($params->cas->nom_cas,json_encode($apres),$id_etab,$t,$id,$emails,email_erreur($apres),fonction($apres),cp($apres),$gps['x'],$gps['y'],$params->cas->id));
			$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
			$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']." ".$params->cas->nom_cas))." ".idx($apres),$params->cas->id));
			Contacts::touch_contact($params->cas->id_contact,$id);
			$mtc=Contacts::do_maj_cas_tag($params->cas->id,$params->cas->tags,$id);
			ldap_update($params->cas->id);
			check_doublon_emails(array_unique(array_merge(json_decode($emails),$cas['emails'])));
			$tab=array('contact/'.$params->cas->id_contact);
			if ($id_etab>0) $tab[]='contact/'.$id_etab;
			$tab=array_merge($tab,$mtc['maj']);
			foreach($prev_etabs as $idetab) {
				$tab[]='contact/'.$idetab;
			}
			return array('maj'=>$tab, 'res'=>1);
		}
		public function merge_casquette($params,$id) {
			$t=Contacts::do_merge_casquette($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_merge_casquette($params,$id) {
			$db= new DB();
			$s=Contacts::get_casquette($params->s->id,false,$id);
			$cas=Contacts::get_casquette($params->d->id,false,$id);
			$prev_s_etabs=Contacts::get_etabs_contact($s['id_contact']);
                   	$prev_d_etabs=Contacts::get_etabs_contact($cas['id_contact']);
                   	$dest=$cas['donnees'];
			$source=$s['donnees'];
			$t=millisecondes();
			$gps=array('x'=>1000,'y'=>1000);
			$has_adresse=false;
			$has_note=false;
			$has_fonction=false;
			foreach($dest as $dd) {
				if ($dd->type=='adresse') $has_adresse=true;
				if ($dd->type=='fonction') $has_fonction=true;
			}
			foreach($source as $ds) {
				$test=true;
				if ($has_adresse && $ds->type=='adresse') $test=false;
				if ($has_fonction && $ds->type=='fonction') $test=false;
				foreach($dest as $k=>$dd) {
					if ($dd->type==$ds->type && $dd->value==$ds->value) $test=false;
					if ($ds->type=='note' && $dd->type=='note') {
						$dest[$k]->value=$dd->value."\n".$ds->value;
						$test=false;
					}
				}
				if ($test) {
					$dest[]=$ds;
					if ($ds->type=='adresse') $gps=get_gps($ds->value);
				}
			}
			if (isset($cas['id_etab']) && $cas['id_etab']>0) {
				$id_etab=$cas['id_etab'];
			} else {
				if (isset($cas['id_etab']) && $cas['id_etab']>0) {
					$id_etab=$s['id_etab'];
				} else $id_etab=0;
			}
			$update = $db->database->prepare('UPDATE casquettes SET donnees=?, id_etab=?, modificationdate=?, modifiedby=?, emails=?, email_erreur=?, fonction=?, cp=?, gps_x=?, gps_y=? WHERE id=?');
			$update->execute(array(json_encode($dest),$id_etab,$t,$id,emails($dest),email_erreur($dest),fonction($dest),cp($dest),$gps['x'],$gps['y'],$params->d->id));
			$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
			$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']." ".$cas['nom_cas']))." ".idx($dest),$params->d->id));
			Contacts::touch_contact($cas['id_contact'],$id);
			$mtc=Contacts::do_maj_cas_tag($params->d->id,array_merge($cas['tags'],$s['tags']),$id);
			ldap_update($params->d->id);
			$params_suivis=new stdClass;
			$params_suivis->d=new stdClass;
			$params_suivis->s=new stdClass;
			$params_suivis->d->id=$cas['id'];
			$params_suivis->s->id=$s['id'];
			$suivis=Suivis::do_move_suivis_casquette($params_suivis,$id);
			//$envois=Mailing::do_move_envoi_cas_casquette($params_suivis,$id);
			$tab=array('contact/'.$cas['id_contact']);
			$tab=array_merge($tab,$mtc['maj']);
			$tab=array_merge($tab,$suivis['maj']);
			//on déplace les collaborateurs
			if (is_array($s['cols'])) {
				foreach($s['cols'] as $col) {
					$update = $db->database->prepare('UPDATE casquettes SET id_etab=? WHERE id=?');
					$update->execute(array($cas['id'],$col->id));
					$tab[]='contact/'.$col->id_contact;
				}
			}
			foreach($prev_s_etabs as $idetab) {
				$tab[]='contact/'.$idetab;
			}
			foreach($prev_d_etabs as $idetab) {
				$tab[]='contact/'.$idetab;
			}
			return array('maj'=>$tab, 'res'=>1);
		}
		public function move_casquette($params,$id) {
			$t=Contacts::do_move_casquette($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_move_casquette($params,$id,$gps=true) {
			$db= new DB();
			$casb=Contacts::get_casquette($params->cas->id,false,$id);
			$prev_etabs=Contacts::get_etabs_contact($casb['id_contact']);
			$update = $db->database->prepare('UPDATE casquettes SET id_contact=? WHERE id=?');
			$update->execute(array($params->cas->id_contact,$params->cas->id));
			$casa=Contacts::get_casquette($params->cas->id,false,$id);
			$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
			$update->execute(array(strtolower(normalizeChars($casa['nom']." ".$casa['prenom']." ".$casa['nom_cas']))." ".idx($casa['donnees']),$params->cas->id));
			Contacts::touch_contact($params->cas->id_contact,$id);
			ldap_update($params->cas->id);
			$tab=array('contact/'.$params->cas->id_contact);
			#si la casquette a été déplacée on vérifie que le contact n'est pas vide
			$tcheck=Contacts::check_contact_vide($casb['id_contact'],$id);
			$tab=array_merge($tab,$tcheck['maj']);
			check_doublon_texte($params->cas->id_contact);
			foreach($prev_etabs as $idetab) {
				$tab[]='contact/'.$idetab;
			}
			return array('maj'=>$tab, 'res'=>1);
		}
		public function add_contact($params,$id) {
			$t=Contacts::do_add_contact($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_contact($params,$id) {
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
			check_doublon_texte($id_contact);
			ldap_update($id_cas);
			return array('maj'=>array('casquettes'), 'res'=>$id_contact);
		}
		public function add_casquette($params,$id) {
			$t=Contacts::do_add_casquette($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_casquette($params,$id) {
			$db= new DB();
			$insert = $db->database->prepare('INSERT INTO casquettes (nom,donnees,emails,email_erreur,fonction,cp,gps_x,gps_y,id_etab,id_contact,creationdate,createdby,modificationdate,modifiedby) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
			$insert->execute(array($params->cas->nom_cas,'[]','[]',0,'','',1000,1000,0,$params->cas->id_contact,millisecondes(),$id,millisecondes(),$id));
			$params->cas->id = $db->database->lastInsertId();
			$insert = $db->database->prepare('INSERT INTO casquettes_fts (id,idx) VALUES (?,?)');
			$insert->execute(array($params->cas->id,strtolower(normalizeChars($params->cas->nom." ".$params->cas->prenom))));
			$t=Contacts::do_mod_casquette($params,$id);
			ldap_update($params->cas->id);
			return array('maj'=>$t['maj'], 'res'=>1);
		}
		public function move_tag($params,$id) {
			$t=Contacts::do_move_tag($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_move_tag($params,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE tags SET id_parent=?, modificationdate=?, modifiedby=? WHERE id=? ');
			$update->execute(array($params->parent->id,millisecondes(),$id,$params->tag->id));
			return array('maj'=>array('casquettes','tags'), 'res'=>1);
		}
		public function mod_tag($params,$id) {
			$t=Contacts::do_mod_tag($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_tag($params,$id) {
			$db= new DB();
			$opts= isset($params->tag->options) ? json_encode($params->tag->options) : "{}";
			$update = $db->database->prepare('UPDATE tags SET nom=?, type=?, options=?, color=?, modificationdate=?, modifiedby=? WHERE id=? ');
			$update->execute(array($params->tag->nom,$params->tag->type,$opts,$params->tag->color,millisecondes(),$id,$params->tag->id));
			return array('maj'=>array('tags'), 'res'=>1);
		}
		public function mod_selection($params,$id) {
			$t=Contacts::do_mod_selection($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_mod_selection($params,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE selections SET nom=?, query=?, modificationdate=?, modifiedby=? WHERE id=? ');
			$update->execute(array($params->selection->nom,$params->selection->query,millisecondes(),$id,$params->selection->id));
			return array('maj'=>array('selections'), 'res'=>1);
		}
		public function add_tag($params,$id) {
			$t=Contacts::do_add_tag($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_tag($params,$id) {
			$db= new DB();
			$insert = $db->database->prepare('INSERT INTO tags (nom, color, id_parent, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?,?) ');
			$insert->execute(array($params->tag->nom,$params->tag->color,0,millisecondes(),$id,millisecondes(),$id));
			$id_tag = $db->database->lastInsertId();
			return array('maj'=>array('tags'), 'res'=>$id_tag);
		}
		public function add_selection($params,$id) {
			$t=Contacts::do_add_selection($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_selection($params,$id) {
			$db= new DB();
			$insert = $db->database->prepare('INSERT INTO selections (nom, query, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?) ');
			$insert->execute(array($params->selection->nom,$params->selection->query,millisecondes(),$id,millisecondes(),$id));
			return array('maj'=>array('selections'), 'res'=>1);
		}
		public function add_cas_tag($params,$id) {
			$t=Contacts::do_add_cas_tag($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_cas_tag($params,$id) {
			$db= new DB();
			$tag=Contacts::get_tag($params->tag->id);
			if($tag['type']!='liste' && $tag['type']!='boutons') {
				$tags=Contacts::get_tags();
				$p=hasListAncestor($params->tag->id, $tags);
				//echo "parent : \n".var_export($p,true);
				if (is_array($p)) {
					$desc=descendants($p, $tags);
					//echo "descendants : \n".var_export($desc,true);
					foreach($desc as $d) {
						if ($d['id']!=$params->tag->id) {
							$delete = $db->database->prepare('DELETE FROM tag_cas WHERE id_tag=? AND id_cas=?');
							$delete->execute(array($d['id'],$params->cas->id));
						}
					}
				}
				$insert = $db->database->prepare('INSERT OR REPLACE INTO tag_cas (id_tag,id_cas,date) VALUES (?,?,?)');
				$insert->execute(array($params->tag->id,$params->cas->id,millisecondes()));
				ldap_update($params->cas->id);
			}
		   	return array('maj'=>array('contact/'.$params->cas->id_contact,'tags'), 'res'=>1);
		}
		public static function get_doublons_texte($params,$id) {
			$db= new DB();
			$total=0;
			$query_count = "SELECT count(*) as nb
			FROM doublons_texte as t1
			WHERE t1.id_doublon=t1.id_contact
			AND (SELECT count(*) FROM doublons_texte WHERE id_doublon=t1.id_doublon AND id_doublon || ',' || id_contact NOT IN (SELECT id_doublon || ',' || id_contact FROM non_doublons_texte) )>1";
			foreach($db->database->query($query_count, PDO::FETCH_ASSOC) as $row){
				$total=$row['nb']+0;
			}
			$query = "SELECT t1.id_doublon as id_doublon,
			t2.id as id_contact,
			t2.nom as nom,
			t2.prenom as prenom,
			t2.type as type,
			t2.sort as sort,
			t20.sort as sort_doublon,
			t3.id as id,
			t3.nom as nom_cas,
			t3.id_etab as id_etab,
			t3.donnees as donnees,
			t3.emails as emails,
			t3.fonction as fonction,
			t3.email_erreur as email_erreur,
			t3.cp as cp,
			t3.gps_x as gps_x,
			t3.gps_y as gps_y,
			t3.creationdate as cas_creationdate,
			t3.createdby as cas_createdby,
			t3.modificationdate as cas_modificationdate,
			t3.modifiedby as cas_modifiedby,
			Group_Concat(DISTINCT t4.id_tag) as tags,
			t5.nom as nom_cas_etab,
			t5.donnees as donnees_etab,
			t6.id as id_contact_etab,
			t6.nom as nom_etab,
			'[' || Group_Concat(DISTINCT '{\"id\":\"'||t7.id||'\",\"type\":\"1\",\"id_contact\":\"'||t7.id_contact||'\",\"nom\":\"'||replace(t8.nom,'\"','\\\"')||'\",\"prenom\":\"'||replace(t8.prenom,'\"','\\\"')||'\",\"fonction\":\"'||replace(t7.fonction,'\"','\\\"')||'\"}')||']' as cols
			FROM doublons_texte as t1
			left outer join contacts as t2 on t1.id_contact=t2.id
			left outer join contacts as t20 on t1.id_doublon=t20.id
			left outer join casquettes as t3 on t3.id_contact=t2.id
			left outer join tag_cas as t4 on t3.id=t4.id_cas
			left outer join casquettes as t5 on t5.id=t3.id_etab
			left outer join contacts as t6 on t6.id=t5.id_contact
			left outer join casquettes as t7 on t7.id_etab=t3.id
			left outer join contacts as t8 on t8.id=t7.id_contact
			where t1.id_doublon IN (
				SELECT id_doublon
				FROM doublons_texte as t1
				inner join contacts as t2 on t1.id_doublon=t2.id
				WHERE id_doublon=id_contact
				AND (SELECT count(*) FROM doublons_texte WHERE id_doublon=t1.id_doublon AND id_doublon || ',' || id_contact NOT IN (SELECT id_doublon || ',' || id_contact FROM non_doublons_texte) )>1
				ORDER BY t2.sort ASC
				LIMIT ".(($params->page-1)*$params->nb).", ".$params->nb."
			) AND t1.id_doublon || ',' || t1.id_contact NOT IN (SELECT id_doublon || ',' || id_contact FROM non_doublons_texte)
			group by t3.id
			ORDER BY t20.sort,id_doublon,id_contact ASC
			";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				if (!array_key_exists("".$row['id_doublon'],$res)) $res[$row['id_doublon']]=array('sort'=>$row['sort_doublon'],'cass'=>array());

				$row['emails']=json_decode($row['emails']);
				$row['donnees']=json_decode($row['donnees']);
				if ($row['id_etab']>0) $row['donnees_etab']=json_decode($row['donnees_etab']);
				$row['cols']=json_decode($row['cols']);
				$row['tags']= $row['tags']!="" ? explode(',',$row['tags']) : array();
				$res[$row['id_doublon']]['cass'][]=$row;
			}
			return array('params'=>$params,'collection'=>$res,'page'=>$params->page, 'nb'=>$params->nb, 'total'=>$total);
		}
		public static function get_doublons_email($params,$id) {
			$db= new DB();
			$total=0;
			$query_count = "SELECT count(distinct email) as nb FROM doublons_email";
			foreach($db->database->query($query_count, PDO::FETCH_ASSOC) as $row){
				$total=$row['nb']+0;
			}
			$query = "SELECT t1.email as email,
			t2.id as id_contact,
			t2.nom as nom,
			t2.prenom as prenom,
			t2.type as type,
			t2.sort as sort,
			t3.id as id,
			t3.nom as nom_cas,
			t3.id_etab as id_etab,
			t3.donnees as donnees,
			t3.emails as emails,
			t3.fonction as fonction,
			t3.email_erreur as email_erreur,
			t3.cp as cp,
			t3.gps_x as gps_x,
			t3.gps_y as gps_y,
			t3.creationdate as cas_creationdate,
			t3.createdby as cas_createdby,
			t3.modificationdate as cas_modificationdate,
			t3.modifiedby as cas_modifiedby,
			Group_Concat(DISTINCT t4.id_tag) as tags,
			t5.nom as nom_cas_etab,
			t5.donnees as donnees_etab,
			t6.id as id_contact_etab,
			t6.nom as nom_etab,
			'[' || Group_Concat(DISTINCT '{\"id\":\"'||t7.id||'\",\"type\":\"1\",\"id_contact\":\"'||t7.id_contact||'\",\"nom\":\"'||replace(t8.nom,'\"','\\\"')||'\",\"prenom\":\"'||replace(t8.prenom,'\"','\\\"')||'\",\"fonction\":\"'||replace(t7.fonction,'\"','\\\"')||'\"}')||']' as cols
			FROM doublons_email as t1
			left outer join casquettes as t3 on t3.id=t1.id_casquette
			left outer join contacts as t2 on t3.id_contact=t2.id
			left outer join tag_cas as t4 on t3.id=t4.id_cas
			left outer join casquettes as t5 on t5.id=t3.id_etab
			left outer join contacts as t6 on t6.id=t5.id_contact
			left outer join casquettes as t7 on t7.id_etab=t3.id
			left outer join contacts as t8 on t8.id=t7.id_contact
			where t1.email IN (
				SELECT email
				FROM doublons_email
				GROUP BY email
				ORDER BY email
				LIMIT ".(($params->page-1)*$params->nb).", ".$params->nb."
			)
			group by t3.id
			ORDER BY email,id_contact ASC
			";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				if (!array_key_exists($row['email'],$res)) $res[$row['email']]=array('sort'=>$row['email'],'cass'=>array());
				$row['emails']=json_decode($row['emails']);
				$row['donnees']=json_decode($row['donnees']);
				if ($row['id_etab']>0) $row['donnees_etab']=json_decode($row['donnees_etab']);
				$row['cols']=json_decode($row['cols']);
				$row['tags']= $row['tags']!="" ? explode(',',$row['tags']) : array();
				$res[$row['email']]['cass'][]=$row;
			}
			return array('params'=>$params,'collection'=>$res,'page'=>$params->page, 'nb'=>$params->nb, 'total'=>$total, 'query'=>$query);
		}
		public static function do_del_doublons_texte($tab) {
			$db= new DB();
			$delete = $db->database->exec('DELETE FROM doublons_texte WHERE id_doublon IN ( SELECT id_doublon FROM doublons_texte WHERE id_contact in ('.implode(', ', $tab).') )');
			return array('maj'=>array('*'), 'res'=>1);
		}
		public function non_doublon_texte($params,$id) {
			$t=Contacts::do_non_doublon_texte($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_non_doublon_texte($params,$id) {
			$db= new DB();
			$insert = $db->database->prepare("INSERT INTO non_doublons_texte (id_doublon, id_contact, date) VALUES (?,?,?)");
			$insert->execute(array($params->id_doublon, $params->id_contact,millisecondes()));
			return array('maj'=>array('*'), 'res'=>1);
		}
		public static function do_del_doublon_texte($id_contact) {
			$db= new DB();
			$query = "SELECT count(*) as nb FROM doublons_texte WHERE id_doublon IN ( SELECT id_doublon FROM doublons_texte WHERE id_contact=$id_contact )";
			$nb=0;
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$nb=$row['nb'];
			}
			$maj=array();
			if ($nb>0) {
				$delete = $db->database->exec("DELETE FROM doublons_texte WHERE id_doublon IN ( SELECT id_doublon FROM doublons_texte WHERE id_contact=$id_contact )");
				$maj[]='*';
			}
			return array('maj'=>$maj, 'res'=>1);
		}
		public static function do_add_doublons_texte($tab) {
			$db= new DB();
			Contacts::do_del_doublons_texte($tab);
			$min=0;
			foreach($tab as $c) {
				if ($min==0) $min=$c;
				else $min=min($min,$c);
			}
			foreach($tab as $c) {
				$insert = $db->database->prepare('INSERT OR REPLACE INTO doublons_texte (id_doublon,id_contact,date) VALUES (?,?,?)');
				$insert->execute(array($min,$c,millisecondes()));
			}
			return array('maj'=>array('*'), 'res'=>1);
		}
		public static function do_add_doublons_email($email,$tab) {
			$db= new DB();
			Contacts::do_del_doublons_email($email);
			foreach($tab as $c) {
				$insert = $db->database->prepare('INSERT OR REPLACE INTO doublons_email (email,id_casquette,date) VALUES (?,?,?)');
				$insert->execute(array($email,$c,millisecondes()));
			}
			return array('maj'=>array('*'), 'res'=>1);
		}
		public static function do_del_doublons_email($email) {
			$db= new DB();
			$delete = $db->database->prepare('DELETE FROM doublons_email WHERE email=?');
			$delete->execute(array($email));
			return array('maj'=>array('*'), 'res'=>1);
		}
		public static function do_maj_cas_tag($id_cas,$liste,$id) {
			$db= new DB();
			$db->database->beginTransaction();
			$query = "SELECT t1.id_tag FROM tag_cas as t1 inner join tags as t2 on t1.id_tag=t2.id WHERE id_cas=$id_cas ORDER BY t2.nom";
			$tags=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$tags[]=$row['id_tag'];
			}
			foreach($liste as $tl){
				if (!in_array($tl,$tags)) {
					$insert = $db->database->prepare('INSERT OR REPLACE INTO tag_cas (id_tag,id_cas,date) VALUES (?,?,?)');
					$insert->execute(array($tl,$id_cas,millisecondes()));
				}
			}
			foreach($tags as $tdb){
				if (!in_array($tdb,$liste)) {
					$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
					$insert->execute(array($tdb."|".$id_cas,'tag_cas',json_encode(array('id_tag'=>$tdb,'id_cas'=>$id_cas)),millisecondes(),$id));
					$delete = $db->database->prepare('DELETE FROM tag_cas WHERE id_tag=? AND id_cas=? ');
					$delete->execute(array($tdb,$id_cas));
				}
			}
			$db->database->commit();
			return array('res'=>1,'maj'=>array('tags'));
		}
		public function del_cas_tag($params,$id) {
			$t=Contacts::do_del_cas_tag($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_cas_tag($params,$id) {
			$db= new DB();
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($params->tag->id."|".$params->cas->id,'tag_cas',json_encode(array('id_tag'=>$params->tag->id,'id_cas'=>$params->cas->id,'id_contact'=>$params->cas->id_contact)),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM tag_cas WHERE id_tag=? AND id_cas=? ');
			$delete->execute(array($params->tag->id,$params->cas->id));
			ldap_update($params->cas->id);
			return array('maj'=>array('contact/'.$params->cas->id_contact,'tags'), 'res'=>1);
		}
		public function des_ass_etablissement($params,$id) {
			$t=Contacts::do_des_ass_etablissement($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_des_ass_etablissement($params,$id) {
			$db= new DB();
			$cas=Contacts::get_casquette($params->cas->id,false,$id);
			$update = $db->database->prepare('UPDATE casquettes SET id_etab=0 WHERE id=?');
			$update->execute(array($params->cas->id));
			ldap_update($params->cas->id);
			return array('maj'=>array('contact/'.$cas['id_contact'],'contact/'.$cas['id_contact_etab'],'tags'), 'res'=>1);
		}
		public function ass_casquette($params,$id) {
			$t=Contacts::do_ass_casquette($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_ass_casquette($params,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE casquettes SET id_etab=? WHERE id=?');
			$update->execute(array($params->id_etab,$params->id_cas));
			ldap_update($params->id_cas);
			return array('maj'=>array('contact/*','tags'), 'res'=>1);
		}
		public function add_panier_tag($params,$id) {
			$t=Contacts::do_add_panier_tag($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_panier_tag($params, $id) {
			$tag=Contacts::get_tag($params->tag->id);
			if($tag['type']!='liste' && $tag['type']!='boutons') {
				$db= new DB();
				$t=millisecondes();
				$panier=User::get_panier($id);
				$tags=Contacts::get_tags();
				$db->database->beginTransaction();

				$p=hasListAncestor($params->tag->id, $tags);
				if (is_array($p)) {
					$desc=descendants($p, $tags);
					foreach($desc as $d) {
						if ($d['id']!=$params->tag->id) {
							$delete = $db->database->prepare('DELETE FROM tag_cas WHERE id_tag=? AND id_cas in ('.implode(',',$panier).')');
							$delete->execute(array($d['id']));
						}
					}
				}
				foreach($panier as $id_cas){
					$insert = $db->database->prepare('INSERT OR REPLACE INTO tag_cas (id_tag,id_cas,date) VALUES (?,?,?)');
					$insert->execute(array($params->tag->id,$id_cas,$t));
				}
				$db->database->commit();
			}
			return array('maj'=>array("casquettes","contact/*","suivis","tags"), 'res'=>1);
		}
		public function del_panier_tag($params,$id) {
			$t=Contacts::do_del_panier_tag($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_panier_tag($params,$id) {
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
			return array('maj'=>array("casquettes","contact/*","suivis","tags"), 'res'=>count($panier));
		}
		public function del_casquette($params,$id) {
			$cas_left=Contacts::get_casquettes_contact($params->cas->id_contact);
			if (count($cas_left)==1) {
				$t=Contacts::do_del_contact($params,$id);
			} else {
				$t=Contacts::do_del_casquette($params,$id);
			}
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_casquette($params,$id) {
			$db= new DB();
			$cas=Contacts::get_casquette($params->cas->id,true,$id);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($params->cas->id,'casquette',json_encode($cas),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM casquettes WHERE id=? ');
			$delete->execute(array($params->cas->id));
			$delete = $db->database->prepare('DELETE FROM tag_cas WHERE id_cas=? ');
			$delete->execute(array($params->cas->id));
			$maj_tab=array();
			foreach($cas['suivis'] as $id_thread=>$thread){
				$p=new stdClass;
				$p->id=$id_thread;
				$st=Suivis::do_del_suivis_thread($p,$id);
				$maj_tab[]=$st['maj'];
			}
			$update = $db->database->prepare('UPDATE casquettes SET id_etab=0, modificationdate=?, modifiedby=? WHERE id_etab=?');
			$update->execute(array(millisecondes(),$id,$params->cas->id));
			Contacts::touch_contact($params->cas->id_contact,$id);
			$p= (object) array('nouveaux'=>array($params->cas->id));
			$tabUser=User::do_del_panier($p,$id);
			check_doublon_emails($cas['emails']);
			$tab=array_merge($maj_tab,$tabUser['maj'], array("casquettes","contact/*","suivis","panier"));
			return array('maj'=>$tab, 'res'=>1);
		}
		public function del_casquettes_panier($params,$id) {
			$t=Contacts::do_del_casquettes_panier($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_del_casquettes_panier($params,$id) {
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
			$t=User::do_del_panier($p,$id);
			$tab=array("casquettes","contact/*","suivis","panier");
			return array('maj'=>array_merge($t['maj'],$tab), 'res'=>1);
		}
		public function un_error_email_panier($params,$id) {
			$t=Contacts::do_un_error_email_panier($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_un_error_email_panier($params,$id) {
			$db= new DB();
			$cass=Contacts::get_casquettes(array('query'=>'::panier::','page'=>1,'nb'=>10,'all'=>1),0,$id);
			$db->database->beginTransaction();
			foreach($cass['collection'] as $cas){
				$donnees=$cas['donnees'];
				$t=millisecondes();
				foreach($donnees as $k=>$d) {
					if ($d->type=='email_erreur') {
						$donnees[$k]->type='email';
						$donnees[$k]->date=$t;
						$donnees[$k]->by=$id;
					}
				}
				$update = $db->database->prepare('UPDATE casquettes SET donnees=?, emails=?, email_erreur=? WHERE id=?');
				$update->execute(array(json_encode($donnees),emails($donnees),0,$cas['id']));
				$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
				$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']." ".$cas['nom_cas']))." ".idx($donnees),$cas['id']));
				$update = $db->database->prepare('UPDATE contacts SET modificationdate=?, modifiedby=? WHERE id=?');
				$update->execute(array(millisecondes(),$id,$cas['id_contact']));
			}
			$db->database->commit();
			$tab=array("casquettes","contact/*","suivis","panier");
			return array('maj'=>$tab, 'res'=>1);
		}
		public function cas_has_tag($id_cas,$id_tag)
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
		public function index_gps(){
			$command = "nohup /usr/bin/php exec.php get_gps > /dev/null 2>&1 &";
			exec($command);
		}
		public function add_nb_contacts($params,$id) {
			$t=Contacts::do_add_nb_contacts($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_nb_contacts($params,$id){
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
			return array('maj'=>array('*'), 'res'=>1);
		}
		public function add_nb_csv($params,$id) {
			$t=Contacts::do_add_nb_csv($params,$id);
			$this->WS->maj($t['maj']);
			return $t['res'];
		}
		public static function do_add_nb_csv($params,$id){
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
			Contacts::index_gps();
			return array('maj'=>array('*'), 'res'=>1);
		}
	}
?>
