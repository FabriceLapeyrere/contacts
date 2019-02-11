<?php
$emails=json_decode(base64_decode($argv[2]));
$db= new DB(true);
foreach($emails as $email) {
	$query = "SELECT
		t1.id as id
		FROM casquettes as t1 WHERE emails LIKE '%$email%'
	";
	$res=array();
	foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
		$res[]=$row['id'];
	}
	if (count($res)>1) {
		$add=Contacts::do_add_doublons_email($email,$res);
		WS_maj($add['maj']);
	} else {
		$add=Contacts::do_del_doublons_email($email);
		WS_maj($add['maj']);
	}
}
