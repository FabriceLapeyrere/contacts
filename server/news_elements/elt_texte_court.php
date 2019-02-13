<?php
	if ($valeur=='') {
		$valeur=isset($tab[2]) ? $tab[2] : "(Votre texte)";
	}
	if ($index>=0) {
		error_log("texte_court\n-------------\n".var_export(array($donnees,$index),true)."\n-------------\n",3,"/tmp/fab.log");

		$donnees[$index]->valeur=$valeur;
	}
