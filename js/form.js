var app= angular.module('form', ['ngRoute','ui.bootstrap', 'angularFileUpload','ngSanitize','ng.ckeditor','fakeWs']);
app.config(['$routeProvider', function($routeProvider) {
	angular.lowercase = angular.$$lowercase;
	$routeProvider.when('/form/:hash', {templateUrl: 'partials/form_public.html', controller: 'showformCtl'});
	$routeProvider.when('/form', {templateUrl: 'partials/form_public_empty.html'});
	$routeProvider.otherwise({redirectTo: '/form'});
}]);
app.config(['$locationProvider', function($locationProvider) {
	$locationProvider.html5Mode(true);
}]);
app.controller('mainCtl', ['$scope', '$location', '$timeout', '$interval', '$sce', 'Link', 'Data', function ($scope, $location, $timeout, $interval, $sce, Link, Data) {
	$scope.now=new Date().getTime();
	$scope.idform=document.getElementById("id-form").value;
	$scope.idcas=document.getElementById("id-cas").value;
	$scope.nom=document.getElementById("nom").value;
	$scope.prenom=document.getElementById("prenom").value;
	$scope.hash=document.getElementById("hash").value;
	$scope.public_header=document.getElementById("public-header").value;
	$scope.public_footer=document.getElementById("public-footer").value;
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
			if (diff[i].path.indexOf("$$")==-1 && diff[i].path.indexOf("uuid")==-1) d.push(diff[i]);
		}
		return d.length==0;
	};
	$scope.pristine=function(key){
		return $scope.isEqual(Data.modele[key],Data.modeleSrv[key]);
	}
	$scope.pristineKey=function(key,k){
		return $scope.isEqual(Data.modele[key][k],Data.modeleSrv[key][k]);
	}
	$scope.dirty=function(key){
		return !$scope.pristine(key);
	}
	$scope.dirtyKey=function(key,k){
		return !$scope.pristineKey(key,k);
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
	$scope.checkOk={};
	$scope.key='form_instances_cas_form/'+$scope.idcas+"/"+$scope.idform;
	$scope.label=function(label){
		if (label) {
			var tab=label.split('|');
			var res=tab[0].trim();
			for (var i=1;i<tab.length;i++){
				res+=' <span class="traduction">/ '+tab[i].trim()+'</span>';
			}
			return res;
		} else return '';
	}
	$scope.check=function(hash,elt){
		if (elt.default===undefined) elt.default='';
		if (!Data.modele[$scope.key][$scope.currenthash].collection[elt.id]) Data.modele[$scope.key][$scope.currenthash].collection[elt.id]={id_schema:elt.id,valeur:elt.default,type:elt.type};
		//console.log($scope.currenthash,elt.id,Data.modele[$scope.key][$scope.currenthash].collection[elt.id]);
	}
	$scope.checkAll=function(){
		//console.log('checkAll',Data.modele[$scope.key][$scope.currenthash]);
		var nb=Object.keys(Data.modele[$scope.key][$scope.currenthash].collection).length;
		angular.forEach(Data.modele[$scope.formkey].schema.pages,function(p){
			angular.forEach(p.elts,function(elt){
				if (elt.type!='titre' && elt.type!='texte') $scope.check($scope.currenthash,elt);
				if (elt.type=='upload') {
					var maxSize=10*1000*1000;
					if (elt.maxSize) maxSize=elt.maxSize*1000*1000;
					if (!$scope.uploaders[$scope.currenthash+'-'+elt.id]) {
						$scope.uploaders[$scope.currenthash+'-'+elt.id] = new FileUploader({
							url: 'upload.php',
							autoUpload:true,
							formData:[{hash:$scope.currenthash,id:elt.id},{type:'form_upload'}],
							onCompleteAll:function(){
								$scope.uploaders[$scope.currenthash+'-'+elt.id].clearQueue();
							},
							filters: [{
						        name: 'filesize',
						        // A user-defined filter
						        fn: function(item) {
									if (item.size>maxSize) window.alert("Fichier trop volumineux");
									return item.size<=maxSize;
						        }
						    }]
						});
					}
				}
			});
		});
		//console.log('checkAll end',Data.modele[$scope.key][$scope.currenthash]);
		if (Object.keys(Data.modele[$scope.key][$scope.currenthash].collection).length>nb) {
			console.log('Let\'s save');
			$scope.save($scope.currenthash);
		}
		else console.log('save not needed');
		$scope.checkOk[$scope.currenthash]=true;
	};
	$scope.$on('modele-update-'+$scope.formkey,function(){
		if (Data.modele[$scope.key]) $scope.checkAll();
	});
	$scope.delFormInstance=function(hash){
		var tab=Object.keys(Data.modele[$scope.key]);
		var idx=tab.indexOf(hash);
		tab.splice(idx,1);
		Link.ajax([{action:'delFormInstance', params:{hash:hash}}],function(){
			if (tab.length>0) $location.path("form/"+tab[0]);
			else window.location("/form/");
		});
	}
	$scope.delFile=function(hash,id_elt,f){
		Link.ajax([{action:'delFormFile', params:{hash:hash,id_elt:id_elt,file:f.nom}}]);
	};
	$scope.save=function(hash){
		Link.ajax([{action:'modFormInstance',params:{instance:Data.modele[$scope.key][hash]}}]);
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
	$scope.testValid=function(hash){
		if (!$scope.isValid[hash]) $scope.isValid[hash]= {};
		if (Data.modele[$scope.key][hash] && $scope.checkOk[hash]) {
			angular.forEach(Data.modele[$scope.formkey].schema.pages,function(p){
				angular.forEach(p.elts,function(elt){
					if (elt.type=='texte_long' && elt.maxLength) {
						var strippedString = Data.modele[$scope.key][hash].collection[elt.id].valeur.replace(/(<([^>]+)>)/ig,"");
						var tab=strippedString.split(' ');
						var t=tab.length<=elt.maxLength;
						var o="Texte trop long ("+tab.length+" mots pour "+elt.maxLength+" autorisés)";
						if (!t){
							if($scope.isValid[hash][elt.id]!=o) $scope.isValid[hash][elt.id]=o;
						} else delete($scope.isValid[hash][elt.id]);
					}
				});
			});
		}
	};
	$scope.done={};
	$scope.testDone=function(hash){
		if (!$scope.done[hash]) $scope.done[hash]= {};
		if (Data.modele[$scope.key][hash] && $scope.checkOk[hash]) {
			angular.forEach(Data.modele[$scope.formkey].schema.pages,function(p){
				angular.forEach(p.elts,function(elt){
					if (elt.mandatory && elt.mandatory=='1' && (!elt.condition || Data.modele[$scope.key][hash].collection[elt.condition.id].valeur.split(',').indexOf(elt.condition.valeur)>=0)) {
						if (elt.type=='texte_court' || elt.type=='texte_long' || elt.type=='date' || elt.type=='multiples' || elt.type=='upload') {
							if(Data.modele[$scope.key][hash].collection[elt.id].valeur && Data.modele[$scope.key][hash].collection[elt.id].valeur.length>0) $scope.done[hash][elt.id]=true;
							else $scope.done[hash][elt.id]=false;
						} else {
							$scope.done[hash][elt.id]=true;
						}
					} else {
						$scope.done[hash][elt.id]=true;
					}
				});
			});
		}
	};
	$scope.setMultiple=function(p,choix){
		var tab=p.collection[p.elt.id].valeur.split(',');
		var i=tab.indexOf(choix.valeur);
		if (tab[0]=='') tab.splice(0,1);
		//console.log(p.collection[p.elt.id].valeur,tab,p.elt.nbMax);
		if (i<0) {
			if (tab.length<p.elt.nbMax) tab.push(choix.valeur);
			else {
				tab.splice(tab.length-1,1);
				tab.push(choix.valeur);
			}
		}
		else tab.splice(i,1);
		var sortedTab=[];
		for(var j=0;j<p.elt.choix.length;j++){
			if(tab.indexOf(p.elt.choix[j].valeur)>=0) sortedTab.push(p.elt.choix[j].valeur);
		}
		p.collection[p.elt.id].valeur=sortedTab.join();
	}
	$scope.canSave=function(hash){
		$scope.testValid(hash);
		//console.log(Object.keys($scope.isValid).length,$scope.dirty($scope.key));
		return Object.keys($scope.isValid[hash]).length==0 && $scope.dirtyKey($scope.key,hash);
	};
	$scope.canValidate=function(hash){
		$scope.testDone(hash);
		test=true;
		angular.forEach($scope.done[hash],function(e){
			test=test && e;
		});
		//console.log(Object.keys($scope.isValid).length,$scope.dirty($scope.key));
		return test && Object.keys($scope.isValid[hash]).length==0;
	};
	$scope.validate=function(hash){
		Link.ajax([{action:'modFormInstance',params:{instance:Data.modele[$scope.key][hash]}}],function(r){
			Link.ajax([{action:'closeFormInstance', params:{hash:hash}}]);
		});
	};
	$scope.addInstance=function(){
		console.log('addInstance');
		Link.ajax([{action:'addFormInstanceCas', params:{id_form:$scope.idform, id_cas:$scope.idcas}}],function(r){
			$location.path('/form/'+r.res[0]);
		});
	};
	$scope.contexts=[{type:$scope.formkey},{type:'form_instances_cas_form/'+$scope.idcas+"/"+$scope.idform}];
	angular.forEach($scope.autresinstances,function(hash){
		$scope.contexts.push({type:'form_instance/'+hash});
	});
	Link.context($scope.contexts);
}]);
