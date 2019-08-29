<?php
function extractEmailsFromString($sChaine) {
	if(false !== preg_match_all('`\w(?:[-_.]?\w)*@\w(?:[-_.]?\w)*\.(?:[a-z]{2,4})`', $sChaine, $aEmails)) {
		if(is_array($aEmails[0]) && sizeof($aEmails[0])>0) {
			return array_unique($aEmails[0]);
		}
	}
	return array();
}
function Hex2RGB($color){
    $color = str_replace('#', '', $color);
    if (strlen($color) != 6){ return array(0,0,0); }
    $rgb = array();
    for ($x=0;$x<3;$x++){
        $rgb[$x] = hexdec(substr($color,(2*$x),2));
    }
    return $rgb;
}
function get_gps($adresse){
	$pays=isset($adresse->pays) ? $adresse->pays : "France";
	$cp=10*(floor($adresse->cp/10));
	//echo "\n".$adresse->cp." // ".$cp."\n";
	$string = $adresse->ville;
	$pattern = '/\bcedex.*/i';
	$replacement = '';
	$ville=preg_replace($pattern, $replacement, $string);
	//echo $adresse->ville." // ".$ville."\n";
	$adresse=str_replace(',','',str_replace("\n",' ',$adresse->adresse));
	//echo "$pays, $cp, $ville, $adresse\n";
	$query1 = http_build_query(array(
	 'q' => "$pays, $cp, $ville, $adresse",
	 'format' => 'json'
	));
	$query2 = http_build_query(array(
	 'q' => "$pays, $cp, $ville",
	 'format' => 'json'
	));
	$url="http://nominatim.openstreetmap.org/search.php?".$query1;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt ($ch, CURLOPT_HEADER, 0);
	$tab=json_decode(curl_exec ($ch));
	if (count($tab)==0) {
		$url="http://nominatim.openstreetmap.org/search.php?".$query2;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:32.0) Gecko/20100101 Firefox/32.0");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt ($ch, CURLOPT_HEADER, 0);
		$tab=json_decode(curl_exec ($ch));
	}
	//error_log(var_export($tab,true)."\n".$url."\n",3,"/tmp/fab.log");
	if (count($tab)>0) return array('x'=>$tab[0]->lon,'y'=>$tab[0]->lat);
	else return array('x'=>'1001','y'=>'1001');
}
function filter($txt) {
	$search = array ('@[\\- ]@i','@[^a-zA-Z0-9_]@');
	$replace = array ('_','_');
	return preg_replace($search, $replace, normalizeChars($txt));
}
function filter2($txt) {
	$search = array ('@[\\- ]@i','@[^a-zA-Z0-9_]@');
	$replace = array ('_','_');
	return strtolower(preg_replace($search, $replace, normalizeChars(trim($txt))));
}
function cmp($a, $b)
{
    if ($a['hash'] == $b['hash']) {
	return 0;
    }
    return ($a['hash'] < $b['hash']) ? -1 : 1;
}
function idx($a){
	$idx="";
	foreach($a as $i){
		if (isset($i->value) && $i->type!='adresse') {
			$idx.=" ".$i->value;
		} else {
			if (isset($i->value->adresse)) $idx.=" ".$i->value->adresse;
			if (isset($i->value->cp)) $idx.=" ".$i->value->cp;
			if (isset($i->value->ville)) $idx.=" ".$i->value->ville;
			if (isset($i->value->pays)) $idx.=" ".$i->value->pays;
		}
	}
	return strtolower(normalizeChars($idx));
}
function emails($a){
	$emails=array();
	foreach($a as $i){
		if (isset($i->value) && $i->type=='email') {
			$emails[]=$i->value;
		}
	}
	return json_encode($emails);
}
function email_erreur($a){
	foreach($a as $i){
		if (isset($i->value) && $i->type=='email_erreur') {
			return 1;
		}
	}
	return 0;
}
function cp($a){
	$cp="";
	foreach($a as $i){
		if (isset($i->value) && $i->type=='adresse'){
			if (!isset($i->value->pays) || strtolower($i->value->pays)=="france" || $i->value->pays=="") {
				if (isset($i->value->cp)) $cp=$i->value->cp;
			}
			if (isset($i->value->pays) && $i->value->pays!="" && strtolower($i->value->pays)!="france") {
				$cp="E";
			}
		}
	}
	return cp2dept($cp);
}
function fonction($a){
	$fonction="";
	foreach($a as $i){
		if (isset($i->value) && $i->type=='fonction') {
			$fonction=$i->value;
		}
	}
	return $fonction;
}
function civilite($a){
	$civilite="";
	foreach($a as $i){
		if (isset($i->value) && $i->type=='civilite') {
			$civilite=$i->value;
		}
	}
	return $civilite;
}
function adresse($a){
	$adresse="";
	foreach($a as $i){
		if (isset($i->value) && $i->type=='adresse') {
            $adresse='';
			if (isset($i->value->adresse)) $adresse.=$i->value->adresse."\n";
			$adresse.=$i->value->cp." ".$i->value->ville;
			if ($i->value->pays!="France") $adresse.="\n".$i->value->pays;
		}
	}
	return $adresse;
}
function normalizeChars($s) {
    $replace = array(
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'Ae', 'Å'=>'A', 'Æ'=>'A', 'Ă'=>'A', 'Ą' => 'A', 'ą' => 'a',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'ae', 'å'=>'a', 'ă'=>'a', 'æ'=>'ae',
        'þ'=>'b', 'Þ'=>'B',
        'Ç'=>'C', 'ç'=>'c', 'Ć' => 'C', 'ć' => 'c',
        'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ę' => 'E', 'ę' => 'e',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e',
        'Ğ'=>'G', 'ğ'=>'g',
        'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'İ'=>'I', 'ı'=>'i', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
        'Ł' => 'L', 'ł' => 'l',
        'Ñ'=>'N', 'Ń' => 'N', 'ń' => 'n',
        'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'Oe', 'Ø'=>'O', 'ö'=>'oe', 'ø'=>'o',
        'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'Š'=>'S', 'š'=>'s', 'Ş'=>'S', 'ș'=>'s', 'Ș'=>'S', 'ş'=>'s', 'ß'=>'ss', 'Ś' => 'S', 'ś' => 's',
        'ț'=>'t', 'Ț'=>'T',
        'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'Ue',
        'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'ue',
        'Ý'=>'Y',
        'ý'=>'y', 'ý'=>'y', 'ÿ'=>'y',
        'Ž'=>'Z', 'ž'=>'z', 'Ż' => 'Z', 'ż' => 'z', 'Ź' => 'Z', 'ź' => 'z'
    );
    return strtr($s, $replace);
}
function millisecondes(){
	return floor(microtime(true)*1000);
}
function replaceHref($html, $redirect, $params)
{
	$dom = new DOMDocument();
	$dom->loadHTML($html);

	//Evaluate Anchor tag in HTML
	$xpath = new DOMXPath($dom);
	$sets=array();

	$sets[] = $xpath->evaluate("/html/body//a");
	$sets[] = $xpath->evaluate("/html/body//area");
	foreach ($sets as $hrefs) {
		for ($i = 0; $i < $hrefs->length; $i++) {
			$href = $hrefs->item($i);
			$url = $href->getAttribute('href');
			if ($url!="##UNSUBSCRIBEURL##" && strpos($url,"mailto:")===false) {
				$p=$params;
				$p['url']=$url;
				$hash=json_encode($p);
				$hash=base64_encode($hash);
				//remove and set target attribute
				$newURL=$redirect."?h=".$hash;

				//remove and set href attribute
				$href->removeAttribute('href');
				$href->setAttribute("href", $newURL);
			}
		}
	}
	// save html
	$html=$dom->saveHTML();
	return $html;
}
function replaceImgs($html, $base, $params, $use_redirect, $redirect)
{
	$dom = new DOMDocument();
	$dom->loadHTML($html);

	//Evaluate Anchor tag in HTML
	$xpath = new DOMXPath($dom);
	$imgs = $xpath->evaluate("/html/body//img");
	for ($i = 0; $i < $imgs->length; $i++) {
		$img = $imgs->item($i);
		$src = $img->getAttribute('src');
		if (strpos($src, '://') === FALSE && strpos($src, 'data:image') === FALSE) {
			$url="$base/$src";
			if ($use_redirect) {
				$p=$params;
				$p['url']=$src;
				$p['isImg']=1;
				$hash=json_encode($p);
				$hash=base64_encode($hash);
				$url=$redirect."?h=".$hash;
			}
			//remove and set src attribute
			$img->removeAttribute('src');
			$img->setAttribute("src", $url);
		}
	}
	// save html
	$html=$dom->saveHTML();
	return $html;
}
/**
     * Copy file or folder from source to destination, it can do
     * recursive copy as well and is very smart
     * It recursively creates the dest file or directory path if there weren't exists
     * Situtaions :
     * - Src:/home/test/file.txt ,Dst:/home/test/b ,Result:/home/test/b -> If source was file copy file.txt name with b as name to destination
     * - Src:/home/test/file.txt ,Dst:/home/test/b/ ,Result:/home/test/b/file.txt -> If source was file Creates b directory if does not exsits and copy file.txt into it
     * - Src:/home/test ,Dst:/home/ ,Result:/home/test/** -> If source was directory copy test directory and all of its content into dest
     * - Src:/home/test/ ,Dst:/home/ ,Result:/home/**-> if source was direcotry copy its content to dest
     * - Src:/home/test ,Dst:/home/test2 ,Result:/home/test2/** -> if source was directoy copy it and its content to dest with test2 as name
     * - Src:/home/test/ ,Dst:/home/test2 ,Result:->/home/test2/** if source was directoy copy it and its content to dest with test2 as name
     * @todo
     *     - Should have rollback technique so it can undo the copy when it wasn't successful
     *  - Auto destination technique should be possible to turn off
     *  - Supporting callback function
     *  - May prevent some issues on shared enviroments : http://us3.php.net/umask
     * @param $source //file or folder
     * @param $dest ///file or folder
     * @param $options //folderPermission,filePermission
     * @return boolean
     */
    function smartCopy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755))
    {
        $result=false;

        if (is_file($source)) {
            if ($dest[strlen($dest)-1]=='/') {
                if (!file_exists($dest)) {
                    cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
                }
                $__dest=$dest."/".basename($source);
            } else {
                $__dest=$dest;
            }
            $result=copy($source, $__dest);
            chmod($__dest,$options['filePermission']);

        } elseif(is_dir($source)) {
            if ($dest[strlen($dest)-1]=='/') {
                if ($source[strlen($source)-1]=='/') {
                    //Copy only contents
                } else {
                    //Change parent itself and its contents
                    $dest=$dest.basename($source);
                    @mkdir($dest);
                    chmod($dest,$options['filePermission']);
                }
            } else {
                if ($source[strlen($source)-1]=='/') {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest,$options['folderPermission']);
                    chmod($dest,$options['filePermission']);
                } else {
                    //Copy parent directory with new name and all its content
                    @mkdir($dest,$options['folderPermission']);
                    chmod($dest,$options['filePermission']);
                }
            }

            $dirHandle=opendir($source);
            while($file=readdir($dirHandle))
            {
                if($file!="." && $file!="..")
                {
                     if(!is_dir($source."/".$file)) {
                        $__dest=$dest."/".$file;
                    } else {
                        $__dest=$dest."/".$file;
                    }
                    //echo "$source/$file ||| $__dest<br />";
                    $result=smartCopy($source."/".$file, $__dest, $options);
                }
            }
            closedir($dirHandle);

        } else {
            $result=false;
        }
        return $result;
    }
	/**
	* Escapes an LDAP AttributeValue
	*/
	if (!function_exists('ldap_escape')) {
		function ldap_escape($string)
		{
		    return stripslashes($string);
		}
	}
	function ldap_update_array($cass) {
		$command = "nohup /usr/bin/php exec.php ldap_update ".implode(' ',$cass)." > /dev/null 2>&1 &";
		exec($command);
	}
	function ldap_update($id) {
        	$C=Config::get();
		// connect to ldap server
		error_log("connection ldap ...\n",3,"../data/log/debug.log");
		if ($C->ldap->active->value==1){
			error_log("reussie\n",3,"../data/log/debug.log");
			$ldapconn = ldap_connect($C->ldap->srv->value)
				or die("Could not connect to LDAP server.");
			ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

			if ($ldapconn) {

				// binding to ldap server
				$ldapbind = ldap_bind($ldapconn, $C->ldap->rdn->value, $C->ldap->pwd->value);
				# on ecrit :
				$c=Contacts::get_casquette($id,false,1);
				$entry=Array();
				$entry['uid']=$id;
				$entry['cn']="";
				$entry['cn']=trim($c['prenom']." ".$c['nom']);
				if ($entry['cn']=="") $entry['cn']=ldap_escape("(sans nom)");
				if (trim($c['nom'])!="") $entry['sn']=ldap_escape($c['nom']);
				else $entry['sn']=ldap_escape("(sans nom)");
				$entry['gn']=ldap_escape($c['prenom']);
				if (isset($c['etab']['nom'])) $entry['o']=ldap_escape($c['etab']['nom']);
				$entry['mail']=isset($c['emails'][0]) ? ldap_escape($c['emails'][0]) : "";
				$tels=array();
				$notes=array();
				$adresse=array();
				foreach($c["donnees"] as $d){
					if ($d->type=='tel') {
						$tels[]=$d->value;
					}
					if ($d->type=='note') {
						$notes[]=$d->value;
					}
					if ($d->type=='adresse') {
						$adresse=$d->value;
					}
				}
				if (isset($c['etab']['nom']))
				foreach($c['etab']['donnees'] as $d){
					if ($d->type=='adresse') {
						$adresse=$d->value;
					}
				}

				$tags=$c['tags'];
				$categories="";
				foreach ($tags as $id_tag) {
					$tag=Contacts::get_tag($id_tag);
					if ($categories=="") $categories.=ldap_escape($tag['nom']);
					else $categories.=", ".ldap_escape($tag['nom']);
				}
				$entry['description']=$categories!="" ? $categories."\n" :"";

				$entry['telephoneNumber']=isset($tels[0]) ? $tels[0] : '';
				$entry['description'].=isset($notes[0]) ? ldap_escape($notes[0])."\n" : "";
				$entry['street']=isset($adresse->adresse) ? ldap_escape($adresse->adresse) : '';
				$entry['postalCode']=isset($adresse->cp) ? ldap_escape($adresse->cp) : '';
				$entry['l']=isset($adresse->ville) ? ldap_escape($adresse->ville) : '';
				$entry["objectclass"][0]="top";
				$entry["objectclass"][1]="inetOrgPerson";
				$entry["objectclass"][2]="person";
				$entry["objectclass"][3]="organizationalPerson";

				#on supprime les champs vide, sinon erreur ldap
				$entry_new=Array();
				foreach ($entry as $key => $value){
						if ($value != ""){
								$entry_new[$key] = $value;
						}
				}
				// Ajout des données dans l'annuaire
				$contact="uid=$id,".$C->ldap->base->value;
				@ldap_delete($ldapconn,$contact);
				$r=ldap_add($ldapconn, $contact, $entry_new);
				foreach($C->ldap->tags->value as $t){
					if ($t->idtag->value>0) {
						error_log($t->idtag->value." ".$t->base->value."\n",3,"../data/log/debug.log");
						$contact="uid=$id,".$t->base->value;
						error_log("suppression casquette n°$id de ".$t->base->value."\n",3,"../data/log/debug.log");
						@ldap_delete($ldapconn,$contact);
						if (Contacts::cas_has_tag($id,$t->idtag->value)) {
							error_log("ajout casquette n°$id à ".$t->base->value."\n",3,"../data/log/debug.log");
							$r=ldap_add($ldapconn, $contact, $entry_new);
						}
					}
				}
				ldap_close($ldapconn);
				return $entry_new;
			}
		}
	}
    function debug_string_backtrace() {
        ob_start();
        debug_print_backtrace();
        $trace = ob_get_contents();
        ob_end_clean();

        // Remove first item from backtrace as it's this function which
        // is redundant.
        $trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1);

        // Renumber backtrace items.
        $trace = preg_replace ('/^#(\d+)/me', '\'#\' . ($1 - 1)', $trace);

        return $trace;
    }
	function cp2dept($cp) {
		if ($cp=="E") return "E";
		if ($cp<1000) return "";
		$n=floor($cp/100);
		if ($n==971) return "$n";
		if ($n==972) return "$n";
		if ($n==973) return "$n";
		if ($n==974) return "$n";
		if ($n==976) return "$n";
		$n=floor($cp/1000);
		if ($n==20 && $cp<20200) $n="2A";
		if ($n==20 && $cp>=20200) $n="2B";
		$n="$n";
		return $n;
	}
	function departement($n) {
		$departements=array();
		$departements['1']=array('nom'=>'Ain ', 'prefecture'=>'Bourg-en-Bresse ', 'region'=>'Rhône-Alpes');
		$departements['2']=array('nom'=>'Aisne ', 'prefecture'=>'Laon ', 'region'=>'Picardie');
		$departements['3']=array('nom'=>'Allier ', 'prefecture'=>'Moulins ', 'region'=>'Auvergne');
		$departements['4']=array('nom'=>'Alpes de Hautes-Provence ', 'prefecture'=>'Digne ', 'region'=>'Provence-Alpes-Côte d\'Azur');
		$departements['5']=array('nom'=>'Hautes-Alpes ', 'prefecture'=>'Gap ', 'region'=>'Provence-Alpes-Côte d\'Azur');
		$departements['6']=array('nom'=>'Alpes-Maritimes ', 'prefecture'=>'Nice ', 'region'=>'Provence-Alpes-Côte d\'Azur');
		$departements['7']=array('nom'=>'Ardèche ', 'prefecture'=>'Privas ', 'region'=>'Rhône-Alpes');
		$departements['8']=array('nom'=>'Ardennes ', 'prefecture'=>'Charleville-Mézières ', 'region'=>'Champagne-Ardenne');
		$departements['9']=array('nom'=>'Ariège ', 'prefecture'=>'Foix ', 'region'=>'Midi-Pyrénées');
		$departements['10']=array('nom'=>'Aube ', 'prefecture'=>'Troyes ', 'region'=>'Champagne-Ardenne');
		$departements['11']=array('nom'=>'Aude ', 'prefecture'=>'Carcassonne ', 'region'=>'Languedoc-Roussillon');
		$departements['12']=array('nom'=>'Aveyron ', 'prefecture'=>'Rodez ', 'region'=>'Midi-Pyrénées');
		$departements['13']=array('nom'=>'Bouches-du-Rhône ', 'prefecture'=>'Marseille ', 'region'=>'Provence-Alpes-Côte d\'Azur');
		$departements['14']=array('nom'=>'Calvados ', 'prefecture'=>'Caen ', 'region'=>'Basse-Normandie');
		$departements['15']=array('nom'=>'Cantal ', 'prefecture'=>'Aurillac ', 'region'=>'Auvergne');
		$departements['16']=array('nom'=>'Charente ', 'prefecture'=>'Angoulême ', 'region'=>'Poitou-Charentes');
		$departements['17']=array('nom'=>'Charente-Maritime ', 'prefecture'=>'La Rochelle ', 'region'=>'Poitou-Charentes');
		$departements['18']=array('nom'=>'Cher ', 'prefecture'=>'Bourges ', 'region'=>'Centre');
		$departements['19']=array('nom'=>'Corrèze ', 'prefecture'=>'Tulle ', 'region'=>'Limousin');
		$departements['2A']=array('nom'=>'Corse-du-Sud ', 'prefecture'=>'Ajaccio ', 'region'=>'Corse');
		$departements['2B']=array('nom'=>'Haute-Corse ', 'prefecture'=>'Bastia ', 'region'=>'Corse');
		$departements['21']=array('nom'=>'Côte-d\'Or ', 'prefecture'=>'Dijon ', 'region'=>'Bourgogne');
		$departements['22']=array('nom'=>'Côtes d\'Armor ', 'prefecture'=>'Saint-Brieuc ', 'region'=>'Bretagne');
		$departements['23']=array('nom'=>'Creuse ', 'prefecture'=>'Guéret ', 'region'=>'Limousin');
		$departements['24']=array('nom'=>'Dordogne ', 'prefecture'=>'Périgueux ', 'region'=>'Aquitaine');
		$departements['25']=array('nom'=>'Doubs ', 'prefecture'=>'Besançon ', 'region'=>'Franche-Comté');
		$departements['26']=array('nom'=>'Drôme ', 'prefecture'=>'Valence ', 'region'=>'Rhône-Alpes');
		$departements['27']=array('nom'=>'Eure ', 'prefecture'=>'Évreux ', 'region'=>'Haute-Normandie');
		$departements['28']=array('nom'=>'Eure-et-Loir ', 'prefecture'=>'Chartres ', 'region'=>'Centre');
		$departements['29']=array('nom'=>'Finistère ', 'prefecture'=>'Quimper ', 'region'=>'Bretagne');
		$departements['30']=array('nom'=>'Gard ', 'prefecture'=>'Nîmes ', 'region'=>'Languedoc-Roussillon');
		$departements['31']=array('nom'=>'Haute-Garonne ', 'prefecture'=>'Toulouse ', 'region'=>'Midi-Pyrénées');
		$departements['32']=array('nom'=>'Gers ', 'prefecture'=>'Auch ', 'region'=>'Midi-Pyrénées');
		$departements['33']=array('nom'=>'Gironde ', 'prefecture'=>'Bordeaux ', 'region'=>'Aquitaine');
		$departements['34']=array('nom'=>'Hérault ', 'prefecture'=>'Montpellier ', 'region'=>'Languedoc-Roussillon');
		$departements['35']=array('nom'=>'Ille-et-Vilaine ', 'prefecture'=>'Rennes ', 'region'=>'Bretagne');
		$departements['36']=array('nom'=>'Indre ', 'prefecture'=>'Châteauroux ', 'region'=>'Centre');
		$departements['37']=array('nom'=>'Indre-et-Loire ', 'prefecture'=>'Tours ', 'region'=>'Centre');
		$departements['38']=array('nom'=>'Isère ', 'prefecture'=>'Grenoble ', 'region'=>'Rhône-Alpes');
		$departements['39']=array('nom'=>'Jura ', 'prefecture'=>'Lons-le-Saunier ', 'region'=>'Franche-Comté');
		$departements['40']=array('nom'=>'Landes ', 'prefecture'=>'Mont-de-Marsan ', 'region'=>'Aquitaine');
		$departements['41']=array('nom'=>'Loir-et-Cher ', 'prefecture'=>'Blois ', 'region'=>'Centre');
		$departements['42']=array('nom'=>'Loire ', 'prefecture'=>'Saint-Étienne ', 'region'=>'Rhône-Alpes');
		$departements['43']=array('nom'=>'Haute-Loire ', 'prefecture'=>'Le Puy-en-Velay ', 'region'=>'Auvergne');
		$departements['44']=array('nom'=>'Loire-Atlantique ', 'prefecture'=>'Nantes ', 'region'=>'Pays de la Loire');
		$departements['45']=array('nom'=>'Loiret ', 'prefecture'=>'Orléans ', 'region'=>'Centre');
		$departements['46']=array('nom'=>'Lot ', 'prefecture'=>'Cahors ', 'region'=>'Midi-Pyrénées');
		$departements['47']=array('nom'=>'Lot-et-Garonne ', 'prefecture'=>'Agen ', 'region'=>'Aquitaine');
		$departements['48']=array('nom'=>'Lozère ', 'prefecture'=>'Mende ', 'region'=>'Languedoc-Roussillon');
		$departements['49']=array('nom'=>'Maine-et-Loire ', 'prefecture'=>'Angers ', 'region'=>'Pays de la Loire');
		$departements['50']=array('nom'=>'Manche ', 'prefecture'=>'Saint-Lô ', 'region'=>'Basse-Normandie');
		$departements['51']=array('nom'=>'Marne ', 'prefecture'=>'Châlons-en-Champagne ', 'region'=>'Champagne-Ardenne');
		$departements['52']=array('nom'=>'Haute-Marne ', 'prefecture'=>'Chaumont ', 'region'=>'Champagne-Ardenne');
		$departements['53']=array('nom'=>'Mayenne ', 'prefecture'=>'Laval ', 'region'=>'Pays de la Loire');
		$departements['54']=array('nom'=>'Meurthe-et-Moselle ', 'prefecture'=>'Nancy ', 'region'=>'Lorraine');
		$departements['55']=array('nom'=>'Meuse ', 'prefecture'=>'Bar-le-Duc ', 'region'=>'Lorraine');
		$departements['56']=array('nom'=>'Morbihan ', 'prefecture'=>'Vannes ', 'region'=>'Bretagne');
		$departements['57']=array('nom'=>'Moselle ', 'prefecture'=>'Metz ', 'region'=>'Lorraine');
		$departements['58']=array('nom'=>'Nièvre ', 'prefecture'=>'Nevers ', 'region'=>'Bourgogne');
		$departements['59']=array('nom'=>'Nord ', 'prefecture'=>'Lille ', 'region'=>'Nord-Pas-de-Calais');
		$departements['60']=array('nom'=>'Oise ', 'prefecture'=>'Beauvais ', 'region'=>'Picardie');
		$departements['61']=array('nom'=>'Orne ', 'prefecture'=>'Alençon ', 'region'=>'Basse-Normandie');
		$departements['62']=array('nom'=>'Pas-de-Calais ', 'prefecture'=>'Arras ', 'region'=>'Nord-Pas-de-Calais');
		$departements['63']=array('nom'=>'Puy-de-Dôme ', 'prefecture'=>'Clermont-Ferrand ', 'region'=>'Auvergne');
		$departements['64']=array('nom'=>'Pyrénées-Atlantiques ', 'prefecture'=>'Pau ', 'region'=>'Aquitaine');
		$departements['65']=array('nom'=>'Hautes-Pyrénées ', 'prefecture'=>'Tarbes ', 'region'=>'Midi-Pyrénées');
		$departements['66']=array('nom'=>'Pyrénées-Orientales ', 'prefecture'=>'Perpignan ', 'region'=>'Languedoc-Roussillon');
		$departements['67']=array('nom'=>'Bas-Rhin ', 'prefecture'=>'Strasbourg ', 'region'=>'Alsace');
		$departements['68']=array('nom'=>'Haut-Rhin ', 'prefecture'=>'Colmar ', 'region'=>'Alsace');
		$departements['69']=array('nom'=>'Rhône ', 'prefecture'=>'Lyon ', 'region'=>'Rhône-Alpes');
		$departements['70']=array('nom'=>'Haute-Saône ', 'prefecture'=>'Vesoul ', 'region'=>'Franche-Comté');
		$departements['71']=array('nom'=>'Saône-et-Loire ', 'prefecture'=>'Mâcon ', 'region'=>'Bourgogne');
		$departements['72']=array('nom'=>'Sarthe ', 'prefecture'=>'Le Mans ', 'region'=>'Pays de la Loire');
		$departements['73']=array('nom'=>'Savoie ', 'prefecture'=>'Chambéry ', 'region'=>'Rhône-Alpes');
		$departements['74']=array('nom'=>'Haute-Savoie ', 'prefecture'=>'Annecy ', 'region'=>'Rhône-Alpes');
		$departements['75']=array('nom'=>'Paris ', 'prefecture'=>'Paris ', 'region'=>'Ile-de-France');
		$departements['76']=array('nom'=>'Seine-Maritime ', 'prefecture'=>'Rouen ', 'region'=>'Haute-Normandie');
		$departements['77']=array('nom'=>'Seine-et-Marne ', 'prefecture'=>'Melun ', 'region'=>'Ile-de-France');
		$departements['78']=array('nom'=>'Yvelines ', 'prefecture'=>'Versailles ', 'region'=>'Ile-de-France');
		$departements['79']=array('nom'=>'Deux-Sèvres ', 'prefecture'=>'Niort ', 'region'=>'Poitou-Charentes');
		$departements['80']=array('nom'=>'Somme ', 'prefecture'=>'Amiens ', 'region'=>'Picardie');
		$departements['81']=array('nom'=>'Tarn ', 'prefecture'=>'Albi ', 'region'=>'Midi-Pyrénées');
		$departements['82']=array('nom'=>'Tarn-et-Garonne ', 'prefecture'=>'Montauban ', 'region'=>'Midi-Pyrénées');
		$departements['83']=array('nom'=>'Var ', 'prefecture'=>'Toulon ', 'region'=>'Provence-Alpes-Côte d\'Azur');
		$departements['84']=array('nom'=>'Vaucluse ', 'prefecture'=>'Avignon ', 'region'=>'Provence-Alpes-Côte d\'Azur');
		$departements['85']=array('nom'=>'Vendée ', 'prefecture'=>'La Roche-sur-Yon ', 'region'=>'Pays de la Loire');
		$departements['86']=array('nom'=>'Vienne ', 'prefecture'=>'Poitiers ', 'region'=>'Poitou-Charentes');
		$departements['87']=array('nom'=>'Haute-Vienne ', 'prefecture'=>'Limoges ', 'region'=>'Limousin');
		$departements['88']=array('nom'=>'Vosges ', 'prefecture'=>'Épinal ', 'region'=>'Lorraine');
		$departements['89']=array('nom'=>'Yonne ', 'prefecture'=>'Auxerre ', 'region'=>'Bourgogne');
		$departements['90']=array('nom'=>'Territoire-de-Belfort ', 'prefecture'=>'Belfort ', 'region'=>'Franche-Comté');
		$departements['91']=array('nom'=>'Essonne ', 'prefecture'=>'Évry ', 'region'=>'Ile-de-France');
		$departements['92']=array('nom'=>'Hauts-de-Seine ', 'prefecture'=>'Nanterre ', 'region'=>'Ile-de-France');
		$departements['93']=array('nom'=>'Seine-Saint-Denis ', 'prefecture'=>'Bobigny ', 'region'=>'Ile-de-France');
		$departements['94']=array('nom'=>'Val-de-Marne ', 'prefecture'=>'Créteil ', 'region'=>'Ile-de-France');
		$departements['95']=array('nom'=>'Val-d\'Oise ', 'prefecture'=>'Pontoise ', 'region'=>'Ile-de-France');
		$departements['971']=array('nom'=>'Guadeloupe', 'prefecture'=>'Basse-Terre', 'region'=>'Guadeloupe');
		$departements['972']=array('nom'=>'Martinique', 'prefecture'=>'Fort-de-France', 'region'=>'Martinique');
		$departements['973']=array('nom'=>'Guyane', 'prefecture'=>'Cayenne', 'region'=>'Guyane');
		$departements['974']=array('nom'=>'La Réunion', 'prefecture'=>'Saint-Denis', 'region'=>'La Réunion');
		$departements['976']=array('nom'=>'Mayotte', 'prefecture'=>'Dzaoudzi', 'region'=>'Mayotte');
		$res=$departements[$n];
		$res['n']=$n;
		return $res;
	}
	function prepend($string, $filename) {
		$context = stream_context_create();
		$fp = fopen($filename, 'r', 1, $context);
		$tmpname = tempnam('tmp/','');
		file_put_contents($tmpname, $string);
		file_put_contents($tmpname, $fp, FILE_APPEND);
		fclose($fp);
		unlink($filename);
		rename($tmpname, $filename);
	}
	function clean_pjs_bloc($id_news, $bloc){
		if (is_object($bloc)) {
			foreach($bloc as $k=>$v) {
				$bloc->$k=clean_pjs_bloc($id_news,$v);
			}
		} elseif (is_array($bloc)){
			foreach($bloc as $k=>$v) {
				$bloc[$k]=clean_pjs_bloc($id_news,$v);
			}
		} else {
			$bloc=preg_replace('/data\/files\/news\/(\d+)\//i',"data/files/news/$id_news/",$bloc);
		}
		return $bloc;
	}
	function ispjused($pj, $bloc){
		$res=false;
		if (is_object($bloc)) {
			foreach($bloc as $k=>$v) {
				$res= $res || ispjused($pj,$v);
			}
		} elseif (is_array($bloc)){
			foreach($bloc as $k=>$v) {
				$res= $res || ispjused($pj,$v);
			}
		} else {
			$res= $pj===$bloc;
		}
		return $res;
	}
	function WS_send($data) {
		$msg=array();
		$msg['id']=-1;
		$msg['key']='1234';
		$msg['data']=$data;
		$command = "nohup /usr/bin/php exec.php ws_send ".base64_encode(json_encode($msg))." > /dev/null 2>&1 &";
		exec($command);
	}
	function WS_maj($types) {
		if (count($types)>0) {
			$msg=array();
			$msg['id']=-1;
			$msg['key']='1234';
			$msg['data']=array('data'=>array(array('action'=>'maj','types'=>$types)));
			$command = "nohup /usr/bin/php exec.php ws_send ".base64_encode(json_encode($msg))." > /dev/null 2>&1 &";
			exec($command);
		}
	}
	function check_doublon_texte($id_contact) {
		$command = "nohup /usr/bin/php exec.php doublon_texte $id_contact > /dev/null 2>&1 &";
		exec($command);
	}
	function check_doublon_emails($emails) {
		$arg=base64_encode(json_encode($emails));
		$command = "nohup /usr/bin/php exec.php doublon_emails $arg > /dev/null 2>&1 &";
		exec($command);
	}
	function doublon_maj($ids_contacts) {
		$arg=base64_encode(json_encode($ids_contacts));
		$command = "nohup /usr/bin/php exec.php doublon_maj $arg > /dev/null 2>&1 &";
		exec($command);
	}
	function conf(){
		$conf=(object) null;
		$conf->ws_port=8082;
		if (file_exists("data/conf.json")) $conf=json_decode(file_get_contents("data/conf.json"));
		else file_put_contents("data/conf.json",json_encode($conf));
		return $conf;
	}
	function hasListAncestor($id, $tags){
		$tag=$tags[$id];
		if ($tag['id_parent']==0) return false;
		else {
			$p=$tags[$tag['id_parent']];
			if ($p['type']=='liste') return $p;
			else return hasListAncestor($p['id'], $tags);
		}
	}
	function typeAncestor($id, $tags){
		$tag=$tags[$id];
		if ($tag['id_parent']==0) return $tag;
		else {
			$p=$tags[$tag['id_parent']];
			if ($p['type']) return $p;
			else return typeAncestor($p['id'], $tags);
		}
	}
	function normaux($id_tag,$tags){
		$tab=array();
		foreach($tags as $e) {
			if ($id_tag==$e['id_parent'] && !$e['type']) $tab[]=$e['id'];
		}
		if (count($tab)==0) return $tab;
		else {
			foreach($tab as $e) {
				$tab=array_merge($tab,normaux($e,$tags));
			}
		}
		return array_unique($tab);
	}
	function descendants($tag,$tags){
		$tab=array();
		foreach($tags as $e) {
			if ($e['id_parent']==$tag['id']) $tab[]=$e;
		}
		if (count($tab)==0) return $tab;
		else {
			foreach($tab as $e) {
				$tab= array_merge($tab,descendants($e,$tags));
			}
		}
		return $tab;
	}
	define('OFFSET', 268435456);
	define('RADIUS', 85445659.4471); /* $offset / pi() */

	function lonToX($lon) {
	    return round(OFFSET + RADIUS * $lon * pi() / 180);
	}

	function latToY($lat) {
	    return round(OFFSET - RADIUS *
		        log((1 + sin($lat * pi() / 180)) /
		        (1 - sin($lat * pi() / 180))) / 2);
	}

	function pixelDistance($lat1, $lon1, $lat2, $lon2, $zoom) {
	    $x1 = lonToX($lon1);
	    $y1 = latToY($lat1);

	    $x2 = lonToX($lon2);
	    $y2 = latToY($lat2);

	    return sqrt(pow(($x1-$x2),2) + pow(($y1-$y2),2)) >> (22 - $zoom);
	}

	function oMerge($o1,$o2){
		$a1=(array)$o1;
		$a2=(array)$o2;
		return (object) array_merge($a1, $a2);
	}
	function deleteDirectory($dirPath) {
	    if (is_dir($dirPath)) {
	        $objects = scandir($dirPath);
	        foreach ($objects as $object) {
	            if ($object != "." && $object !="..") {
	                if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
	                    deleteDirectory($dirPath . DIRECTORY_SEPARATOR . $object);
	                } else {
	                    unlink($dirPath . DIRECTORY_SEPARATOR . $object);
	                }
	            }
	        }
	    reset($objects);
	    rmdir($dirPath);
	    }
	}
	function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
		$header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
		mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
	}
	function utf8_for_xml($string)
	{
	    return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
	}
?>
