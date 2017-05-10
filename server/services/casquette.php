<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */
$email=$params['email'];

$db=new DB();
$query="select id from casquettes where emails like '%$email%'";
$id=0;
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
	$id=$row['id'];
}
$casquette=array();
if ($id>0) {
	$casquette[]=Contacts::get_casquette($id,false,1);
}
echo json_encode($casquette);
?>
