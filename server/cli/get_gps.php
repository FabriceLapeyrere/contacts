<?php
$casquettes=Contacts::get_casquettes(array('query'=>'::adresse::','page'=>1,'nb'=>10,'all'=>true),0,1);
$db= new DB();
$i=1;
$total=count($casquettes['collection']);
function cmpc($a, $b) {
    if ($a['type'] == $b['type']) {
        return 0;
    }
    return ($a['type'] < $b['type']) ? 1 : -1;
}
uasort($casquettes['collection'], 'cmpc');
$s=0;
foreach($casquettes['collection'] as $cas) {
	$donnees=$cas['donnees'];
	$gps=array('x'=>1000,'y'=>1000);
	if ($cas['gps_x']>=1000 || $cas['gps_x']==$cas['gps_y']) {
		foreach($donnees as $d) {
			if ($d->type=='adresse') {
				$gps=get_gps($d->value);
				sleep(0.1);
			}
		}
		if ($gps['x']!=1000) {		
			$update = $db->database->prepare('UPDATE casquettes SET gps_x=?, gps_y=? WHERE id=?');
			$update->execute(array($gps['x'],$gps['y'],$cas['id']));
			
		}
		echo "                                 \r$i/$total, ".$cas['id']." -> ".$gps['x'].", ".$gps['y']."                              ";
	}
	$i++;
	if (floor(10*$i/$total) > $s) {
		$s++;
		WS_maj(array('*'));
	}
}
WS_maj(array('*'));

echo "\n";	

