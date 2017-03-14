<?php
$casquettes=Contacts::get_casquettes(array('query'=>'1','page'=>1,'nb'=>10,'all'=>true),0,1);
$db= new DB();
$db->database->beginTransaction();
$i=1;
foreach($casquettes['collection'] as $cas) {
	$donnees=$cas['donnees'];
	$update = $db->database->prepare('UPDATE casquettes SET nom=?, donnees=?, id_etab=?, emails=?, email_erreur=?, fonction=?, cp=? WHERE id=?');
	$update->execute(array($cas['nom_cas'],json_encode($donnees),$cas['id_etab'],emails($donnees),email_erreur($donnees),fonction($donnees),cp($donnees),$cas['id']));
	$update = $db->database->prepare('UPDATE casquettes_fts SET idx=? WHERE id=?');
	$update->execute(array(strtolower(normalizeChars($cas['nom']." ".$cas['prenom']))." ".idx($donnees),$cas['id']));
	$res=array('contact/'.$cas['id_contact']);
	echo "             \r".$i;	
	$i++;
}
$db->database->commit();
CR::maj(array('*'));
echo "\n";	

