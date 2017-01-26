<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */
$id=$params['id'];
$retour='';
if (Casquettes::existe($id)) {
	$c=new Casquette($id);
	$tab=$c->tout();
	$tab['id']=$id;
	$retour=json_encode($tab);
}
echo $retour;
?>
