<?php
 /**
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice.lapeyrere@surlefil.org>
 */
$id=$argv[2];
$S=array('user'=>User::get_user($id));
IMAP::check_imap($id);
