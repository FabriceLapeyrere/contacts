<?php
$casquettes=Contacts::get_casquettes(array('query'=>'1','page'=>1,'nb'=>10,'all'=>true),0,1);
$db= new DB();
$db->database->beginTransaction();
$i=1;
foreach($casquettes['collection'] as $cas) {
	echo "                      \r".$i." -> ".$cas['id'];	
	$donnees=$cas['donnees'];
	foreach($donnees as $k=>$j){
		if ($j->value==NULL) {
			unset($donnees[$k]);
		}
	}
	$donnees = array_values($donnees);
	$update = $db->database->prepare('UPDATE casquettes SET nom=?, donnees=?, id_etab=?, emails=?, email_erreur=?, fonction=?, cp=? WHERE id=?');
	$update->execute(array($cas['nom_cas'],json_encode($donnees),$cas['id_etab'],emails($donnees),email_erreur($donnees),fonction($donnees),cp($donnees),$cas['id']));
	$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
	$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']." ".$cas['nom_cas']))." ".idx($donnees),$cas['id']));
	$i++;
}
$db->database->commit();
WS_maj(array('*'));
echo "\n";	

