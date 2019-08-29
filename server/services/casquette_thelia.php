<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */
$params=json_decode(json_encode($params));
$id=$params->id_thelia;
$contacts= new Contacts();
function id_thelia($o)
{
	$d=json_decode($o);
	if(is_array($d)) {
		foreach($d as $donnee){
			if ($donnee->label=='Id Thelia') return $donnee->value*1;
		}
	}
}

$contacts->database->sqliteCreateFunction('id_thelia', 'id_thelia', 1);
$query = "SELECT id FROM casquettes WHERE id_thelia(donnees)=$id";
error_log("iumm2 : \n".$query."\n",3,'../data/tmp/debug.log');
$res=array();
$id_cas=0;
foreach($contacts->database->query($query, PDO::FETCH_ASSOC) as $row){
	$id_cas=$row['id'];
	error_log("iumm2 : \n".$id_cas."\n",3,'../data/tmp/debug.log');
}
echo $id_cas;
?>
