<?php
$db= new DB();
$query = "SELECT * FROM suivis where id_thread IS NULL ORDER BY id asc";
$cas=array();
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
	if (!array_key_exists($row['id_casquette'],$cas)) $cas[$row['id_casquette']]=array();
	$cas[$row['id_casquette']][]=$row;
}
$db->database->beginTransaction();
$i=1;
foreach($cas as $k=>$thread){
	echo "                               \r$i / ".count($cas);
	$modificationdate=0;
	$modifiedby=0;
	foreach($thread as $suivi){
		$modificationdate=max($suivi['modificationdate'],$modificationdate);
		if ($suivi['modificationdate']==$modificationdate) $modifiedby=$suivi['modifiedby'];
	}
	$s=$thread[0];
	$sL=$thread[count($thread)-1];
	$insert = $db->database->prepare('INSERT INTO suivis_threads (id_casquette, nom, desc, creationdate, createdby, modificationdate, modifiedby) VALUES (?,?,?,?,?,?,?)');
	$insert->execute(array($s['id_casquette'], "Suivi", "", $s['creationdate'], $s['createdby'], $modificationdate, $modifiedby));
	$id_thread = $db->database->lastInsertId();
	$update = $db->database->prepare('UPDATE suivis set id_thread=? where id_casquette=?');
	$update->execute(array($id_thread,$k));
	$update = $db->database->prepare("UPDATE acl set id_ressource=?, type_ressource='suivis_threads' where id_ressource=? and type_ressource='suivis'");
	$update->execute(array($id_thread,$sL['id']));
	$i++;
}
$delete = $db->database->prepare("DELETE from acl where type_ressource='suivis'");
$delete->execute();
$db->database->commit();
echo "\n";
WS_maj(array("*"));

