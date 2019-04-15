var app= angular.module('form', ['ngRoute','ui.bootstrap', 'angularFileUpload','ngSanitize','ng.ckeditor','fakeWs']);
app.config(['$routeProvider', function($routeProvider) {
	angular.lowercase = angular.$$lowercase;
	$routeProvider.when('/form/:hash', {templateUrl: 'partials/form_public.html', controller: 'showformCtl'});
	$routeProvider.otherwise({redirectTo: '/form'});
}]);
app.config(['$locationProvider', function($locationProvider) {
	$locationProvider.html5Mode(true);
}]);
app.controller('mainCtl', ['$scope', '$location', '$timeout', '$interval', '$sce', 'Link', 'Data', function ($scope, $location, $timeout, $interval, $sce, Link, Data) {
	$scope.now=new Date().getTime();
	$scope.idform=document.getElementById("id-form").value;
	$scope.nom=document.getElementById("nom").value;
	$scope.prenom=document.getElementById("prenom").value;
	$scope.autresinstances=document.getElementById("autres-instances").value.split(',');
	$scope.hash=document.getElementById("hash").value;
	$scope.formkey="form/"+$scope.idform;
	Data.accept_anonymous=true;
	$scope.help=function(id){
		$uibModal.open({
			templateUrl: 'partials/inc/help_'+id+'.html'
		});
	};
	$scope.isAnswer=function(){
		var Qparams={};
		for(var i=0;i<Data.contexts.length;i++){
			if (Data.contexts[i].type=='casquettes') Qparams=Data.contexts[i].params;
		}
		if ('query' in Qparams && 'casquettes' in Data.modele) {
			Aparams=Data.modele.casquettes.params;
			return $scope.isEqual(Qparams,Aparams);
		}
		return true;
	};
	$scope.uploaders={};
	$scope.uploading=function(){
		var res=false;
		angular.forEach($scope.uploaders,function(u){
			if(u.queue.length>0 && u.progress<100) res=true;
		});
		return res;
	};
	$scope.done=false;
	$scope.trust=function(html){
		return $sce.trustAsHtml(html);
	}
	$scope.calendar=function(t){
		moment.lang('fr');
		var date=moment(parseInt(t));;
		return date.calendar();
	}
	$scope.calendarHeure=function(t){
		moment.lang('fr_heure');
		var date=moment(parseInt(t));;
		return date.calendar();
	}
	$scope.calendarSansHeure=function(t){
		moment.lang('fr_sans_heure');
		var date=moment(parseInt(t));;
		return date.calendar();
	}
	$scope.horaire=function(t){
		var date=moment(parseInt(t) * 1000);;
		return date.format('HH[h]mm:ss');
	}
	$scope.byId=function(a,id){
		var res=false;
		angular.forEach(a,function(e){
			if(e.id==id) {
				return e;
			}
		});
		return res;
	};
	$scope.isEqual=function(a,b){
		var diff= rfc6902.createPatch(a,b);
		var d=[];
		for(var i=0;i<diff.length;i++) {
			if (diff[i].path.indexOf("$$")==-1) d.push(diff[i]);
		}
		return d.length==0;
	};
	$scope.pristine=function(key){
		return $scope.isEqual(Data.modele[key],Data.modeleSrv[key]);
	}
	$scope.dirty=function(key){
		return !$scope.pristine(key);
	}
	$scope.min=function(a,b){
		return Math.min(a,b);
	};
	$scope.index=function(k,a,v){
		var i=-1;
		angular.forEach(a,function(e){
			if(e[k]==v) {
				i=a.indexOf(e);
				return;
			}
		});
		return i;
	};
	$scope.elByIndex=function(k,a,v){
		var o;
		angular.forEach(a,function(e){
			if(e[k]==v) {
				o=e;
				return;
			}
		});
		return o;
	};
	$scope.$on('link-ready', function(event, args) {
		var data=[{
			action:'public-login',
			params: {}
		}];
		Link.ajax(data);
	});
	$scope.$on('data-update', function(event, args) {
		$scope.$apply();
	});
	$scope.Data=Data;
	$scope.editorOk=false;
	$scope.$watch(function(){return CKEDITOR.status;},function(o,n){
		$scope.editorOk=true;
	});
}]);
app.controller('showformCtl', ['$routeParams','$scope', '$http', '$location', '$interval', '$uibModal', 'FileUploader', 'Link', 'Data', function ($routeParams, $scope, $http, $location, $interval, $uibModal, FileUploader, Link, Data) {
	$scope.currenthash=$routeParams.hash;
	$scope.key='form_instance/'+$routeParams.hash;
	$scope.label=function(label){
		var tab=label.split('|');
		var res=tab[0].trim();
		for (var i=1;i<tab.length;i++){
			res+=' <span class="traduction">/ '+tab[i].trim()+'</span>';
		}
		return res;
	}
	$scope.check=function(hash,elt){
		if (elt.default===undefined) elt.default='';
		if (!Data.modele[$scope.key].collection[elt.id]) Data.modele[$scope.key].collection[elt.id]={id_schema:elt.id,valeur:elt.default,type:elt.type};
	}
	$scope.checkAll=function(){
		console.log('checkAll',Data.modele[$scope.key]);
		angular.forEach(Data.modele[$scope.key].form.schema.pages,function(p){
			angular.forEach(p.elts,function(elt){
				if (elt.type!='titre' && elt.type!='texte') $scope.check(Data.modele[$scope.key].hash,elt);
				if (elt.type=='upload') {
					if (!$scope.uploaders[Data.modele[$scope.key].hash+'-'+elt.id]) {
						$scope.uploaders[Data.modele[$scope.key].hash+'-'+elt.id] = new FileUploader({
							url: 'upload.php',
							autoUpload:true,
							formData:[{hash:Data.modele[$scope.key].hash,id:elt.id},{type:'form_upload'}],
							onCompleteAll:function(){
								$scope.uploaders[Data.modele[$scope.key].hash+'-'+elt.id].clearQueue();
							}
						});
					}
				}
			});
		});
		console.log('checkAll end',Data.modele[$scope.key]);
		$scope.save();
	};
	$scope.delFile=function(hash,id_elt,f){
		Link.ajax([{action:'delFormFile', params:{hash:hash,id_elt:id_elt,file:f.nom}}]);
	};
	$scope.save=function(){
		Link.ajax([{action:'modFormInstance',params:{instance:Data.modele[$scope.key]}}]);
	};
	$scope.editorOptions = {
		height:"200px",
		language: 'fr',
		skin:"minimalist",
		toolbarGroups:[
			{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
			{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
			{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
			{ name: 'forms', groups: [ 'forms' ] },
			{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
			{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
			{ name: 'links', groups: [ 'links' ] },
			{ name: 'insert', groups: [ 'insert' ] },
			{ name: 'styles', groups: [ 'styles' ] },
			{ name: 'colors', groups: [ 'colors' ] },
			{ name: 'tools', groups: [ 'tools' ] },
			{ name: 'others', groups: [ 'others' ] },
			{ name: 'about', groups: [ 'about' ] }
		],
		removeButtons:"Source,Save,NewPage,Preview,Print,Templates,Cut,Undo,Redo,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,HiddenField,Checkbox,TextField,Textarea,Select,Button,ImageButton,Radio,Strike,Subscript,Superscript,NumberedList,Outdent,Indent,BulletedList,Blockquote,CreateDiv,BidiLtr,BidiRtl,Language,Anchor,Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Styles,Format,Font,BGColor,ShowBlocks,About"
	};
	$scope.isValid={};
	$scope.testValid=function(){
		angular.forEach(Data.modele[$scope.key].form.schema.pages,function(p){
			angular.forEach(p.elts,function(elt){
				if (elt.type=='texte_long' && elt.maxLength) {
					var StrippedString = Data.modele[$scope.key].collection[elt.id].valeur.replace(/(<([^>]+)>)/ig,"");
					var tab=StrippedString.split(' ');
					var t=tab.length<=elt.maxLength;
					var o="Texte trop long ("+tab.length+" mots pour "+elt.maxLength+" autorisés)";
					if (!t){
						if($scope.isValid[elt.id]!=o) $scope.isValid[elt.id]=o;
					} else delete($scope.isValid[elt.id]);
				}
			});
		});
	};
	$scope.setMultiple=function(p,choix){
		var tab=p.collection[p.elt.id].valeur.split(',');
		var i=tab.indexOf(choix.valeur);
		if (tab[0]=='') tab.splice(0,1);
		console.log(p.collection[p.elt.id].valeur,tab,p.elt.nbMax);
		if (i<0) {
			if (tab.length<p.elt.nbMax) tab.push(choix.valeur);
			else {
				tab.splice(tab.length-1,1);
				tab.push(choix.valeur);
			}
		}
		else tab.splice(i,1);
		p.collection[p.elt.id].valeur=tab.join();
	}
	$scope.canSave=function(){
		$scope.testValid();
		//console.log(Object.keys($scope.isValid).length,$scope.dirty($scope.key));
		return Object.keys($scope.isValid).length==0 && $scope.dirty($scope.key);
	};
	$scope.contexts=[{type:$scope.formkey},{type:'form_instance/'+$scope.hash}];
	angular.forEach($scope.autresinstances,function(hash){
		$scope.contexts.push({type:'form_instance/'+hash});
	});
	Link.context($scope.contexts);
}]);
