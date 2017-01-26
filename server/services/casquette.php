<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */
$email=$params['email'];

$contacts=new Contacts();
$query="select id from casquettes where emails like '%$email%'";
$id=0;
foreach($contacts->database->query($query, PDO::FETCH_ASSOC) as $row){
	$id=$row['id'];
}
error_log($id." ".$email."\n",3,'/tmp/fab.log');
$casquette=array();
if ($id>0) {
	$casquette[]=$contacts->get_casquette($id);
}
echo json_encode($casquette);
?>
