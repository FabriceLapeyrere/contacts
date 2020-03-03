<?php
$input=$argv[2];
$output=$argv[3];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$row = 1;
$header=array();
$errorlines=array();
$lignes=array();

$nom_idx=-1;
$prenom_idx=-1;
$fonction_idx=-1;
$id_idx=-1;
$idstr_idx=-1;
$type_idx=-1;
$structure_idx=-1;
$adresse_idx=-1;
$ville_idx=-1;
$cp_idx=-1;
$pays_idx=-1;

if (($handle = fopen($input, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($row>1) {
            $lignes[]=$data;
        }
        if ($row==1) {
            $num = count($data);
            $header=$data;
            if (!in_array('id',$header)) $header[]='id';
            if (!in_array('idstr',$header)) $header[]='idstr';
            if (!in_array('type',$header)) $header[]='type';
            foreach ($header as $key => $value) {
                echo $key."/".$value.", ";
                if ($value=='nom') $nom_idx=$key;
                if ($value=='prenom') $prenom_idx=$key;
                if ($value=='fonction') $fonction_idx=$key;
                if ($value=='structure') $structure_idx=$key;
                if ($value=='adresse') $adresse_idx=$key;
                if ($value=='ville') $ville_idx=$key;
                if ($value=='cp') $cp_idx=$key;
                if ($value=='pays') $pays_idx=$key;
                if ($value=='id') $id_idx=$key;
                if ($value=='idstr') $idstr_idx=$key;
                if ($value=='type') $type_idx=$key;
            }
            echo "\n";
        } else {
            if (count($data)!=$num) $error_lines[]=$row;
        }
        $row++;
    }
    fclose($handle);
}
if ($nom_idx==-1 || $prenom_idx==-1 || $fonction_idx==-1 || $structure_idx==-1){
        echo "Les colonnes structure, nom, prenom et fonction sont obligatoires.\n";
        exit(0);
}
$contacts=array();
$structures=array();
$id=1;
foreach($lignes as $k_ligne=>$ligne) {
    $ligne[$id_idx]="";
    $ligne[$idstr_idx]="";
    $ligne[$type_idx]="";
    if ($ligne[$structure_idx]!="" && $ligne[$nom_idx]=="" && $ligne[$prenom_idx]=="" && $ligne[$fonction_idx]=="") {
        //structure seule
        $ligne[$type_idx]=2;
        $ligne[$id_idx]=$id;
        $ligne[$nom_idx]=$ligne[$structure_idx];
        $structures[]=$ligne;
        $id++;
    } else {
        if ($ligne[$structure_idx]!=""){
            //structure avec contact
            $add_structure=true;
            $str_current=array();
            $idstr=-1;
            foreach ($structures as $key => $value) {
                if ($value[$nom_idx]==$ligne[$structure_idx]) {
                    //la structure existe
                    $add_structure=false;
                    $idstr=$value[$id_idx];
                    $str_current=$value;
                }
            }
            if ($add_structure){
                //la structure n'existe pas, on l'ajoute
                $s=array_fill(0,count($header),"");
                $s[$id_idx]=$id;
                $ligne[$idstr_idx]=$id;
                $s[$nom_idx]=$ligne[$structure_idx];
                $s[$type_idx]=2;
                if ($adresse_idx!=-1) {
                    $s[$adresse_idx]=$ligne[$adresse_idx];
                    $ligne[$adresse_idx]="";
                }
                if ($ville_idx!=-1) {
                    $s[$ville_idx]=$ligne[$ville_idx];
                    $ligne[$ville_idx]="";
                }
                if ($cp_idx!=-1) {
                    $s[$cp_idx]=$ligne[$cp_idx];
                    $ligne[$cp_idx]="";
                }
                if ($pays_idx!=-1) {
                    $s[$pays_idx]=$ligne[$pays_idx];
                    $ligne[$pays_idx]="";
                }

                $structures[]=$s;
                $id++;
            } else {
                //la structure existe, on fait le lien et on enlève l'adresse
                if ($str_current[$adresse_idx]==$ligne[$adresse_idx]
                    && $str_current[$cp_idx]==$ligne[$cp_idx]
                    && $str_current[$ville_idx]==$ligne[$ville_idx]
                    && $str_current[$pays_idx]==$ligne[$pays_idx]
                ) {
                    $ligne[$adresse_idx]='';
                    $ligne[$cp_idx]='';
                    $ligne[$ville_idx]='';
                    $ligne[$pays_idx]='';
                }
                $ligne[$idstr_idx]=$idstr;
            }
        }
        //dans tous les cas, on ajoute le contact
        $ligne[$id_idx]=$id;
        $ligne[$type_idx]=1;
        $contacts[]=$ligne;
        $id++;
    }
}
$fp = fopen($output, 'w');
fputcsv($fp, $header);
foreach ($contacts as $fields) {
    fputcsv($fp, $fields);
}
foreach ($structures as $fields) {
    fputcsv($fp, $fields);
}
