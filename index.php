<?php
require __DIR__ . '/vendor/autoload.php';
foreach (glob("server/*.php") as $filename)
{
    include $filename;
}
include 'conf/main.php';
include 'conf/session.php';
$conf=conf();
?>
<html ng-app="contacts" ng-controller="mainCtl">
<head>
<title ng-bind="Data.modeleSrv.config.config.app.brand.value"></title>
<base href="<?=RACINE?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta charset="UTF-8">
<link href="lib/css/bootstrap.min.css" media="all" type="text/css" rel="stylesheet">
<link href="lib/css/angular-motion.min.css" media="all" type="text/css" rel="stylesheet">
<link href="lib/css/bootstrap-switch.css" media="all" type="text/css" rel="stylesheet">
<link href="lib/css/hotkeys.css" media="all" type="text/css" rel="stylesheet">
<link href="lib/css/cssreset-context-min.css" rel="stylesheet" type="text/css">
<link href="lib/css/cssbase-context-min.css" rel="stylesheet" type="text/css">
<link href="lib/css/mapbox-gl.css" rel="stylesheet" type="text/css">
<link href="css/contacts.css" media="all" type="text/css" rel="stylesheet">
</head>
<body>
<input type="hidden" id="ws-port" value="<?=$conf->ws_port?>"/>
<div ng-if="Data.user.id>=0" ng-class="{'navbar':Data.user.id>=0, 'navbar-default':Data.user.id>=0}" role="navigation" bs-navbar ng-include="'partials/menu.html'">
</div>
<div class="col-xs-12 col-md-6" ng-show="map.show" ng-if="map.ok">
	<div class="carte" mymapbox
	mb-sources="map.sources"
	mb-layers="map.layers"
	></div>
</div>
<div id="main-container" ng-view></div>
<div ng-if="Data.user.id>=0" id="chat" ng-class="{'visible':chatVisible}" ng-include="'partials/chat.html'" ng-swipe-right="chatNavPrec()" ng-swipe-left="chatNavSuiv()"></div>
<div id="main-lock" ng-if="Data.offline">Connection en cours...</div>
<div class='app-loader-container'><span ng-class="{'ok':uploading()}" class="glyphicon glyphicon-upload"></span> <span ng-class="{'ok':!Data.modeleFresh || !isAnswer()}" class="glyphicon glyphicon-refresh"></span></div>
<script src="lib/angular-1.7.8.min.js"></script>
<script src="lib/angular-animate.min.js"></script>
<script src="lib/angular-touch.min.js"></script>
<script src="lib/angular-locale_fr-fr.js"></script>
<script src="lib/angular-route.min.js"></script>
<script src="lib/angular-resource.min.js"></script>
<script src="lib/ui-bootstrap-tpls-1.1.2.js"></script>
<script src="lib/angular-sanitize.min.js"></script>
<script src="lib/angular-file-upload.min.js"></script>
<script src="lib/draganddrop.js"></script>
<script src="lib/autofill-event.js"></script>
<script src="lib/angular-toggle-switch.min.js"></script>
<script src="lib/moment-with-langs.js"></script>
<script src="lib/hotkeys.js"></script>
<script src="lib/mapbox-gl.js"></script>
<script src="lib/ckeditor/ckeditor.js"></script>
<script src="lib/ng-ckeditor.min.js"></script>
<script src="lib/mousewheel.js"></script>
<script src="lib/hamster.js"></script>
<script src="lib/angular.audio.js"></script>
<script src="lib/peg-0.9.0.min.js"></script>
<script src="lib/scrollglue.js"></script>
<script src="lib/websocketR2.js"></script>
<script src="js/ws.js"></script>
<script src="js/utils.js"></script>
<script src="js/contacts.js"></script>
<script src="js/directives.js"></script>
<script src="js/mapbox.js"></script>
<script src="js/filtres.js"></script>
</body>
</html>
