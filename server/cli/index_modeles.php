<?php
$db= new DB();
$query = "SELECT * FROM news";
$news=array();
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
	$row['blocs']=json_decode($row['blocs']);
	$news[]=$row;
}
$db->database->beginTransaction();
foreach($news as $k=>$n){
	$delete = $db->database->prepare('DELETE FROM modeles_news WHERE id_news=?');
	$delete->execute(array($n['id']));
	$already=array();
	foreach($n['blocs'] as $b){
		if (!in_array($b->id_modele,$already)) {
			$insert = $db->database->prepare('INSERT INTO modeles_news (id_modele,id_news) VALUES (?,?)');
			$insert->execute(array($b->id_modele,$n['id']));
			$already[]=$b->id_modele;
		}
	}
}
$db->database->commit();
