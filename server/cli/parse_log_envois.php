<?php
$db= new DB();
$envois=Mailing::get_envois(1);
foreach($envois as $e) {
	$id_envoi=$e['id'];
	echo $e['sujet']."\n";
	if (file_exists("data/files/envois/$id_envoi/succes.log")){
		$query = "SELECT count(*) as nb FROM envoi_cas WHERE id_envoi=$id_envoi";
		$nb=0;
		foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
			$nb=$row['nb'];
		}
		if ($nb==0) {
			$log=file("data/files/envois/$id_envoi/succes.log");
			$db->database->beginTransaction();		
			foreach($log as $l) {
				$o=json_decode($l);
				echo $o->date." - ".$o->cas->prenom." ".$o->cas->nom." - ".$o->cas->id."\n";
				$insert = $db->database->prepare('INSERT OR REPLACE INTO envoi_cas (id_envoi,id_cas,emails,date) VALUES (?,?,?,?)');
				$insert->execute(array($id_envoi,$o->cas->id,json_encode($o->cas->emails),$o->date));
			}
			$db->database->commit();
		}
	}
	WS_maj(array("*"));		
}
echo "\n";

