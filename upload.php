<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
	include $filename;
}
include 'conf/main.php';
include 'conf/auth.php';

if ( !empty( $_FILES ) ) {

	$type = $_POST[ 'type' ];
	if ($type=='template') {
		$id = $_POST[ 'id' ];
		$tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
		$path_parts = pathinfo($_FILES[ 'file' ][ 'name' ]);

		$uploadDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $id;
		$uploadPath = $uploadDir . DIRECTORY_SEPARATOR . filter($path_parts['filename']).".".$path_parts['extension'];
		if (!file_exists($uploadDir)){
			mkdir($uploadDir, 0777, true);
		}
		$i=1;
		$path=$uploadPath;
		foreach (glob("$uploadDir/*") as $f)
		{
			unlink($f);
		}
		if (move_uploaded_file( $tempPath, $path )) {
			Publipostage::touch_template($id,$my_session->user->id);
			WS_maj(array("template/$id"));
			$answer = array(
				'answer' => 'File transfer completed'
			);
		} else {
			$answer = array(
				'answer' => 'Erreur...'
			);
		}
		$json = json_encode( $answer );

		echo $json;
	}
	if ($type=='news' || $type=='mail') {
		$id = $_POST[ 'id' ];
		$tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
		$path_parts = pathinfo($_FILES[ 'file' ][ 'name' ]);

		$uploadDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $id;
		$uploadPath = $uploadDir . DIRECTORY_SEPARATOR . filter($path_parts['filename']).".".$path_parts['extension'];
		if (!file_exists($uploadDir)){
			mkdir($uploadDir, 0777, true);
		}
		$i=1;
		$path=$uploadPath;
		while (file_exists($path)){
			$path=$uploadPath."_copie_$i";
			$i++;
		}
		if (move_uploaded_file( $tempPath, $path )) {
			if ($type=='news') {
				Mailing::touch_news($id,$my_session->user->id);
				WS_maj(array("news/$id"));
			}
			if ($type=='mail'){
				Mailing::touch_mail($id,$my_session->user->id);
				WS_maj(array("mail/$id"));
			}
			$answer = array(
				'answer' => 'File transfer completed'
			);
		} else {
			$answer = array(
				'answer' => 'Erreur...'
			);
		}
		$json = json_encode( $answer );

		echo $json;
	}
	if ($type=='nbcsv') {
		$tempPath = $_FILES[ 'file' ][ 'tmp_name' ];
		$path_parts = pathinfo($_FILES[ 'file' ][ 'name' ]);
		$hash=millisecondes();
		$uploadDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'tmp';
		$uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $hash;
		if (move_uploaded_file( $tempPath, $uploadPath )) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$row = 1;
			$header=array();
			$errorlines=array();
			$exemples=array();
			if (($handle = fopen($uploadPath, "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					if ($row>1) {
						$exemples[]=$data;
					}
					if ($row==1) {
						$num = count($data);
						$header=$data;
					} else {
						if (count($data)!=$num) $error_lines[]=$row;
					}
					$row++;
				}
				fclose($handle);
			}
			$types = array(
				'type',
				'nom',
				'prenom',
				'email',
				'tel',
				'fax',
				'note',
				'fonction',
				'adresse',
				'cp',
				'ville',
				'pays',
				'tag',
				'id',
				'idstr'
			);
			$labels=array(
				'Contact/Structure',
				'Nom',
				'Prénom',
				'E-mail',
				'Téléphone',
				'Fax',
				'Note',
				'Fonction',
				'Adresse',
				'Cp',
				'Ville',
				'Pays',
				'Catégories',
				'Identifiant',
				'Identifiant de la structure'
			);
			$map_labels=array();
			$map=array();
			foreach($header as $k=>$type_string){
				$type_tab=explode('|',$type_string);
				$type=$type_tab[0];
				$c=array_search($type, $types);
				if ($c!==false) {
					$label=$labels[$c];
					$i=1;
					$label_alt=$label;
					while(in_array($label_alt,$map_labels)) {
						$label_alt=$label." $i";
						$i++;
					}
					$map_labels[]=$label_alt;
					if(!isset($map[$type])) $map[$type]=array();
					$map[$type_string][$k]=$label_alt;
				}
			}
			$contacts=array();
			foreach($exemples as $exemple) {
				$note="";
				$contact=array();
				$contact['tags']=array();
				$donnees=array();
				$adresse=array();
				foreach($map as $type_string=>$keys) {
					$type_tab=explode('|',$type_string);
					$type=$type_tab[0];
					$p1='';
					if (count($type_tab)>1) $p1=$type_tab[1];
					$p2='';
					if (count($type_tab)>2) $p2=$type_tab[2];
					$i=0;
					foreach($keys as $k=>$v) {
						$label=$v;
						if ($exemple[$k]!='') {
							if ($i==0) {
								if ($type=='id') $contact['id']=$exemple[$k];
								if ($type=='idstr') $contact['idstr']=$exemple[$k];
								if ($type=='nom') $contact['nom']=$exemple[$k];
								if ($type=='prenom') $contact['prenom']=$exemple[$k];
								if ($type=='type') $contact['type']=$exemple[$k];
								if ($type=='fonction') {
									$contact['fonction']=$exemple[$k];
									$donnees[]=array('type'=>'fonction','label'=>$label,'value'=>$exemple[$k]);
								}
								if ($type=='adresse') $adresse['adresse']=trim($exemple[$k]);
								if ($type=='cp') $adresse['cp']=$exemple[$k];
								if ($type=='ville') $adresse['ville']=$exemple[$k];
								if ($type=='pays') $adresse['pays']=$exemple[$k];
							}
							if ($type=='note') {
								if (trim($exemple[$k])!='') $note.="\n".$exemple[$k];
							}
							if ($type=='tag') {
								$t=explode(',',$exemple[$k]);
								foreach ($t as $tv) {
									if (trim($tv)!="") {
										if ($p1!='') $tv=$p1.">".$tv;
										$contact['tags'][]=$tv;
										$contact['tags']=array_values(array_unique($contact['tags']));
									}
								}
							}
							if ($type=='email') {
								$ti=0;
								foreach(extractEmailsFromString($exemple[$k]) as $m) {
									if ($ti==0) $cl='';
									else $cl="/".$ti;
									$donnees[]=array('type'=>'email','k'=>$k,'suffixe'=>$cl,'value'=>$m);
									$ti++;
								}
							}
							if ($type=='tel') {
								$tel_tab=explode('/',$exemple[$k]);
								$ti=0;
								foreach($tel_tab as $t) {
									if ($ti==0) $cl='';
									else $cl="/".$ti;
									$pattern = "/([^\(\)]*)(?:\((.*)\)){0,1}/";
									preg_match_all($pattern, $t, $matches, PREG_OFFSET_CAPTURE);
									$tv=$matches[1][0][0];
									if (isset($matches[2][0][0])) $cl.=" ".$matches[2][0][0];
									$donnees[]=array('type'=>'tel','k'=>$k,'suffixe'=>$cl,'value'=>$tv);
									$ti++;
								}

							}
							if ($type=='fax') {
								$fax_tab=explode('/',$exemple[$k]);
								$ti=0;
								foreach($fax_tab as $t) {
									if ($ti==0) $cl='';
									else $cl="/".$ti;
									$pattern = "/([^\(\)]*)(?:\((.*)\)){0,1}/";
									preg_match_all($pattern, $t, $matches, PREG_OFFSET_CAPTURE);
									$tv=$matches[1][0][0];
									if (isset($matches[2][0][0])) $cl.=$matches[2][0][0];
									$donnees[]=array('type'=>'fax','k'=>$k,'suffixe'=>$cl,'value'=>$t);
									$ti++;
								}
							}
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
				if ($contact['type']==2) {
					$contact['cols']=array();
				}
				$contact['donnees']=$donnees;
				$contacts[]=$contact;
			}
			foreach($contacts as $indexc=>$c) {
				if (array_key_exists('idstr',$c)) {
					$idstr=$c['idstr'];
					foreach($contacts as $indexs=>$s) {
						if (array_key_exists('id',$s) && $s['id']==$idstr && $s['type']==2) {
							$contacts[$indexc]['str']=$s;
							$contacts[$indexs]['cols'][]=$c;
						}
					}
				}
			}
			$exemples_limited=array();
			$i=0;
			foreach($contacts as $indexc=>$c) {
				if ($i<100) $exemples_limited[$indexc]=$c;
				$i++;
			}
			$answer = array(
				'status'=> 'ok',
				'hash'=> $hash,
				'header'=> $header,
				'map'=> $map,
				'exemples'=> $exemples_limited,
				'rows'=> $row-2,
				'errorlines'=> $errorlines,
				'filename'=> $_FILES[ 'file' ][ 'name' ]
			);
		} else {
			$answer = array(
				'status'=> 'ko',
			);
		}
		$json = json_encode( $answer );

		echo $json;

	}

} else {

	echo 'No files';

}

?>
