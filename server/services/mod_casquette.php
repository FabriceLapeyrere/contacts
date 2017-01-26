<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */

$params=json_decode(json_encode($params));
$contacts= new Contacts();
$c=$contacts->get_casquette($params->id_base,true);

$p=(object) null;
$p->cas=$c;
$p->tag=(object) null;
	
foreach($params->categories as $id_tag){
	$p->tag->id=$id_tag;
	$contacts->add_cas_tag($p,$S['user']['id']);
}

