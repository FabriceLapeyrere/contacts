<?php
$contacts= new Contacts();
$query = "SELECT id FROM casquettes";
$res=array();
foreach($contacts->database->query($query, PDO::FETCH_ASSOC) as $row){
	ldap_update($row['id']);
	echo "             \r".$row['id'];
}
