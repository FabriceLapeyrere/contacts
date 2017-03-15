<?php
	class Mailing
	{
		//mails
		public static function add_mail($params,$id) {
			$db= new DB();
			$sujet=$params->mail->sujet;
			$insert = $db->database->prepare('INSERT INTO emails (sujet, html, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?)');
			$insert->execute(array($sujet, '', millisecondes(), $id, millisecondes(), $id));
			$id_mail = $db->database->lastInsertId();
			CR::maj(array("mails","mail/$id_mail"));
			return $id_mail;
		}
		public static function mod_mail($params,$id) {
			$db= new DB();
			$id_mail=$params->mail->id;
			$sujet=$params->mail->sujet;
			$html=$params->mail->html;
			$update = $db->database->prepare('UPDATE emails SET sujet=?, html=?, modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array($sujet, $html, millisecondes(), $id, $id_mail));
			CR::maj(array("mails","mail/$id_mail"));
		}
		public static function get_mail($id) {
			return Mailing::get_mails($id);
		}
		public static function del_mail($params,$id) {
			$db= new DB();
			$id_mail=$params->mail->id;
			$mail=Mailing::get_mail($id_mail);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id,'mail',json_encode($mail),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM emails WHERE id=?');
			$delete->execute(array($id_mail));
			CR::maj(array("mails","mail/$id_mail"));
		}
		public static function get_mails($id=0) {
			$db= new DB();
			if ($id>0) $query = "SELECT * FROM emails where id=$id";
			else $query = "SELECT * FROM emails ORDER BY id ASC";
			$mails=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				if ($id>0) {
					$row['pjs']=array();
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					foreach(glob("./data/files/mail/".$row['id']."/*") as $f){
						if (is_file($f)) {
							$row['pjs'][]=array(
								"path"=>$f,
								"filename"=>basename($f),
								"mime"=>finfo_file($finfo, $f)
							);
						}
					}
				}
				$row['verrou']=WS::get_verrou('mail/'.$row['id']);
				$mails[$row['id']]=$row;
			}
			if ($id>0) return $row;
			return $mails;
		}	
		public static function touch_mail($id_mail,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE emails SET modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array(millisecondes(), $id, $id_mail));
			CR::maj(array("mail/$id_mail"));	
			return 1;
		}
		public static function del_mail_pj($params,$id)
		{
			$db= new DB();
			$id_mail=$params->id;
			$pj=$params->pj;
			unlink("./data/files/mail/$id_mail/".$pj->filename);
			Mailing::touch_mail($id_mail,$id);
			return 1;
		}
		//news
		public static function add_news($params,$id) {
			$db= new DB();
			$sujet= isset($params->news->sujet) ? $params->news->sujet : "";
			$t=millisecondes();
			$insert = $db->database->prepare('INSERT INTO news (sujet, blocs, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?)');
			$insert->execute(array($sujet, '[]', $t, $id, $t, $id));
			$id_news = $db->database->lastInsertId();
			CR::maj(array("newss","news/$id_news"));
			return $id_news;
		}
		public static function dup_news($params,$id) {
			$db= new DB();
			$news=Mailing::get_news($params->news->id,$id);
			$t=millisecondes();
			$insert = $db->database->prepare('INSERT INTO news (sujet, blocs, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?)');
			$insert->execute(array($news['sujet']." (copie)", json_encode($news['blocs']), $t, $id, $t, $id));
			$id_news = $db->database->lastInsertId();
			smartCopy("./data/files/news/".$params->news->id, "./data/files/news/$id_news");
			CR::maj(array("newss","news/$id_news"));
			return $id_news;
		}
		public static function mod_news($params,$id) {
			$db= new DB();
			$id_news=$params->news->id;
			$sujet=$params->news->sujet;
			$blocs=$params->news->blocs;
			$t=millisecondes();
			$update = $db->database->prepare('UPDATE news SET sujet=?, blocs=?, modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array($sujet, json_encode($blocs), $t, $id, $id_news));
			CR::maj(array("newss","news/$id_news"));
			return $id;
		}
		public static function get_news($id_news,$id) {
			return Mailing::get_newss($id_news,$id);
		}
		public static function del_news($params,$id) {
			$db= new DB();
			$id_news=$params->news->id;
			$news=Mailing::get_news($id_news);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id_news,'news',json_encode($news),millisecondes(),$id));
			$delete = $db->database->prepare('DELETE FROM news WHERE id=?');
			$delete->execute(array($id_news));
			CR::maj(array("newss","news/$id_news"));
		}
		public static function get_newss($id_news=0,$id) {
			$db= new DB();
			$query = "SELECT * FROM news";
			if ($id_news>0){
				$query .= "
					 WHERE id=$id_news";
			} else {
				$query .= "
					 ORDER BY id ASC";
			}
			$newss=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['verrou']=WS::get_verrou('news/'.$row['id']);
				if ($id_news>0) {
					$row['blocs']=json_decode($row['blocs']);
					if (is_array($row['blocs'])){
						foreach($row['blocs'] as $k=>$b){
							$row['blocs'][$k]->verrou=WS::get_verrou('newsbloc/'.$row['id'].'/'.$k);
							$row['blocs'][$k]->donnees=Mailing::donnees_modele($row['id'],$row['blocs'][$k]->id_modele,isset($row['blocs'][$k]->donnees) ? $row['blocs'][$k]->donnees : array());
							$hb=Mailing::html_bloc($row['id'],$row['blocs'][$k]->id_modele,$row['blocs'][$k]->donnees,$id,$k);
							$row['blocs'][$k]->html=$hb[0];
							$row['blocs'][$k]->donneeshtml=$hb[1];
						}
					} else {
						$row['blocs']=array();
					}
					$row['pjs']=array();
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					foreach(glob("data/files/news/".$row['id']."/*") as $f){
						if (is_file($f)) {
							$used=false;
							foreach($row['blocs'] as $b){
								foreach($b->donnees as $d){
									if (is_object($d) && $d->valeur==$f){
										$used=true;
									}
									if (is_object($d) && is_object($d->valeur)){
										foreach($d->valeur as $do){
								 			if (is_object($do) && $do->valeur==$f){
												$used=true;
											}
										}
									}
								}
							}
							$row['pjs'][]=array(
								"path"=>$f,
								"filename"=>basename($f),
								"mime"=>finfo_file($finfo, $f),
								"used"=>$used
							);
						}
					}
				} else {
					unset($row['blocs']);
				}
				$newss[$row['id']]=$row;
			}
			if ($id_news>0) return $row;
			return $newss;
		}	
		//news modeles
		public static function donnees_modele($id_news,$id_modele,$donnees) {
			$donnees_ok=array();
			$modele=Mailing::get_modele($id_modele);
			$pattern = "/::([^::]*)::/";
			preg_match_all($pattern, $modele['modele'], $matches, PREG_OFFSET_CAPTURE, 3);
			$donnees_modele=array();
			foreach($matches[0] as $key=>$value){
				$code=$matches[0][$key][0];
				$tab=explode('&',$matches[1][$key][0]);
				$d=new stdClass;					
				$d->nom=filter($tab[1]);
				$d->type=$tab[0];
				$d->valeur=null;
				$d->label=$tab[1];
				$donnees_modele[]=$d;
			}
			foreach($donnees as $k=>$donnee){
				$donnees_ok[]=$donnee;
			}
			foreach($donnees_modele as $d) {
				$test=false;
				//on rajoute les nouvelles données
				foreach($donnees as $k=>$donnee){
					if (isset($donnee->nom) && $donnee->nom==$d->nom && $donnee->type==$d->type)
						$test=true;
				}
				if (!$test) {
					$d->valeur=null;
					$donnees_ok[]=$d;
				}
			}
			//on enlève les données obsolètes
			foreach($donnees_ok as $k=>$donnee){
				$test=false;
				foreach($donnees_modele as $d) {
					if (isset($donnee->nom) && $donnee->nom==$d->nom && $donnee->type==$d->type)
						$test=true;
				}
				if (!$test) {
					unset($donnees_ok[$k]);
				}
			}
			return $donnees_ok;
		}
		public static function html_bloc($id_news,$id_modele,$donnees,$id,$k) {
			global $C;
			$modele=Mailing::get_modele($id_modele);
			$nom=$modele['nom'];
			$tab=explode('::',$nom);
			$modCat='Sans thème';
			if (count($tab)>0) $modCat=$tab[0];
			$donneeshtml=json_encode('{}');
			$html=$modele['modele'];
			$pattern = "/::([^::]*)::/";
			preg_match_all($pattern, $modele['modele'], $matches, PREG_OFFSET_CAPTURE, 3);
			foreach($matches[0] as $key=>$value){
				$code=$matches[0][$key][0];
				$tab=explode('&',$matches[1][$key][0]);
				$type=$tab[0];
				$label=$tab[1];
				$nom=filter($label);
				$valeur='';
				foreach($donnees as $k=>$donnee){
					if (isset($donnee->nom) && $donnee->nom==$nom && $donnee->type==$type)
						$valeur=$donnee->valeur;
				}
				if(file_exists("data/news_elements/elt_$type.php")) include "data/news_elements/elt_$type.php";
				elseif(file_exists("server/news_elements/elt_$type.php")) include "server/news_elements/elt_$type.php";
				$html=str_replace($code,$valeur,$html);
			}
			$html=str_replace('::code::',$html,$C->news->wrapper->value);
			return array($html,$donneeshtml);
		}
		public static function del_news_pj($params,$id)
		{
			$id_news=$params->id;
			$pj=$params->pj;
			unlink("./data/files/news/$id_news/".$pj->filename);
			Mailing::touch_news($id_news,$id);
			return 1;
		}
		public static function add_modele($params,$id) {
			$db= new DB();
			$nom=$params->modele->nom;
			$insert = $db->database->prepare('INSERT INTO news_modeles (nom, modele, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?)');
			$insert->execute(array($nom, '', millisecondes(), $id, millisecondes(), $id));
			$id_modele = $db->database->lastInsertId();
			return $id_modele;
		}
		public static function mod_modele($params,$id) {
			$db= new DB();
			$id_modele=$params->modele->id;
			$nom=$params->modele->nom;
			$modele=$params->modele->modele;
			$update = $db->database->prepare('UPDATE news_modeles SET nom=?, modele=?, modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array($nom, $modele, millisecondes(), $id, $id_modele));
			$tab[]='news/*';
			$tab[]="modele/$id_modele";
			CR::maj($tab);
			return $id_modele;
		}
		public static function mod_nom_cat_modele($params,$id) {
			$db= new DB();
			$nom_cat_new=$params->nom_cat_new;
			$nom_cat=$params->nom_cat;
			$update = $db->database->prepare('UPDATE news_modeles SET nom=REPLACE(nom,?,?)');
			$update->execute(array($nom_cat."::",$nom_cat_new."::"));
			$tab[]='news/*';
			$tab[]="modele/*";
			$tab[]="modeles";
			CR::maj($tab);
		}
		public static function touch_news($id_news,$id) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE news SET modificationdate=?, modifiedby=? WHERE id=?');
			$update->execute(array(millisecondes(), $id, $id_news));
			CR::maj(array("news/$id_news"));
			return 1;
		}
		public static function get_modele($id_modele) {
			$db= new DB();
			$query = "SELECT * FROM news_modeles WHERE id=$id_modele";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res=$row;
			}
			return $res;
		}
		public static function del_modele($params,$id) {
			$db= new DB();
			$id_modele=$params->news->id;
			$modele=Mailing::get_modele($id_modele);
			$insert = $db->database->prepare('INSERT INTO trash (id_item, type, json, date , by) VALUES (?,?,?,?,?) ');
			$insert->execute(array($id_modele,'modele',json_encode($modele),millisecondes(),$id));
		}
		public static function get_modeles() {
			$db= new DB();
			$query = "SELECT * FROM news_modeles ORDER BY id ASC";
			$modeles=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['verrou']=WS::get_verrou('modele/'.$row['id']);
				$modeles[$row['id']]=$row;
			}
			return $modeles;
		}	
		//envois
		public static function get_envois_casquette($id_cas,$id) {
			$db= new DB();
			$query = "SELECT t1.*, t2.sujet, t2.type, t2.id_type, t2.date FROM envoi_cas as t1 INNER JOIN envois as t2 on t1.id_envoi=t2.id WHERE t1.id_cas=$id_cas ORDER BY id ASC";
			$envois=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$row['emails']=json_decode($row['emails']);
				$envois[]=$row;
			}
			return $envois;
		}
		public static function get_envoi($id_envoi,$params='',$id) {
			$db= new DB();
			if ($params!='') {
				$envoi_page=$params->boite->page;
				$envoi_nb=$params->boite->nb;
				$envoi_first=($envoi_page-1)*$envoi_nb;
				$succes_page=$params->succes->page;
				$succes_nb=$params->succes->nb;
				$succes_first=($succes_page-1)*$succes_nb;
				$erreur_page=$params->erreur->page;
				$erreur_nb=$params->erreur->nb;
				$erreur_first=($erreur_page-1)*$erreur_nb;
				$impact_page=$params->impact->page;
				$impact_nb=$params->impact->nb;
				$impact_first=($impact_page-1)*$impact_nb;
			}   
			$query = "SELECT * FROM envois WHERE id=$id_envoi";
			$envoi=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$envoi=$row;
				$exp=json_decode($envoi['expediteur']);
				$envoi['expediteur']=$exp;
				$envoi['pjs']=json_decode($envoi['pjs']);
			}
			if ($params!='') {
				$query = "SELECT count(*) as total FROM boite_envoi WHERE id_envoi=$id_envoi";
				$envoi_total=0;
				foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
					$envoi_total=$row['total'];
				}
			}   
			$envoi['boite_envoi']=array();
			if ($params!='') {
				$envoi['boite_envoi']['page']=$envoi_page;
				$envoi['boite_envoi']['nb']=$envoi_nb;
				$envoi['boite_envoi']['total']=$envoi_total;
			}   
			$envoi['boite_envoi']['collection']=array();
			$cas=array();
			if ($params!='') {
				$query = "SELECT * FROM boite_envoi WHERE id_envoi=$id_envoi LIMIT $envoi_first,$envoi_nb";
			} else {
				$query = "SELECT * FROM boite_envoi WHERE id_envoi=$id_envoi";
			}   
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$envoi['boite_envoi']['collection'][]=$row;
				if ($params!='') $cas[]=$row['id_cas'];
			}
			if ($params!='') {
				//log d'envoi
				$envoi['succes_log']=array();
				$envoi['succes_log']['page']=$succes_page;
				$envoi['succes_log']['nb']=$succes_nb;
				$envoi['succes_log']['collection']=array();
				$succes_log="./data/files/envois/$id_envoi/succes.log";
				$i=0;
				if (file_exists($succes_log)) {
					$handle = fopen($succes_log, 'r');
					if ($handle)
					{
						while (!feof($handle))
						{
							$line=json_decode(fgets($handle));
							if (isset($line->i)){
								if ($i>=$succes_first && $i<$succes_first+$succes_nb) {
									$envoi['succes_log']['collection'][]=$line;
								}
								$i++;
							}
						}
						fclose($handle);
					}
				}
				$envoi['succes_log']['total']=$i;
				//log d'erreurs
				$envoi['erreur_log']=array();
				$envoi['erreur_log']['page']=$erreur_page;
				$envoi['erreur_log']['nb']=$erreur_nb;
				$envoi['erreur_log']['collection']=array();
				$erreur_log="./data/files/envois/$id_envoi/erreur.log";
				$i=0;
				if (file_exists($erreur_log)) {
					$handle = fopen($erreur_log, 'r');
					if ($handle)
					{
						while (!feof($handle))
						{
							$line=json_decode(fgets($handle));
							if (isset($line->i)){
								if ($i>=$erreur_first && $i<$erreur_first+$erreur_nb) {
									$envoi['erreur_log']['collection'][]=$line;
								}
								$i++;
							}
						}
						fclose($handle);
					}
				}
				$envoi['erreur_log']['total']=$i;
				//impact
				$envoi['impact']=array();
				$query = "SELECT count(*) as total FROM r WHERE id_envoi=$id_envoi ORDER BY DATE DESC;";
				foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
					$envoi['impact']['total']=$row['total'];
				}
				$envoi['impact']['page']=$erreur_page;
				$envoi['impact']['nb']=$erreur_nb;
				$envoi['impact']['collection']=array();
				$query = "SELECT * FROM r WHERE id_envoi=$id_envoi ORDER BY DATE DESC LIMIT $impact_first,$impact_nb;";
				foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
					$envoi['impact']['collection'][]=$row;
					$cas[]=$row['id_cas'];
				}
				$query = "SELECT t2.id as id, t2.emails as emails, t3.id as id_contact, t3.nom as nom, t3.prenom as prenom, t3.type as type FROM
					casquettes as t2
					inner join contacts as t3 ON t2.id_contact=t3.id
					WHERE t2.id in (".implode(',',$cas).")";
				$envoi['cas']=array();
				foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
					$row['emails']=array_unique(json_decode($row['emails']));
					$envoi['cas'][$row['id']]=$row;
				}
			}
			return $envoi;
		}
		public static function get_envois($id) {
			$db= new DB();
			$query = "SELECT by,date,id,sujet FROM envois ORDER BY date DESC";
			$res=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$res[]=$row;
			}
			return $res;
		}
		public static function envoyer($params,$id){
			$db= new DB();
			$table='';
			$selection=array();
			$expediteur=$params->res->expediteur;
			$query=$params->res->query;
			if ($params->type=='news') {
				$news=Mailing::get_news($params->e->id,$id);
				$sujet=$news['sujet'];
				$html='';
				foreach($news['blocs'] as $b){
					$html.=$b->html."\n";
				};
				$pjs=$news['pjs'];
			}
			if ($params->type=='mail') {
				$mail=Mailing::get_mail($params->e->id);
				$sujet=$mail['sujet'];
				$html=$mail['html'];
				$pjs=$mail['pjs'];
			}
			$casquettes=Contacts::get_casquettes(array('query'=>$query,'page'=>1,'nb'=>10,'all'=>1),0,$id);
			$selection=$casquettes['collection'];
			$nb=$casquettes['total'];

			$insert = $db->database->prepare('INSERT INTO envois (sujet, html, pjs, expediteur, type, id_type, nb, statut, date, by) VALUES (?,?,?,?,?,?,?,?,?,?)');
			$insert->execute(array($sujet,$html,json_encode($pjs),json_encode($expediteur),$params->type,$params->e->id,$nb,1,millisecondes(),$id));
			$id_envoi = $db->database->lastInsertId();
			//on met à jour les liens vers les fichiers
			$html=str_replace("./data/files/{$params->type}/{$params->e->id}","./data/files/envois/$id_envoi",$html);
			foreach($pjs as $k=>$pj){
				$pjs[$k]['path']=str_replace("./data/files/{$params->type}/{$params->e->id}","./data/files/envois/$id_envoi",$pj['path']);
			}
			$update = $db->database->prepare('UPDATE envois SET html=?, pjs=? WHERE id=?');
			$update->execute(array($html,json_encode($pjs),$id_envoi));
			smartCopy("./data/files/{$params->type}/{$params->e->id}","./data/files/envois/$id_envoi");
		
			$db->database->beginTransaction();
			$i=1;
			foreach($selection as $c){
				$insert = $db->database->prepare('INSERT INTO boite_envoi (id_cas, id_envoi, i, erreurs, date, by) VALUES (?,?,?,?,?,?)');
				$insert->execute(array($c['id'],$id_envoi,$i,'',millisecondes(),$id));
				$i++;
			}
			$db->database->commit();
			CR::maj(array("envoi/$id_envoi",'envois'));
			return $id_envoi;
		}
		public static function start_envoi($id_envoi){
			$command = "nohup /usr/bin/php exec.php envoi_mails ".$id_envoi." > /dev/null 2>&1 &";
			exec($command);
		}	
		public static function stop_envoi($id_envoi){
			Mailing::arret_envoi($id_envoi);
		}
		public static function arret_envoi($id_envoi) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE envois SET statut=2 WHERE id=?');
			$update->execute(array($id_envoi));
			CR::maj(array("envoi/$id_envoi"));
		}
		public static function pause_envoi($id_envoi) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE envois SET statut=1 WHERE id=?');
			$update->execute(array($id_envoi));
			CR::maj(array("envoi/$id_envoi"));
		}
		public static function play_envoi($id_envoi) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE envois SET statut=0 WHERE id=?');
			$update->execute(array($id_envoi));
			CR::maj(array("envoi/$id_envoi"));
		}
		public static function restart_envoi($id_envoi) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE boite_envoi SET erreurs=? WHERE id_envoi=?');
			$update->execute(array('',$id_envoi));
			Mailing::start_envoi($id_envoi);
		}
		public static function vide_envoi($id_envoi) {
			$db= new DB();
			$delete = $db->database->prepare('DELETE FROM boite_envoi WHERE id_envoi=?');
			$delete->execute(array($id_envoi));
			CR::maj(array("envoi/$id_envoi"));
		}
		public static function nb_messages_boite_envoi($id_envoi) {
			$db= new DB();
			$query = "SELECT count(*) FROM boite_envoi WHERE id_envoi=$id_envoi AND erreurs=''";
			$nb=0;
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$nb=$row['count(*)'];
			}
			return $nb;
		}
		public static function statut_envoi($id_envoi) {
			$db= new DB();
			$query = "SELECT statut FROM envois WHERE id=$id_envoi";
			$statut=0;
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$statut=$row['statut'];
			}
			return $statut;
		}
		public static function envoi_premier_message($id_envoi) {
			$db= new DB();
			$query = "SELECT * FROM boite_envoi WHERE id_envoi=$id_envoi AND erreurs='' LIMIT 0,1";
			$m=array();
			foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
				$m=$row;
			}
			return $m;
		}
		public static function message_erreur($id_message,$erreur) {
			$db= new DB();
			$update = $db->database->prepare('UPDATE boite_envoi SET erreurs=? WHERE id=?');
			$update->execute(array($erreur,$id_message));
		}
		public static function log_succes($id_envoi,$log) {
			$db= new DB();
			$log_path="./data/files/envois/$id_envoi";
			$log_file="$log_path/succes.log";
			if (!file_exists($log_path)) mkdir($log_path, 0777, true);
			error_log(json_encode($log)."\n",3,$log_file);
			$insert = $db->database->prepare('INSERT OR REPLACE INTO envoi_cas (id_envoi,id_cas,emails,date) VALUES (?,?,?,?)');
			$insert->execute(array($id_envoi,$log['cas']['id'],json_encode($log['cas']['emails']),$log['date']));
			CR::maj(array("contact/".$log['cas']['id_contact']));	
		}
		public static function log_erreur($id_envoi,$log) {
			$log_path="./data/files/envois/$id_envoi";
			$log_file="$log_path/erreur.log";
			if (!file_exists($log_path)) mkdir($log_path, 0777, true);
			error_log(json_encode($log)."\n",3,$log_file);
		}
		public static function sup_message($id_message) {
			$db= new DB();
			$delete = $db->database->prepare('DELETE FROM boite_envoi WHERE id=?');
			$delete->execute(array($id_message));
		}
	}
