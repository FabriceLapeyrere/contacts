<?php
$db= new DB(true);
$query = "SELECT id, emails FROM casquettes";
$res=array();
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
	$res[]=$row;
}
$i=1;
foreach($res as $r) {
	echo "                      \r$i/".count($res);	
	$emails=json_decode($r['emails']);
	foreach($emails as $email) {
		$doublons=array();
		foreach($res as $r1){
			$emails1=json_decode($r1['emails']);
			if (in_array($email,$emails1)) $doublons[]=$r1['id'];
		}
		if (count($doublons)>1) {
			$add=Contacts::do_add_doublons_email($email,$doublons);
			WS_maj($add['maj']);
		}
	}
	$i++;
}
