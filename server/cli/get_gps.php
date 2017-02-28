<?php
$casquettes=Contacts::get_casquettes(array('query'=>'::adresse::','page'=>1,'nb'=>10,'all'=>true),0,1);
$db= new DB();
$i=1;
$total=count($casquettes['collection']);
foreach($casquettes['collection'] as $cas) {
	$donnees=$cas['donnees'];
	$gps=array('x'=>1000,'y'=>1000);
	if ($cas['gps_x']==1000) {
		foreach($donnees as $d) {
			if ($d->type=='adresse') {
				$gps=get_gps($d->value);
				sleep(2);
			}
		}
		if ($gps['x']!=1000) {		
			$update = $db->database->prepare('UPDATE casquettes SET gps_x=?, gps_y=? WHERE id=?');
			$update->execute(array($gps['x'],$gps['y'],$cas['id']));
			CR::maj(array('contact/'.$cas['id_contact']));
		}
	}
	echo "                                             \r$i/$total, ".$gps['x'].", ".$gps['y']."                              ";
	$i++;
}
echo "\n";	

