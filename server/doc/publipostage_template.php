<?php
include_once('./server/lib/tbs_class.php');
include_once('./server/lib/tbs_plugin_opentbs.php');
set_time_limit(0);
$params=json_decode(json_encode($_POST));
$query=$params->res->query;
$template=$params->template;

$TBS = new clsTinyButStrong;
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
$TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);
$TBS->SetOption('noerr', true);

$casquettes=Contacts::get_casquettes(array('query'=>$query,'page'=>1,'nb'=>10,'all'=>true),0,$my_session->user->id);
$modele=array_values($casquettes['collection']);
$data=array();
foreach ($modele as $key => $cas) {
    $contact=$cas;
    $contacts['data']=array();
    foreach($cas['donnees'] as $d) {
        if ($d->type!='adresse') $contact['data'][filter2($d->label)]=$d->value;
    }
    if ($cas['id_etab']>0)
        $contact['adresse']=$cas['nom_etab']."\n".trim(adresse($cas['donnees_etab']));
    if ($contact['adresse']=='')
        $contact['adresse']=adresse($cas['donnees']);
    $contact['nom_complet']=$cas['prenom'];
    if ($contact['nom_complet']!="") $contact['nom_complet'].=" ".$cas['nom'];
    else $contact['nom_complet']=$cas['nom'];
    if ($contact['data']['civilite']!="") $contact['nom_complet_civilite']=$contact['data']['civilite']." ".$contact['nom_complet'];
    else $contact['nom_complet_civilite']=$contact['nom_complet'];
    $data[]=$contact;
}
$TBS->MergeBlock('c',$data);
error_log(var_export($data,true),3,"/tmp/fab.log");
$TBS->Show(OPENTBS_DOWNLOAD, 'doc.odt');
