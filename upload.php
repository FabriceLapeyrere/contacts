<?php
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'fake_ws/conf.php';
include 'conf/main.php';
include 'conf/auth.php';

if ( !empty( $_FILES ) ) {

	$type = $_POST[ 'type' ];
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
		    if ($type=='news') Mailing::touch_news($id,$S['user']['id']);
		    if ($type=='mail') Mailing::touch_mail($id,$S['user']['id']);
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
	} elseif ($type=='nbcsv') {
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
					if ($row>1 && $row<=101) {
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
				'note',
				'fonction',
				'adresse',
				'cp',
				'ville',
				'pays',
				'id',
				'idstr'
			);
			$labels=array(
				'Contact/Structure',
				'Nom',
				'Prénom',
				'E-mail',
				'Téléphone',
				'Note',
				'Fonction',
				'Adresse',
				'Cp',
				'Ville',
				'Pays',
				'Identifiant',
				'Identifiant de la structure'
			);
			$map_labels=array();
			$map=array();
			foreach($header as $k=>$type){
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
					$map[$type][$k]=$label_alt;
				}
			}
			$contacts=array();
			foreach($exemples as $exemple) {
				$note="";
				$contact=array();
				$donnees=array();
				$adresse=array();
				foreach($map as $type=>$keys) {
					$i=0;
					foreach($keys as $k=>$v) {
						if ($exemple[$k]!='') {
							if ($i==0) {
								if ($type=='id') $contact['id']=$exemple[$k]; 
								if ($type=='idstr') $contact['idstr']=$exemple[$k]; 
								if ($type=='nom') $contact['nom']=$exemple[$k]; 
								if ($type=='prenom') $contact['prenom']=$exemple[$k]; 
								if ($type=='type') $contact['type']=$exemple[$k]; 
								if ($type=='note') $note.="\n".$exemple[$k];
								if ($type=='fonction') $donnees[]=array('type'=>'fonction','k'=>$k,'value'=>$exemple[$k]);
								if ($type=='adresse') $adresse['adresse']=trim($exemple[$k]); 
								if ($type=='cp') $adresse['cp']=$exemple[$k]; 
								if ($type=='ville') $adresse['ville']=$exemple[$k]; 
								if ($type=='pays') $adresse['pays']=$exemple[$k];
							}
							if ($type=='email') {
								foreach(extractEmailsFromString($exemple[$k]) as $m) {
									$donnees[]=array('type'=>'email','k'=>$k,'value'=>$m);
								}
							}
							if ($type=='tel') $donnees[]=array('type'=>'tel','k'=>$k,'value'=>$exemple[$k]);
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
			$answer = array(
				'status'=> 'ok',
				'hash'=> $hash,
				'header'=> $header,
				'map'=> $map,
				'exemples'=> $contacts,
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
