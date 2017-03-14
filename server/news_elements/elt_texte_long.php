<?php
	if ($valeur=='') {
		$valeur=isset($tab[3]) ? $tab[3] : "(Votre texte<br />sur plusieurs lignes)";
	}
	else {
		$color='';
		if (isset($tab[2])) {
			$color=$tab[2];
			$doc = new DOMDocument();
			$cv = mb_convert_encoding($valeur, 'HTML-ENTITIES', "UTF-8");
			$doc->loadHTML($cv);
			$xpath = new DOMXpath($doc);
			$nodes = $xpath->query('//a');
			foreach($nodes as $node) {
				$href=$node->getAttribute('href');
				$node->setAttribute('style',"text-decoration:none;color:$color;");
			}
			$innerHTML="";
			foreach ($doc->getElementsByTagName('body')->item(0)->childNodes as $child) {
				$innerHTML.= $doc->saveXML($child);
			}
			$valeur=$innerHTML;
		}
	}
?>
