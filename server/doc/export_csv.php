<?php
/**
 *
 * @license    GPL 3 (http://www.gnu.org/licenses/gpl.html)
 * @author     Fabrice Lapeyrere <fabrice@surlefil.org>
 */
set_time_limit(0);
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'fake_ws/conf.php';
include 'conf/main.php';
include 'conf/auth.php';

	$params=json_decode(json_encode($_POST));
    $filename="data/tmp/export".time().".csv";
	$fp = fopen($filename, 'w');
	$selection=array();
    $query=$params->res->query;
    $casquettes=Contacts::get_casquettes(array('query'=>$query,'page'=>1,'nb'=>10,'all'=>true),0,$S['user']['id']);
	$keys=array();
	foreach ($casquettes['collection'] as $cas) {
		$tab=array();
		$tab['1_nom']=$cas['nom'];
		$tab['2_prenom']=$cas['prenom'];
		$donnees=$cas['donnees'];
		$adresse_ok=false;
		foreach($donnees as $donnee){
			if ($donnee->value!=""){
				if ($donnee->type=='adresse') {
					$adresse_ok=true;
					$adresse=$donnee->value;
					$tab["3_adresse"]=$adresse->adresse;
					$tab["3_cp"]=$adresse->cp;
					$tab["3_ville"]=$adresse->ville;
					$tab["3_pays"]=$adresse->pays;
				}
				else $tab["3_".$donnee->label]=$donnee->value;
			}
		}
		if ($cas['id_etab']>0) {
			if (!$adresse_ok){
				foreach($cas['donnees_etab'] as $donnee){
					if ($donnee->value!=""){
						if ($donnee->type=='adresse') {
							$adresse=$donnee->value;
							$tab["3_adresse"]=$adresse->adresse;
							$tab["3_cp"]=$adresse->cp;
							$tab["3_ville"]=$adresse->ville;
							$tab["3_pays"]=$adresse->pays;
						}
						else $tab["3_".$donnee->label]=$donnee->value;
					}
				}
			}
			$tab['4_structure']=$cas['nom_etab'];
		}
		$tab['6_listes']="";
		$categories=$cas['tags'];
		$listes=array();
		foreach($categories as $id_cat){
			$cat=Contacts::get_tag($id_cat);
			$listes[]=$cat['nom'];
		}
		$tab['6_listes']=implode(', ',$listes);
        foreach($tab as $cle=>$valeur){
			if (!in_array($cle,$keys)) $keys[]=$cle;
		}
        $csv=array();
		for($i=0;$i<count($keys);$i++){
            $v='';
			if (isset($tab[$keys[$i]])) $v=$tab[$keys[$i]];
            $csv[]=$v;
		}
		fputcsv($fp, $csv);
	}
    $header=array();
	foreach($keys as $cle){
		$header[]=substr($cle,2);
	}
	fclose($fp);
	prepend(implode(',',$header)."\n",$filename);
	header("Location: $filename");
?>	
