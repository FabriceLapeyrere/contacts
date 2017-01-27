<?php
	if ($valeur=='') {
		$valeur=isset($tab[3]) ? $tab[3] : "(Votre texte<br />sur plusieurs lignes)";
	}
	else {
		$color='';
		if (isset($tab[2])) {
			$color=$tab[2];
			$doc = new DOMDocument();
			$doc->loadHTML($valeur);
			$xpath = new DOMXpath($doc);
			$nodes = $xpath->query('//a');
			foreach($nodes as $node) {
				$href=$node->getAttribute('href');
				$node->setAttribute('style',"text-decoration:none;color:$color;");
			}
			$valeur=$doc->saveHTML($doc->getElementsByTagName('body')->item(0));
		}
	}
?>
