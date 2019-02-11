<?php
	if ($valeur=='') {
		$valeur=isset($tab[2]) ? $tab[2] : "#000000";
	}
	if ($index>=0) {
		$donnees[$index]->valeur=$valeur;
	}			
?>
