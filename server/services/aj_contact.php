<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */

$params=json_decode(json_encode($params));
$c=(object) null;
$c->contact=(object) null;
$c->contact->nom=$params->nom;
$c->contact->prenom=$params->prenom;
$c->contact->type=1;
$t0=Contacts::do_add_contact($c,$S['user']['id']);
$id=$t0['res'];
$contact=Contacts::get_contact($id,true,1);
$p=(object) null;
foreach($contact['casquettes'] as $cas){
	$p->cas= (object) $cas;
	$p->cas->donnees=$params->donnees;
}
$t1=Contacts::do_mod_casquette($p,$S['user']['id']);
$p->tag=(object) null;	
foreach($params->categories as $id_tag){
	$p->tag->id=$id_tag;
	$t2=Contacts::do_add_cas_tag($p,$S['user']['id']);
}
WS_maj(array_merge($t0,$t1,$t2));
