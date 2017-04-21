<?php
$db= new DB();
$query = "SELECT id FROM casquettes";
$res=array();
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
	$res[]=$row['id'];
}
foreach($res as $id){
	ldap_update($id);
	echo "             \r".$id;
}
