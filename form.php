<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'conf/main.php';
include 'conf/session.php';
$conf=conf();
$hash=$_REQUEST['h'];
$db= new DB();
$query = "SELECT * FROM form_casquette WHERE hash='$hash'";
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
    $id_form=$row['id_form'];
    $id_cas=$row['id_casquette'];
}
$query = "SELECT * FROM forms WHERE id=$id_form";
foreach($db->database->query($query, PDO::FETCH_ASSOC) as $row){
    $form=$row;
}
$t=millisecondes();
if (
    !isset($id_form)
){
?>
    <html>
    <head>
    <title>Erreur</title>
    <meta charset="UTF-8">
    </head>
    <body>
        La page demandée n'existe pas.
    </body>
    </html>
<?
} elseif (
    $form['state']=='closed'
    || $form['state']=='scheduled' && ($t<$form['from_date'] || $t>$form['to_date'])
){
?>
    <html>
    <head>
    <title>Erreur</title>
    <meta charset="UTF-8">
    </head>
    <body>
        Ce formulaire est fermé.
    </body>
    </html>
<?
} else {
?>
<html ng-app="form" ng-controller="mainCtl">
<head>
<title ng-bind="Data.modele[key].nom"></title>
<base href="<?=RACINE?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
<link href="lib/css/angular-motion.min.css" media="all" type="text/css" rel="stylesheet">
<link href="lib/css/bootstrap-switch.css" media="all" type="text/css" rel="stylesheet">
<link href="css/contacts.css" media="all" type="text/css" rel="stylesheet">
<link href="css/publicform.css" media="all" type="text/css" rel="stylesheet">
</head>
<body>
<input type="hidden" id="ws-port" value="<?=$conf->ws_port?>"/>
<input type="hidden" id="id-form" value="<?=$id_form?>"/>
<input type="hidden" id="id-cas" value="<?=$id_cas?>"/>
<div id="form-container" ng-controller="showformCtl">
    <ng-include src="'partials/form_public.html'"></ng-include>
</div>
<div class='app-loader-container'><span ng-class="{'ok':uploading()}" class="glyphicon glyphicon-upload"></span> <span ng-class="{'ok':!Data.modeleFresh || !isAnswer()}" class="glyphicon glyphicon-refresh"></span></div>
<div id="main-lock" ng-if="Data.offline">Connection en cours...</div>
<script src="lib/rfc6902.min.js"></script>
<script src="lib/angular-1.4.9.min.js"></script>
<script src="lib/angular-animate.min.js"></script>
<script src="lib/angular-touch.min.js"></script>
<script src="lib/angular-locale_fr-fr.js"></script>
<script src="lib/angular-route.min.js"></script>
<script src="lib/angular-resource.min.js"></script>
<script src="lib/ui-bootstrap-tpls-1.1.2.min.js"></script>
<script src="lib/angular-sanitize.min.js"></script>
<script src="lib/angular-file-upload.min.js"></script>
<script src="lib/moment-with-langs.js"></script>
<script src="lib/ckeditor/ckeditor.js"></script>
<script src="lib/ng-ckeditor.min.js"></script>
<script src="lib/mousewheel.js"></script>
<script src="lib/hamster.js"></script>
<script src="lib/websocketR2.js"></script>
<script src="js/ws.js"></script>
<script src="js/utils.js"></script>
<script src="js/form.js"></script>
<script src="js/directives.js"></script>
<script src="js/filtres.js"></script>
</body>
</html>
<?}
