<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */
$cass=array_slice($argv, 2);
foreach($cass as $id_cas) {
	ldap_update($id_cas);
}
