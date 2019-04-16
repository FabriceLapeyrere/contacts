<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'conf/main.php';
$C=Config::get();
function aj_contact($params)
{
	$c=(object) null;
	$c->contact=(object) null;
	$c->contact->nom=$params['nom'];
	$c->contact->prenom=$params['prenom'];
	$c->contact->type=1;
    $maj=array();
    $tab=Contacts::do_add_contact($c,1);
    $maj=array_merge($maj,$tab['maj']);
	$contact=Contacts::get_contact($tab['res'],false,1);
	$p=(object) null;
	foreach($contact['casquettes'] as $cas){
		$p->cas= (object) $cas;
		$p->cas->donnees=$params['donnees'];
	}
    $tab=Contacts::do_mod_casquette($p,1);
	$maj=array_merge($maj,$tab['maj']);
    $maj=array_values(array_unique($maj));
    WS_maj($maj);
	return $p->cas->id;
}
function casquette($email)
{
	$db= new DB(true);
	$query="select id from casquettes where emails like '%$email%'";
	$id=0;
	foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
		$id=$row['id'];
	}
	$casquette=array();
	if ($id>0) {
		$casquette[]=Contacts::get_casquette($id,false,1);
	}
	return $casquette;
}

function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
  $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}
if (isset($_GET['cle'])) {
	if (file_exists("data/cle/".$_GET['cle'])) {
		$fichier=file("data/cle/".$_GET['cle']);
		$params['nom']=trim($fichier[0]);
		$params['prenom']=trim($fichier[1]);
		$params['donnees']=array();
		$params['donnees'][]=(object) array(
			'label'=>'E-mail',
			'type'=>'email',
			'value'=>trim($fichier[2])
		);
		$id_form=trim($fichier[3]);
        $form=Forms::get_form($id_form,1);
        if (isset($form['schema']->mail_body) && $form['schema']->mail_body!="") $mail_body=$form['schema']->mail_body;
        $mail_body_confirm="";
        if (isset($form['schema']->mail_body_confirm) && $form['schema']->mail_body_confirm!="") $mail_body_confirm=$form['schema']->mail_body_confirm;
        if ($form['id']>0) {
			if (isset($fichier[4]) && $fichier[4]=='done')
			{
                $casquette=casquette(trim($fichier[2]));
				if (count($casquette)>0){
                    $hashs=Forms::get_form_instances_cas_form($casquette[0]['id'],$form['id'],1);
                    if (count($hashs)>0) {
                        foreach ($hashs as $h=>$f) {
                            $hash=$h;
                        }
                        header('location:'.$C->app->url->value."/form/".$h);
                    } else {
                        $msg="Quelque chose s'est mal passé, veuillez <a href='{$C->app->url->value}/formulaire.php?id=".$form['id']."'>essayer à nouveau</a>.";
                        ?>
                        <html>
                        <head>
                        <title>Inscription au formulaire : <?=$form['nom']?></title>
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <meta charset="UTF-8">
                        <link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
                        </head>
                        <body style="background-color:#FFF;color:#000;">
                        	<div class="col-xs-12 col-md-6 col-md-offset-3">
                        		<p><?=$msg?></p>
                            </div>
                        </body>
                        </html>
                        <?
                    }
                } else {
                    $msg="Quelque chose s'est mal passé, veuillez <a href='{$C->app->url->value}/formulaire.php?id=".$form['id']."'>essayer à nouveau</a>.";
?>
<html>
<head>
<title>Inscription au formulaire : <?=$form['nom']?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
</head>
<body style="background-color:#FFF;color:#000;">
	<div class="col-xs-12 col-md-6 col-md-offset-3">
		<p><?=$msg?></p>
    </div>
</body>
</html>
<?
                }
            } else {
				$message="Bonjour!!
Une nouvelle inscription au formulaire ".$form['nom']." :
Nom : {$params['nom']}
Prenom : {$params['prenom']}
email : {$params['donnees'][0]->value}

ciao";
				foreach(explode(",",$C->app->mails_notification->value) as $dest){
					mail_utf8(trim($dest),"Inscription automatique au formulaire ".$form['nom'],$message,'From: '.$C->app->mails_notification_from->value);
				}
				$id_cas=aj_contact($params);
                $addParams=new stdClass;
                $addParams->id_cas=$id_cas;
                $addParams->id_form=$id_form;
                $tab=Forms::do_add_form_instance_cas($addParams,1);
                WS_maj($tab['maj']);
                $instance=Forms::get_form_instance($tab['res'],1);
                $fichier[]='done';
				file_put_contents("data/cle/".$_GET['cle'],$fichier);
                $message="Bonjour,

Voici le lien pour remplir le formulaire : ".$form['nom']." :

{$C->app->url->value}/form.php?h=".$instance['hash']."
";
                if ($mail_body!='') {
                    $message=str_replace('##URL##',$C->app->url->value."/form/".$instance['hash'],$mail_body);
                }
                mail_utf8($fichier[2],"Votre lien / ".$form['nom'],$message,'From: '.$C->app->mails_notification_from->value);
                header('location:'.$C->app->url->value."/form/".$instance['hash']);
			}
		}
	}
}
?>
