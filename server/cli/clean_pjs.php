<?php
$db= new DB();
$query = "SELECT id,blocs FROM news";
$res=array();
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
	$res[]=$row;
}
foreach($res as $news){
	$news['blocs']=json_decode($news['blocs']);
	if (is_string($news['blocs'])) $news['blocs']=json_decode($news['blocs']);
	foreach($news['blocs'] as $index=>$b) {
		$news['blocs'][$index]=clean_pjs_bloc($news['id'],$b);
	}
	$update = $db->database->prepare('UPDATE news SET blocs=? WHERE id=?');
	$update->execute(array(json_encode($news['blocs']), $news['id']));
	CR::maj(array("newss","news/".$news['id']));		
}
