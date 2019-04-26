<?php
	if ($valeur=='') {
		$valeur=isset($tab[2]) ? $tab[2] : "(Votre texte)";
	}
	$valeur=preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $valeur);
	$valeur=preg_replace('/\*([^*]+)\*/', '<i>$1</i>', $valeur);
