<?php
$racine="/";
if (PHP_SAPI !== 'cli')
{
	$racine=str_replace('//','/',dirname(substr($_SERVER['PHP_SELF'], 0, -1))."/");
}
define("RACINE",$racine);
$config=new Config();
$C=$config->get();
