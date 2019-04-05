<?php
$tab=json_decode(base64_decode($argv[2]));
$ids_contacts=implode(',',$tab);
$db= new DB(true);
$query = "SELECT
	t1.id as id,
	t1.nom as nom,
	t1.prenom as prenom
	FROM contacts as t1 WHERE t1.id IN (
		SELECT id_contact from doublons_texte where id_doublon=(SELECT id_doublon FROM doublons_texte where id_contact IN ($ids_contacts))
	)";
$r0=array();
$ids=array();
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
	$r0[]=$row;
	$ids[]=$row['id'];
}
if (count($ids)>0) {
	$del=Contacts::do_del_doublons_texte($ids);
	WS_maj($del['maj']);
}

$query = "SELECT
	t1.id as id,
	t1.nom as nom,
	t1.prenom as prenom
	FROM contacts as t1
";
$res=array();
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
	$res[]=$row;
}
$doublons[]=array();
foreach($r0 as $r1) {
	if (!in_array($r1['id'],$doublons) && $r1['nom'].$r1['prenom']!='') {
		echo "                      \r".$r1['id'];
		$s1=str_replace('_',' ',filter2($r1['nom']." ".$r1['prenom']));
		$t1=explode(' ',$s1);
		$d1=array();
		foreach($res as $r2) {
			if ($r1['id']!=$r2['id'] && $r2['nom'].$r2['prenom']!='') {
				$test=0;
				$s2=str_replace('_',' ',filter2($r2['nom']." ".$r2['prenom']));
				$t2=explode(' ',$s2);
				$r=array();
				foreach($t1 as $m1){
					foreach($t2 as $m2){
						similar_text($m1,$m2,$percent);
						if ($percent>90) $r[]=$m1;
					}
				}
				if (count(array_unique($r))==count($t1) && (count($t1)==count($t2) || count($t1)>3)) {
					echo "\n=> ".$r2['id'].", ".$r1['nom']." ".$r1['prenom'].", ".$r2['nom']." ".$r2['prenom']."\n";
					$d1[]=$r1['id'];
					$d1[]=$r2['id'];
					$d1=array_unique($d1);
					$doublons[]=$r1['id'];
					$doublons[]=$r2['id'];
				}
			}
		}
		if (count($d1)>0) {
			$add=Contacts::do_add_doublons_texte($d1);
			WS_maj($add['maj']);
		}
	}
}
