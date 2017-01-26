<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */
$p=(object) null;
$p->cas=(object) null;
$p->cas->id=$params['id_casquette'];
$p->tag=(object) null;
$p->tag->id=$params['id_categorie'];

Contacts::add_cas_tag($p);
echo json_encode(array('ajout'=>'ok'));
?>
