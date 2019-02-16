<?php
	if ($valeur=='') {
		$valeur=isset($tab[2]) ? $tab[2] : "(Votre texte)";
	}
	if ($index>=0) {
		$donnees[$index]->valeur=$valeur;
	}
