<?php
$id_contact=$argv[2];
$db= new DB(true);
$query = "SELECT
	t1.id as id,
	t1.nom as nom,
	t1.prenom as prenom
	FROM contacts as t1
";
$res=array();
$r1=array();
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
	$res[]=$row;
	if ($row['id']==$id_contact) $r1=$row;
}
$doublons[]=array();
if ($r1['nom'].$r1['prenom']!='') {
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
	} else {
		$add=Contacts::do_del_doublon_texte($id_contact);
		WS_maj($add['maj']);
	}
}
