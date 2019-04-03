var app= angular.module('contacts', ['ngRoute','ngDragDrop','ui.bootstrap', 'toggle-switch', 'angularFileUpload','ngSanitize','cfp.hotkeys','ng.ckeditor','fakeWs','luegg.directives','ngTouch','ngAudio']);

app.config(['$routeProvider', function($routeProvider) {
	angular.lowercase = angular.$$lowercase;
	$routeProvider.when('/login', {templateUrl: 'partials/login.html', controller: 'loginCtl'});
	//casquettes
	$routeProvider.when('/contacts', {templateUrl: 'partials/contacts.html', controller: 'contactsCtl', hotkeys: [
		['s', 'Ajoute/Enleve le contact au panier', 'sel()'],
		['c', 'Vide la barre de recherche', 'clearQuery()'],
		['o', 'Selectionne le contact précédent', 'up()'],
		['l', 'Selectionne le contact suivant', 'down()'],
		['k', 'Page précédente', 'prev()'],
		['m', 'Page suivante', 'next()']
	]});
	$routeProvider.when('/modcontact/:id', {templateUrl: 'partials/modcontact.html', controller: 'modcontactCtl'});
	$routeProvider.when('/doublons_texte/', {templateUrl: 'partials/doublons_texte.html', controller: 'doublonsTexteCtl'});
	$routeProvider.when('/doublons_email/', {templateUrl: 'partials/doublons_email.html', controller: 'doublonsEmailCtl'});
	$routeProvider.when('/carte', {templateUrl: 'partials/carte.html', controller: 'carteCtl'});
	//mailing
	$routeProvider.when('/modmail/:id', {templateUrl: 'partials/modmail.html', controller: 'modmailCtl'});
	$routeProvider.when('/mail/:id', {templateUrl: 'partials/mail.html', controller: 'mailCtl'});
	$routeProvider.when('/mails', {templateUrl: 'partials/mails.html', controller: 'mailsCtl'});
	$routeProvider.when('/modnews/:id', {templateUrl: 'partials/modnews.html', controller: 'modnewsCtl'});
	$routeProvider.when('/news', {templateUrl: 'partials/news.html', controller: 'newsCtl'});
	$routeProvider.when('/modmodele/:id', {templateUrl: 'partials/modmodele.html', controller: 'modmodeleCtl'});
	$routeProvider.when('/envois', {templateUrl: 'partials/envois.html', controller: 'envoisCtl'});
	$routeProvider.when('/modenvoi/:id', {templateUrl: 'partials/modenvoi.html', controller: 'modenvoiCtl'});
	//publipostage
	$routeProvider.when('/modsupport/:id', {templateUrl: 'partials/modsupport.html', controller: 'modsupportCtl'});
	$routeProvider.when('/supports', {templateUrl: 'partials/supports.html', controller: 'supportsCtl'});
	$routeProvider.when('/modtemplate/:id', {templateUrl: 'partials/modtemplate.html', controller: 'modtemplateCtl'});
	$routeProvider.when('/templates', {templateUrl: 'partials/templates.html', controller: 'templatesCtl'});
	//suivis
	$routeProvider.when('/modsuivi/:id', {templateUrl: 'partials/modsuivi.html', controller: 'modsuiviCtl'});
	$routeProvider.when('/addsuivi/:id', {templateUrl: 'partials/addsuivi.html', controller: 'addsuiviCtl'});
	$routeProvider.when('/suivis', {templateUrl: 'partials/suivis.html', controller: 'suivisCtl'});
	//admin
	$routeProvider.when('/moduser/:id', {templateUrl: 'partials/moduser.html', controller: 'modUserCtl'});
	$routeProvider.when('/adduser', {templateUrl: 'partials/adduser.html', controller: 'addUserCtl'});
	$routeProvider.when('/modgroup/:id', {templateUrl: 'partials/modgroup.html', controller: 'modGroupCtl'});
	$routeProvider.when('/addgroup', {templateUrl: 'partials/addgroup.html', controller: 'addGroupCtl'});
	$routeProvider.when('/admin', {templateUrl: 'partials/admin.html', controller: 'adminCtl'});
	$routeProvider.when('/moi', {templateUrl: 'partials/moduser.html', controller: 'moiCtl'});
	$routeProvider.otherwise({redirectTo: '/contacts'});
}]);
app.config(['$locationProvider', function($locationProvider) {
	$locationProvider.html5Mode(true);
}]);
app.run(['$rootScope', '$uibModalStack',
    function ($rootScope,  $uibModalStack) {
        // close the opened modal on location change.
        $rootScope.$on('$locationChangeStart', function ($event) {
            $uibModalStack.dismissAll();
        });
    }]);
app.controller('mainCtl', ['$scope', '$http', '$location', '$timeout', '$interval', '$uibModal', '$q', '$window', '$sce', 'Link', 'Data', 'ngAudio', function ($scope, $http, $location, $timeout, $interval, $uibModal, $q, $window, $sce, Link, Data, ngAudio) {
	Data.mainQuery='';
	Data.suivisGroup=0;
	$scope.help=function(id){
		$uibModal.open({
			templateUrl: 'partials/inc/help_'+id+'.html'
		});
	};
	$scope.localeSensitiveComparator = function(v1, v2) {
		// If we don't get strings, just compare by index
		if (v1.type !== 'string' || v2.type !== 'string') {
			return (v1.index < v2.index) ? -1 : 1;
		}
		// Compare strings alphabetically, taking locale into account
		return v1.value.localeCompare(v2.value);
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
	$scope.map={}
	$scope.map.ok=false;
	$scope.map.show=false;
	$scope.map.rendered=false;
	$scope.map.center={lng:"0",lat:"0"};
	$scope.map.bounds={x0:'-180',x1:'180',y0:'-90',y1:'90'};
	$scope.map.zoom="1.2";
	$scope.map.clusterIds='';
	$scope.map.sources={'clusters':
		{
		type: 'geojson',
		data:{type:'FeatureCollection',features:[]}
		}};
	$scope.map.layers = [
		{
			id: 'cluster',
			type: 'circle',
			source: 'clusters',
			filter: ['has', 'nb'],
           		paint: {
				"circle-color": [
					"step",
					["get", "nb"],
					"#ffffff",
					2,
					"#54c3ff",
					100,
					"#00a0f0",
					750,
					"#0094d8"
				],
				"circle-radius": [
					"step",
					["get", "nb"],
					10,
					2,
					15,
					100,
					20,
					200,
					30,
					750,
					40
				]
			}
		},{
			id: 'cluster-count',
			type: 'symbol',
			source: 'clusters',
			filter: ['has', 'nb'],
			layout: {
				'text-field': '{nb}',
				'text-size': 12
			}
		}
	];
	$scope.sound = ngAudio.load("img/sonar.mp3");
	$scope.query_history={c:-1,tab:[]};
	$scope.Data=Data;
	$scope.params='test';
	$scope.done=false;
	$scope.brand='';
	$scope.tagsOpen=[];
	$scope.panier=[];
	$scope.scroll=0;
	$scope.filtre={suivis:0,newsletters:{id_newsletter:'!!'},tags:{}};
	$scope.total={};
	$scope.total.casquettes=0;
	$scope.selected={index:0};
	$scope.ts={};
	$scope.tt={};
	$scope.tabs={};
	$scope.tabs.admin={};
	$scope.tabs.news={};
	$scope.pageCourante={};
	$scope.pageCourante.contacts=1;
	$scope.pageCourante.cluster=1;
	$scope.pageCourante.envois=1;
	$scope.pageCourante.erreur=1;
	$scope.pageCourante.impacts=1;
	$scope.pageCourante.tags={};
	$scope.pageCourante.news=1;
	$scope.pageCourante.mails=1;
	$scope.afterLogin='/contacts';
	$scope.pageCourante.suivis={};
	$scope.pageCourante.suivis.prochains=1;
	$scope.pageCourante.suivis.retard=1;
	$scope.pageCourante.suivis.termines=1;
	$scope.initScroll=0;
	$scope.parser={};
	$scope.path=function(){return $location.path();}
	$scope.trust=function(html){
		return $sce.trustAsHtml(html);
	}
	$scope.logout=function(){
		Link.logout();
	};
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
	$scope.lastMod=function(cas){
		var cas_mod={date:cas.cas_modificationdate,by:cas.cas_modifiedby};
		var contact_mod={date:cas.modificationdate,by:cas.modifiedby};
		if (cas_mod.date>contact_mod.date) return cas_mod;
		else return contact_mod;
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
	$scope.children=function(tag){
		var res={};
		angular.forEach(Data.modele.tags, function(t){
			if (t.id_parent==tag.id){
				res[t.id]=t;
			}
		});
		return res;
	}
	$scope.descendants=function(tag){
		var tab=[];
		angular.forEach(Data.modele.tags,function(e){
			if (e.id_parent==tag.id) tab.push(e);
		});
		if (tab.length==0) return tab;
		else {
			angular.forEach(tab,function(e){
				tab=tab.concat($scope.descendants(e));
			});
		}
		return tab;
	}
	$scope.normaux=function(id_tag){
		var tab=[];
		angular.forEach(Data.modele.tags,function(e){
			if (id_tag==e.id_parent && !e.type) tab.push(e);
		});
		if (tab.length==0) return tab;
		else {
			angular.forEach(tab,function(e){
				tab=tab.concat($scope.normaux(e));
			});
		}
		return tab;
	}
	$scope.isAncestor=function(tag,ancestor){
		if (tag.id_parent==0 && tag.id!=ancestor.id) return false;
		else if (tag.id_parent==ancestor.id || tag.id==ancestor.id) return true;
		else return $scope.isAncestor(Data.modele.tags[tag.id_parent],ancestor);
	};
	$scope.hasListAncestor=function(tag){
		if (tag.id_parent==0) return false;
		else {
			var p=Data.modele.tags[tag.id_parent];
			if (p.type=='liste') return p;
			else return $scope.hasListAncestor(p);
		}
	};
	$scope.ancestorSpecial=function (tag) {
		if (tag.id_parent==0) return false;
		else {
			var p=Data.modele.tags[tag.id_parent];
			if (p.type) return true;
			else return $scope.ancestorSpecial(Data.modele.tags[p.id]);
		}
	};
	$scope.hasSpecial=function (cas) {
		for(var i=0;i<cas.tags.length;i++) {
			if (Data.modele.tags[cas.tags[i]].typeAncestor!='normal') return true;
		}
		return false;
	};
	$scope.moveok=function(tag){
		var res={};
		angular.forEach(Data.modele.tags, function(t){
			if(!$scope.isAncestor(t,tag)){
				res[t.id]=t;
			}
		});
		return res;
	}
	$scope.hasChild=function(tag){
		var res=false;
		angular.forEach(Data.modele.tags, function(t){
			if (t.id_parent==tag.id){
				res=true;
			}
		});
		return res;

	}
	$scope.byCasId=function(a,id){
		var res=false;
		angular.forEach(a,function(e){
			angular.forEach(e.casquettes,function(c){
				if(c.id==id) {
					res=e;
					return;
				}
			});
		});
		return res;
	};
	$scope.casById=function(id){
		var res=false;
		angular.forEach($scope.modele.casquettes,function(e){
			angular.forEach(e.casquettes,function(c){
				if(c.id==id) {
					res=c;
					return;
				}
			});
		});
		return res;
	};
	$scope.isEqual=function(a,b){
		return JSON.stringify(angular.copy(a), null, 4)==JSON.stringify(angular.copy(b), null, 4);
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
	$scope.updatePanier=function(){
		Link.ajax([{action:'modPanier', params:{panier:Data.modele.panier}}]);
	};
	$scope.addPanier=function(nouveaux){
		Link.ajax([{action:'addPanier', params:{nouveaux:nouveaux}}]);
	};
	$scope.delPanier=function(nouveaux){
		Link.ajax([{action:'delPanier', params:{nouveaux:nouveaux}}]);
	};
	$scope.panierAdd=function(cas,i){
		var nouveaux=[cas.id];
		$scope.addPanier(nouveaux);
		var i = (typeof i !== 'undefined') ? i : -1;
		if (i>=0) $scope.selected.index=i;
	};
	$scope.panierDel=function(cas,i){
		var nouveaux=[cas.id];
		$scope.delPanier(nouveaux);
		var i = (typeof i !== 'undefined') ? i : -1;
		if (i>=0) $scope.selected.index=i;
	};
	$scope.panierVide=function(){
		if (Data.user.id) {
			Data.modele.panier=[];
			$scope.updatePanier();
		}
	};
	$scope.dansPanier=function(cas){
		if (Data.user.id>0 && Data.modele.panier) {
			return Data.modele.panier.indexOf(cas.id)>=0;
		}
	};
	$scope.addContactMod=function(type){
		$scope.addContact={type:type};
		var modal = $uibModal.open({
			templateUrl: 'partials/addcontactmod.html',
			controller: 'addContactModCtl',
			resolve:{
				contact: function () {
					return $scope.addContact;
				}
			}
		});

		modal.result.then(function (contact) {
			Link.ajax([{action:'addContact', params:{contact:contact}}], function(r){$location.path('/modcontact/'+ r.res[0]);});
		});
	};
	$scope.stripAccents=function(str){
		if (str){
			return removeDiacritics(str);
		}
	};
	$scope.descTagRec=function(tag,id_parent){
		var h=[tag];
		if (tag.id_parent!=id_parent && tag.id_parent!=0){
			angular.forEach($scope.descTagRec(Data.modele.tags[tag.id_parent],id_parent), function(t){
				h.push(t);
			});
		}
		return h;
	};
	$scope.descTag=function(tag){
		var h=$scope.descTagRec(tag,0);
		return h.reverse();
	};
	$scope.descTagParent=function(tag,id_parent){
		var h=$scope.descTagRec(tag,id_parent);
		return h.reverse();
	};
	$scope.formatDescTag=function(t){
		var tab=[];
		angular.forEach(t,function(e){tab.push(e.nom);});
		return tab.join('>');
	};
	$scope.itemsParPage=10;
	$scope.maxSize = 5;
	$scope.selections={};
	$scope.courant={};
	$scope.parNomTag = function(tags) {
		var t = angular.copy(tags);
		if (t) {
			t.sort(function(a,b) {
				var tagA=Data.modele.tags[a];
				var tagB=Data.modele.tags[b];
				return $scope.formatDescTag($scope.descTagParent(tagA)).localeCompare($scope.formatDescTag($scope.descTagParent(tagB)));
			});
		}
		return t;
	};
	$scope.valeur = function(cas,type){
		var res=[];
		angular.forEach(cas.donnees,function(d){
			if (d.type==type) {
				res.push({valeur:d.value});
			}
		});
		return res;
	}
	$scope.csv=function(){
		var contexts=angular.copy(Data.contexts);
		var modal = $uibModal.open({
			templateUrl: 'partials/csv.html',
			controller: 'envoyerModCtl',
			resolve:{
				parsed: function () {
					return $scope.parsed;
				},
				type: function () {
					return 'tout';
				}
			}
		});
		modal.result.then(function (res) {
			var data={
				type:'export_csv',
				res:res
			};
			angular.element.redirect('doc.php',data,'POST','_blank');
			Link.context(contexts);
		},function(){Link.context(contexts);});
	}
	$scope.frontParser={};
	$http.get('js/pegjs.grammar').then(function(msg){
		$scope.frontParser=PEG.buildParser(msg.data);
	});
	$scope.backParser={};
	$http.get('js/pegjs2.grammar').then(function(msg){
		$scope.backParser=PEG.buildParser(msg.data);
	});
	$scope.parsed={
		front:function(query){
			var p='';
			if (query && $scope.frontParser.parse) {
				try { p=$scope.frontParser.parse(query); }
				catch (err) {
					p='Erreur de syntaxe';
				}
				if (p!='Erreur de syntaxe') {

					var tag = /::tag(\d+)::/;
					while (tab = tag.exec(p)) {
						if (Data.modele.tags[tab[1]]) {
							var tags=$scope.descTag(Data.modele.tags[tab[1]]);
							var tagHtml='<span class="tag">';
							for(var i=0;i<tags.length;i++){
								if (i==tags.length-1) tagHtml+='<span style="color:{{data.modele.tags['+tags[i].id+'].color}};">{{data.modele.tags['+tags[i].id+'].nom}}</span>';
								else tagHtml+='<span style="color:{{data.modele.tags['+tags[i].id+'].color}};">{{data.modele.tags['+tags[i].id+'].nom}}></span>';
							}
							tagHtml+='</span>';
							p=p.replace(tab[0],tagHtml);
						}
						else {
							p=p.replace(tab[0], "(le tag n'existe pas)");
						}
					}
					var dpt = /::dpt([AB0-9]+)::/;
					while (tab = dpt.exec(p)) {
						p=p.replace(tab[0],'<span class="tag" style="background-color:#CCC;color:#FFF;">'+departement(tab[1]).nom+'</span>');
					}
					var dpts = /::dpts\/([,AB0-9]+)::/;
					while (tab = dpts.exec(p)) {
						var cps=tab[1].split(',');
						var html='';
						for(var i=0;i<cps.length;i++){
							html+='<span class="tag" style="background-color:#CCC;color:#FFF;">'+departement(cps[i]).nom+'</span> ';
						}
						p=p.replace(tab[0],html);
					}
				}
			}
			return p;
		},
		back:function(query){
			var p='1';
			if (query && $scope.backParser.parse) {
				try { p=$scope.backParser.parse(query); }
				catch (err) {
					p=false;
				}
			}
			return p;
		}
	};
	//chat
	$scope.resizeChat=function(){
		if ($window.innerWidth<768) $scope.chatZoom=1;
		if ($window.innerWidth>=768 && $window.innerWidth<992) $scope.chatZoom=2;
		if ($window.innerWidth>=992 && $window.innerWidth<1170) $scope.chatZoom=3;
		if ($window.innerWidth>=1170) $scope.chatZoom=4;
	};
	angular.element($window).on('resize', $scope.resizeChat);
	$scope.newMessage={};
	$scope.chatVisible=false;
	$scope.chatZoom=4;
	$scope.toggleChat=function(p){
		if ($scope.chatVisible) {
			$scope.chatVisible=false;
			angular.element(document.getElementById('chat')).css('height',0);
		} else {
			$scope.chatVisible=true;
			angular.element(document.getElementById('chat')).css('height',p+'%');
		}
	};
	$scope.sendMessage=function(key){
		Link.ajax([{action:'sendMessage', params:{id_from:Data.user.id, id_to:key, message:$scope.newMessage[key]}}]);
		$scope.newMessage[key]='';
	}
	$scope.channels=[];
	$scope.nonlus=0;
	$scope.majChat=function(o,n){
		if (!$scope.isEqual(o,n)) {
			var channels=[];
			var groups=[];
			var uids=[];
			angular.forEach(Data.modele.groups,function(g){
				if (g.users.indexOf(Data.user.id)>=0) {
					channels.push({id:-g.id,name:g.nom});
					groups.push(g.id);
				}
			});
			angular.forEach(groups,function(gid){
				angular.forEach(Data.modele.groups[gid].users,function(uid){
					if (uid!=Data.user.id && uids.indexOf(uid)<0) {
						channels.push(Data.modele.users[uid]);
						uids.push(uid);
					}
				});
			});
			$scope.channels=channels;
		}
	};
	$scope.$watchCollection('Data.modele.logged',$scope.majChat);
	$scope.$watchCollection('Data.modele.groups',$scope.majChat);
	$scope.$watchCollection(function(){if (Data.user.id && Data.user.id>0) return Data.modele.chat},function(o,n){
		if (Data.user.id>0 && !$scope.isEqual(o,n)) {
			var nonlus=0;
			angular.forEach(Data.modele.chat.collection,function(l,id_corresp){
				angular.forEach(l,function(m){
					if (!Data.modele.chat.lus[id_corresp] || m.creationdate>Data.modele.chat.lus[id_corresp]) {
			nonlus++;
			$scope.sound.play();
			}
				});
			});
			$scope.nonlus=nonlus;
			$scope.resizeChat();
		}
	});
	$scope.chatNav=0;
	$scope.chatNavSuiv=function(){if ($scope.chatNav<$scope.channels.length-1) $scope.chatNav++};
	$scope.chatNavPrec=function(){if ($scope.chatNav>0) $scope.chatNav--};
	$scope.setlus=function(id){
		if ($scope.nonlus>0) Link.ajax([{action:'setLus',params:{id_user:Data.user.id, id_corresp:id}}]);
	};
	$scope.modMessageMod=function(m){
		var modal = $uibModal.open({
			templateUrl: 'partials/modmessagemod.html',
			controller: 'modMessageModCtl',
			resolve:{
				message: function () {
					return m;
				}
			}
		});

		modal.result.then(function (m) {
			Link.ajax([{action:'modMessage', params:{message:m}}]);
		});
	};
	$scope.addNbContactsMod=function(){
		var modal = $uibModal.open({
			templateUrl: 'partials/addnbcontactsmod.html',
			controller: 'addNbContactsModCtl',
		});
		modal.result.then(function (res) {
			Link.ajax([{action:'addNbContacts',params:{tags:res.tags, contacts:res.contacts}}]);
		});
	}
	$scope.addNbCsvMod=function(){
		var modal = $uibModal.open({
			templateUrl: 'partials/addnbcsvmod.html',
			controller: 'addNbCsvModCtl'
		});
		modal.result.then(function (res) {
			Link.ajax([{action:'addNbCsv',params:{tags:res.tags, hash:res.hash, map:res.map}}]);
		});
	}
}]);
app.controller('accueilCtl', ['$scope', '$http', '$location', function ($scope, $http, $location) {
}]);
app.controller('carteCtl', ['$scope', '$http', '$location', 'Link', 'Data', function ($scope, $http, $location, Link, Data) {
	$scope.Data=Data;
	$scope.itemsParPage=5;
	$scope.update=function(map){
		var center=map.getCenter();
		var zoom=map.getZoom();
		var bounds=map.getBounds();
		$scope.map.center={lng:''+center.lng,lat:''+center.lat};
		$scope.map.zoom=''+zoom;
		$scope.map.bounds={x0:''+bounds._sw.lng,x1:''+bounds._ne.lng,y0:''+bounds._sw.lat,y1:''+bounds._ne.lat};
		//console.log('update',$scope.map.center,$scope.map.zoom);
		$scope.getPage()
	}
	$scope.init=function(map){
		$scope.map.rendered=true;
		//console.log('init',$scope.map.center,$scope.map.zoom);
		map.setZoom($scope.map.zoom*1.0);
		map.setCenter([$scope.map.center.lng*1.0,$scope.map.center.lat*1.0]);
	}
	$scope.getCluster=function(m){
		$scope.map.clusterIds=m.properties.ids;
		$scope.pageCourante.cluster=1;
		$scope.getPage()
	}
	$scope.$watchCollection('Data.modele.carte.geojson_clusters',function(o,n){
		if (Data.modele.carte) $scope.map.sources={'clusters':
		{
		type: 'geojson',
		data: Data.modele.carte.geojson_clusters
		}};
	});
	$scope.$watch('pageCourante.cluster',function(o,n){
		if (o!=n) $scope.getPage();
	});
	$scope.$on('mapboxglMap:featureClick',function(e,f){
		//console.log(f);
		$scope.getCluster(f);
	});
	$scope.$on('mapboxglMap:moveend',function(e,t){
		//console.log('moveend',t.target,$scope.map.rendered);
		if ($scope.map.rendered) {
			$scope.update(t.target);
		}
	});
	$scope.$on('mapboxglMap:ready',function(e,t){
		//console.log('load');
		if (!$scope.map.rendered) {
			$scope.init(t.target);

		}
	});
	$scope.getPage=function(){
		Link.context([{type:'carte',params:{query:$scope.parsed.back(Data.mainQuery),center:$scope.map.center,zoom:$scope.map.zoom,bounds:$scope.map.bounds}},{type:'cluster',params:{ids:$scope.map.clusterIds,page:$scope.pageCourante.cluster,nb:$scope.itemsParPage}},{type:'tags'},{type:'panier'}]);
	}
	$scope.$watch('Data.modele.config.config.carte.mapbox_accessToken.value',function(n,o){
		if (n && !$scope.map.ok) {
			mapboxgl.accessToken = n;
			$scope.map.ok=true;
		}
	});
	$scope.map.show=true;
	$scope.$on("$destroy", function(){
		$scope.map.show=false;
	});
	if ($scope.map.rendered) $scope.getPage();
	else Link.context([{type:'tags'},{type:'panier'}]);
}]);
app.controller('loginCtl', ['$scope', '$http', '$location', 'Link', 'Data', function ($scope, $http, $location, Link, Data) {
	if (Data.user.id==-1) {
		$scope.Data=Data;
		$scope.msgtxt='';
		$scope.login=function(){
			var data=[{
				action:'login',
				params: {
					login:$scope.user.login,
					password:$scope.user.password,
					uid:Data.uid,
				}
			}];
			Link.ajax(data);
		};
	} else {
		$location.path('/contacts');
	}
}]);



//casquettes
app.controller('contactsCtl', ['$scope', '$http', '$location', '$timeout', '$interval', '$window', '$uibModal','Link', 'Data', function ($scope, $http, $location, $timeout, $interval, $window, $uibModal, Link, Data) {
	$scope.Data=Data;
	$scope.panierKey='panier';
	$scope.itemsParPage=10;
	$scope.itemsParPageTag=20;
	$scope.typeahead={};
	$scope.containsCurrentWord = function(tags, viewValue) {
		var words = viewValue.split(/([&|^!()]+)/);
		$scope.typeahead.words=words;
		var cursor=document.getElementById("mainInput").selectionStart;
		var currentWord = '';
		var pos=0;
		for (var i = 0; i < words.length; i++) {
			pos=pos+words[i].length;
			if (pos>=cursor) {
				if (words[i].match(/([&|^!()]+)/)) $scope.typeahead.wordIndex=i+1;
				else $scope.typeahead.wordIndex=i;
				currentWord=words[$scope.typeahead.wordIndex];
				break;
			}
		}
		//console.log(cursor,currentWord,words);
		res=[];
		angular.forEach(tags,function(tag){
			if (removeDiacritics(tag.nom.toLowerCase()).indexOf(removeDiacritics(currentWord.toLowerCase()))>=0) res.push(tag);
		});
		return res;
	}
	$scope.typeaheadOnSelect = function(tag) {
		$scope.typeahead.words[$scope.typeahead.wordIndex] = ':tag'+tag.id;
		$scope.Data.mainQuery = $scope.typeahead.words.join('');
		$timeout(function(){
			var pos=0;
			for (var i = 0; i <=$scope.typeahead.wordIndex; i++) {
				pos=pos+$scope.typeahead.words[i].length;
			}
			document.getElementById("mainInput").setSelectionRange(pos, pos);
		},200);
	}
	$scope.modCas=function(cas){
		if (!Data.modele['contact/'+cas.id_contact]) {
			var casquettes={};
			casquettes[cas.id]={id:cas.id}
			Data.modele.casquettes.collection.forEach(function(e){
				if (e.id_contact==cas.id_contact) casquettes[e.id]=e;
			});
			var c={
				id:cas.id_contact,
				nom:cas.nom,
				prenom:cas.prenom,
				casquettes:casquettes,
				type:cas.type,
				creationdate:cas.creationdate,
				createdby:cas.createdby,
				modificationdate:cas.modificationdate,
				modifiedby:cas.modifiedby
			};
			Data.modele['contact/'+cas.id_contact]=c;
			Data.modeleSrv['contact/'+cas.id_contact]=c;
		}
		$location.path('/modcontact/'+ cas.id_contact);
	}
	$scope.modCasEtab=function(cas){
		if (!Data.modele['contact/'+cas.id_contact_etab]) {
			var casquettes={};
			casquettes[cas.id_etab]={id:cas.id_etab}
			Data.modele.casquettes.collection.forEach(function(e){
				if (e.id_contact==cas.id_contact_etab) casquettes[e.id]=e;
			});
			var c={
				id:cas.id_contact_etab,
				nom:cas.nom_etab,
				casquettes:casquettes,
				type:2
			};
			Data.modele['contact/'+cas.id_contact_etab]=c;
			Data.modeleSrv['contact/'+cas.id_contact_etab]=c;
		}
		$location.path('/modcontact/'+ cas.id_contact_etab);
	}
	$scope.historyPrev=function(){
		var l=$scope.query_history.tab.length;
		if (l>0) {
			$scope.query_history.c=Math.min($scope.query_history.c+1,l-1);
			while($scope.query_history.c<l-1 && $scope.query_history.tab[l-1-$scope.query_history.c]==Data.mainQuery){
				$scope.query_history.c=Math.min($scope.query_history.c+1,l-1);
			}
			Data.mainQuery=$scope.query_history.tab[l-1-$scope.query_history.c];
		}
	};
	$scope.historyNext=function(){
		if ($scope.query_history.c==0) {
			Data.mainQuery='';
			return;
		}
		var l=$scope.query_history.tab.length;
		if (l>0) {
			$scope.query_history.c=Math.max($scope.query_history.c-1,0);
			while($scope.query_history.c>0 && $scope.query_history.tab[l-1-$scope.query_history.c]==Data.mainQuery){
				$scope.query_history.c=Math.max($scope.query_history.c-1,0);
			}
			Data.mainQuery=$scope.query_history.tab[l-1-$scope.query_history.c];
		}
	};
	$scope.history=function(e){
		if (e.ctrlKey && e.keyCode==38) $scope.historyPrev();
		if (e.ctrlKey && e.keyCode==40) $scope.historyNext();
		if (e.keyCode==13) $scope.getPage(1);
	};
	$scope.normalizedNom = function(tag) {
		return tag.nom ? removeDiacritics(tag.nom) : '';
	};
	$scope.panierAll=function(){
		Link.ajax([{action:'panierAll', params:{query:$scope.parsed.back(Data.mainQuery)}}])
	};
	$scope.clearQuery=function(){
		Data.mainQuery='';
		$scope.getPage(1);
	};
	$scope.insert=function(channel,data,ctrl){
		var txt='';
		var c=false;
		var s=false;
		if ($scope.dragging.c=='c') c=true;
		if ($scope.dragging.s=='s') s=true;
		if (channel=='tag') {
			txt=':tag'+data.id;
		}
		if (channel=='sel') {
			txt=data.query;
		}
		if (channel=='panier') {
			txt=':panier';
		}
		if (channel=='sel' && (Data.mainQuery!='' || s)) txt='('+txt+')';
		if (s) txt='!'+txt;
		if(c) {
		if (Data.mainQuery!='') {
				txt= '&' + txt;
				Data.mainQuery= '(' + Data.mainQuery + ')' + txt;
			} else {
				txt= '|' + txt;
				Data.mainQuery= Data.mainQuery + txt;
			}
		} else {
			Data.mainQuery= Data.mainQuery + txt;
		}
		if (channel=='etab') {
			Data.mainQuery=':etab'+data.id;
		}
		$scope.getPage(1);
	};
	$scope.up=function(){
		$scope.selected.index--;
		if ($scope.selected.index<0) {
			if (Data.modele.casquettes.page>1) {
				$scope.prev();
				$scope.getPage();
				$scope.selected.index=9;
			}
		}
	};
	$scope.down=function(){
		$scope.selected.index++;
		if ($scope.selected.index>=$scope.itemsParPage) {
			if (Data.modele.casquettes.page<1+Data.modele.casquettes.total/$scope.itemsParPage) {
				$scope.next();
				$scope.getPage();
			}
		}
	};
	$scope.ajustScroll=function(){
		$timeout(function(){
			angular.forEach(angular.element(document.getElementById('contacts-list')).children(),function(e){
				var elt=angular.element(e);
				if (elt.hasClass('courant')) {
					var M=document.getElementById('main-container').offsetTop;
					var et=M+e.offsetTop;
					var eb=et+e.clientHeight;
					var WT=document.body.scrollTop;
					var WB=WT+$window.innerHeight;
					if (WT>et-20) WT=eb-$window.innerHeight+20;
					if (WB<eb+20) WT=et-20;
					var WB=WT+$window.innerHeight;
					document.body.scrollTop=WT;
				}
			});
		},200);
	};
	$scope.$watch('selected.index',function(){
		if (Data.modele.casquettes && Data.modele.casquettes.collection[$scope.selected.index]) {
			$scope.courant.id=Data.modele.casquettes.collection[$scope.selected.index].id;
			$scope.ajustScroll();
		}
	});
	$scope.$watch('Data.mainQuery',debounce(function(n,o){
		if (n!=o) {
			$scope.getPage(1);
		}
	},500));
	$scope.$watch('Data.modele.casquettes',function(n,o){
		if (n!=o && Data.modele.casquettes && Data.modele.casquettes.collection[$scope.selected.index]) {
			if ($scope.selected.index<0) $scope.selected.index=$scope.itemsParPage-1;
			if ($scope.selected.index>$scope.itemsParPage-1) $scope.selected.index=0;
			$scope.courant.id=Data.modele.casquettes.collection[$scope.selected.index].id;
			$scope.ajustScroll();
		}
	});
	$scope.$watch('pageCourante.contacts',function(n,o){
		if (n!=o && Data.modele.casquettes && $scope.pageCourante.contacts>0 && $scope.pageCourante.contacts<1+(Data.modele.casquettes.total/$scope.itemsParPage)) {
			$scope.getPage();
			if (n<o && $scope.selected.index==$scope.itemsParPage-1) $scope.selected.index=$scope.itemsParPage-1;
			if (n<o && $scope.selected.index!=$scope.itemsParPage-1) $scope.selected.index=0;
			if (n>o) $scope.selected.index=0;
		}
	});
	$scope.getPage=function(init){
		var page;
		var query=$scope.parsed.back(Data.mainQuery);
		if (query) {
			var l=$scope.query_history.tab.length;
			if (Data.mainQuery!='' && $scope.query_history.tab[l-1-$scope.query_history.c]!=Data.mainQuery) {
				$scope.query_history.tab.splice(l-$scope.query_history.c,0,Data.mainQuery);
			}
			if (Data.mainQuery=='') {
				$scope.query_history.c=-1;
			}
			if (init) {
				$scope.pageCourante.contacts=1;
				$scope.selected.index=0;
				page=1;
			}
			else page=$scope.pageCourante.contacts;
			Link.context([{type:'casquettes', params:{page:page, nb:$scope.itemsParPage, query:query}},{type:'tags'},{type:'selections'},{type:'panier'}]);
		}
	};
	$scope.delContact=function(cas){
		Link.ajax([{action:'delContact', params:{cas:cas}}]);
	};
	$scope.delCasquettesPanier=function(cas){
		Link.ajax([{action:'delCasquettesPanier', params:{}}]);
	};
	$scope.unErrorEmailPanier=function(cas){
		Link.ajax([{action:'unErrorEmailPanier', params:{}}]);
	};
	var tagsScroll=undefined;
	$scope.dragging={active:false,c:'nc',s:'ns'};
	$scope.dragText={
		tag:{
			tag:{
				v:{
					c:{
						s:'déplacer la catégorie',
						ns:'déplacer la catégorie'
					},
					nc:{
						s:'déplacer la catégorie',
						ns:'déplacer la catégorie'
					}
				},
				nv:{
					c:{
						s:'déplacer la catégorie',
						ns:'déplacer la catégorie'
					},
					nc:{
						s:'déplacer la catégorie',
						ns:'déplacer la catégorie'
					}
				}
			},
			query:{
				v:{
					c:{
						s:'contacts qui ne sont pas dans la catégorie',
						ns:'contacts qui sont dans la catégorie'
					},
					nc:{
						s:'contacts qui ne sont pas dans la catégorie',
						ns:'contacts qui sont dans la catégorie'
					}
				},
				nv:{
					c:{
						s:'contacts, parmi les resultats, qui ne sont pas dans la catégorie',
						ns:'contacts, parmi les resultats, qui sont dans la catégorie'
					},
					nc:{
						s:'ajouter aux résultats les contacts qui ne sont pas dans la catégorie',
						ns:'ajouter aux résultats les contacts de la catégorie'
					}
				}
			},
			contact:{
				v:{
					c:{
						s:'ajouter à la catégorie',
						ns:'ajouter à la catégorie'
					},
					nc:{
						s:'ajouter à la catégorie',
						ns:'ajouter à la catégorie'
					}
				},
				nv:{
					c:{
						s:'ajouter à la catégorie',
						ns:'ajouter à la catégorie'
					},
					nc:{
						s:'ajouter à la catégorie',
						ns:'ajouter à la catégorie'
					}
				}
			}
		},
		panier:{
			tag:{
				v:{
					c:{
						s:'enlever de la catégorie',
						ns:'ajouter à la catégorie'
					},
					nc:{
						s:'enlever de la catégorie',
						ns:'ajouter à la catégorie'
					}
				},
				nv:{
					c:{
						s:'enlever de la catégorie',
						ns:'ajouter à la catégorie'
					},
					nc:{
						s:'enlever de la catégorie',
						ns:'ajouter à la catégorie'
					}
				}
			},
			query:{
				v:{
					c:{
						s:'contacts qui ne sont pas dans le panier',
						ns:'contacts qui sont dans le panier'
					},
					nc:{
						s:'contacts qui ne sont pas dans le panier',
						ns:'contacts qui sont dans le panier'
					}
				},
				nv:{
					c:{
						s:'contacts, parmi les resultats, qui ne sont pas dans le panier',
						ns:'contacts, parmi les resultats, qui sont dans le panier'
					},
					nc:{
						s:'ajouter aux résultats les contacts qui ne sont pas dans le panier',
						ns:'ajouter aux résultats les contacts du panier'
					}
				}
			}
		},
		sel:{
			query:{
				v:{
					c:{
						s:'contacts qui ne sont pas dans la selection',
						ns:'contacts qui sont dans la selection'
					},
					nc:{
						s:'contacts qui ne sont pas dans la selection',
						ns:'contacts qui sont dans la selection'
					}
				},
				nv:{
					c:{
						s:'contacts, parmi les resultats, qui ne sont pas dans la selection',
						ns:'contacts, parmi les resultats, qui sont dans la selection'
					},
					nc:{
						s:'ajouter aux résultats les contacts qui ne sont pas dans la selection',
						ns:'ajouter aux résultats les contacts de la selection'
					}
				}
			}
		}

	};
	$scope.$on('ANGULAR_HOVER_STOP',function(d,el,e,c){
		$scope.dragging.active=false;
	});
	$scope.$on('ANGULAR_DRAG_END',function(d,el,e,c){
		$scope.dragging.active=false;
	});
	$scope.$on('ANGULAR_HOVER',function(d,el,e,c){
		$scope.dragging.active=true;
		$scope.dragging.drop=angular.element(el).attr('data-drop-type');
		$scope.dragging.channel= c;
		$scope.dragging.c= e.ctrlKey ? 'c': 'nc';
		$scope.dragging.s= e.shiftKey ? 's': 'ns';
		var helper = document.getElementById('drag-helper');
		helper.style.left=(30+e.pageX)+"px";
		helper.style.top=(15+e.pageY)+"px";
	});
	$scope.next=function(){
		if ($scope.pageCourante.contacts<=Data.modele.casquettes.total/$scope.itemsParPage) {
			$scope.pageCourante.contacts++;
		}
	};
	$scope.prev=function(){
		if ($scope.pageCourante.contacts>1) {
			$scope.pageCourante.contacts--;
		}
	};
	$scope.sel=function(){
		var cas=$scope.courant;
		if (cas.id>0) {
			Data.modele.panier.indexOf(cas.id)>=0 ? $scope.panierDel(cas) : $scope.panierAdd(cas);
		}
	};
	$scope.dropOnTag = function(e,data,channel,tag,ctrl) {
		if (channel=='tag'){
			if ($scope.pageCourante.tags[data.id_parent]-1*$scope.itemsParPage==$scope.ts[data.id_parent].length-1) $scope.pageCourante.tags[data.id]--;
			$scope.movTag(data, tag);
		}
		if (channel=='panier'){
			if ($scope.dragging.s=='s') Link.ajax([{action:'delPanierTag', params:{tag:tag}}]);
			else Link.ajax([{action:'addPanierTag', params:{tag:tag}}]);
		}
	};
	$scope.movTag=function(tag,parent){
		if (!$scope.isAncestor(parent,tag)) Link.ajax([{action:'movTag', params:{tag:tag, parent:parent}}]);
	};
	$scope.addCasTag = function(e,tag,cas) {
		var idx=-1;
		angular.forEach(Data.modele.casquettes.collection,function(e,i){
			if (e.id==cas.id) idx=i;
		});
		if (idx>=0) {
			var p=Data.modele.casquettes.collection[idx].tags.indexOf(tag.id);
			if (p<0) {
				Data.modele.casquettes.collection[idx].tags.push(tag.id);
			}
		}
		Link.ajax([{action:'addCasTag', params:{cas:{id:cas.id,id_contact:cas.id_contact}, tag:{id:tag.id}}}]);
	};
	$scope.delCasTag = function(tag,cas) {
		var idx=-1;
		angular.forEach(Data.modele.casquettes.collection,function(e,i){
			if (e.id==cas.id) idx=i;
		});
		if (idx>=0) {
			var p=Data.modele.casquettes.collection[idx].tags.indexOf(tag.id);
			if (p>=0) {
				Data.modele.casquettes.collection[idx].tags.splice(p,1);
			}
		}
		Link.ajax([{action:'delCasTag', params:{cas:cas, tag:tag}}]);
	};
	$scope.CasTagClick = function(e,tag,cas) {
		if (e.shiftKey || e.ctrlKey){
			if ($window.confirm('Supprimer de la catégorie ?')) {
				$scope.delCasTag(tag,cas);
			}
		} else {
			$window.alert('Tag n°'+tag.id+'\n(maj+clic ou ctrl+clic pour enlever.)');
		}
	};
	$scope.delTag = function(tag) {
		Link.ajax([{action:'delTag', params:{tag:tag}}]);
	};
	$scope.videTag = function(e,tag) {
		if (e.shiftKey || e.ctrlKey){
			if ($window.confirm('Vider la catégorie ? Cela va supprimer toutes les catégories enfants.')) {
				Link.ajax([{action:'videTag', params:{tag:tag}}]);
			}
		}
	};
	$scope.delSelection = function(sel) {
		Link.ajax([{action:'delSelection', params:{selection:sel}}]);
	};
	$scope.modTagMod=function(tag){
		var modTag=angular.copy(tag);
		Link.set_verrou(['tag/'+tag.id]);
		var modal = $uibModal.open({
			templateUrl: 'partials/modtagmod.html',
			controller: 'modTagModCtl',
			resolve:{
				moveok: function () {
					return $scope.moveok;
				},
				movTag: function () {
					return $scope.movTag;
				},
				descTag: function () {
					return $scope.descTag;
				},
				modTag: function () {
					return modTag;
				},
				bouton: function () {
					return 'Modifier';
				}
			}
		});
		modal.result.then(function (tag) {
			Link.ajax([{action:'modTag', params:{tag:tag}}, {action:'del_verrou', type:'tag/'+tag.id}]);
		}, function(){Link.del_verrou('tag/'+tag.id);});
	}
	$scope.addTagMod=function(){
		var modTag={};
		var modal = $uibModal.open({
			templateUrl: 'partials/modtagmod.html',
			controller: 'modTagModCtl',
			resolve:{
				moveok: function () {
					return $scope.moveok;
				},
				movTag: function () {
					return $scope.movTag;
				},
				descTag: function () {
					return $scope.descTag;
				},
				modTag: function () {
					return modTag;
				},
				bouton: function () {
					return 'Ajouter';
				}
			}
		});
		modal.result.then(function (tag) {
			Link.ajax([{action:'addTag', params:{tag:tag}}]);
		});
	}
	$scope.activeSelection=function(sel){
		Data.mainQuery=angular.copy(sel.query);
		$scope.getPage(1);
	};
	$scope.saveSelectionMod=function(){
		var modSel={query:Data.mainQuery};
		var modal = $uibModal.open({
			templateUrl: 'partials/modselmod.html',
			controller: 'modSelectionModCtl',
			resolve:{
				parsed: function () {
					return $scope.parsed;
				},
				modSel: function () {
					return modSel;
				},
				bouton: function () {
					return 'Ajouter';
				}
			}
		});
		modal.result.then(function (sel) {
			Link.ajax([{action:'addSelection',params:{selection:sel}}]);
		});
	}
	$scope.modSelectionMod=function(sel){
		var modSel=angular.copy(sel);
		Link.set_verrou(['selection/'+sel.id]);
		var modal = $uibModal.open({
			templateUrl: 'partials/modselmod.html',
			controller: 'modSelectionModCtl',
			resolve:{
				parsed: function () {
					return $scope.parsed;
				},
				modSel: function () {
					return modSel;
				},
				bouton: function () {
					return 'Modifier';
				}
			}
		});
		modal.result.then(function (sel) {
			Link.ajax([{action:'modSelection', params:{selection:sel}}, {action:'del_verrou', type:'selection/'+sel.id}]);
		}, function(){Link.del_verrou('selection/'+sel.id);});
	}
	$scope.getPage();
}]);
app.controller('modcontactCtl', ['$scope', '$filter', '$http', '$location', '$routeParams', '$uibModal', '$window', 'Link', 'Data', 'hotkeys', function ($scope, $filter, $http, $location, $routeParams, $uibModal, $window, Link, Data, hotkeys) {
	$scope.key='contact/'+$routeParams.id;
	$scope.prevNextKey='contact_prev_next/'+$routeParams.id;
	Link.context([{type:$scope.key},{type:'tags'},{type:'contact_prev_next/'+$routeParams.id,params:{query:$scope.parsed.back(Data.mainQuery)}}]);
	$scope.$watch('Data.modele["contact_prev_next/'+$routeParams.id+'"]',function(n,o){
		if (n) {
			Link.context([{type:$scope.key},{type:'tags'},{type:'contact_prev_next/'+$routeParams.id,params:{query:$scope.parsed.back(Data.mainQuery)}},
				{type:'contact/'+n[0]},
				{type:'contact/'+n[1]}
			]);
		}
	});
	$scope.sv={};
	$scope.ev={};
	$scope.svDesc={};
	$scope.idx=-1;
	$scope.modContact=function(contact){
		Data.modele[$scope.key].nom=contact.nom;
		Data.modele[$scope.key].prenom=contact.prenom;
		Link.ajax([{action:'modContact', params:{id:$routeParams.id, nom:contact.nom, prenom:contact.prenom}},
			{action:'del_verrou',type:$scope.key}]);
	}
	$scope.modFirstCas=function(){

	}
	hotkeys.bindTo($scope)
	.add({
		combo: 'w',
		description: 'Modifier la première casquette / le premier établissement',
		callback: function() {
			var idcas=0;
			angular.forEach(Data.modele[$scope.key].casquettes,function(e,i){
				if (idcas==0) idcas=i;
			});
			//console.log($scope.key);
			$scope.modCasquetteMod(Data.modele[$scope.key].casquettes[idcas]);
		}
	})
	.add({
		combo: 'x',
		description: 'Contact suivant',
		callback: function() {
			var next=Data.modele['contact_prev_next/'+$routeParams.id][1];
			if (next>0) $location.path('/modcontact/'+ next);
		}
	})
	.add({
		combo: 'q',
		description: 'Contact précédent',
		callback: function() {
			var prev=Data.modele['contact_prev_next/'+$routeParams.id][0];
			if (prev>0) $location.path('/modcontact/'+ prev);
		}
	})
	$scope.modCasquetteMod=function(cas){
		Link.set_verrou(['casquette/'+cas.id]);
		$scope.casquetteCopy=angular.copy(cas);
		var modal = $uibModal.open({
			templateUrl: 'partials/modcasquettemod.html',
			controller: 'modCasquetteModCtl',
			resolve:{
				cas: function () {
					return $scope.casquetteCopy;
				},
				index: function () {
					return $scope.index;
				},
				bouton: function () {
					return 'Modifier';
				}
			}
		});
		modal.result.then(function (cas) {
			Link.ajax([{action:'modCasquette', params:{cas:cas}}, {action:'del_verrou', type:'casquette/'+cas.id}]);
			var idx=-1;
			angular.forEach(Data.modele[$scope.key].casquettes,function(e,i){
				if (e.id==cas.id) idx=i;
			});
			if (idx>=0) Data.modele[$scope.key].casquettes[idx]=cas;
		}, function(){Link.del_verrou('casquette/'+cas.id);});
	}
	$scope.modContactMod=function(){
		Link.set_verrou([$scope.key]);
		$scope.contactCopy=angular.copy(Data.modele[$scope.key]);
		var modal = $uibModal.open({
			templateUrl: 'partials/modcontactmod.html',
			controller: 'modContactModCtl',
			resolve:{
				contact: function () {
					return $scope.contactCopy;
				}
			}
		});
		modal.result.then(function (contact) {
			$scope.modContact(contact);
		}, function(){Link.del_verrou($scope.key);});
	}
	$scope.assEtablissement=function(cas){
		Link.set_verrou(['casquette/'+cas.id]);
		$scope.modCasquette=cas;
		var modal = $uibModal.open({
			templateUrl: 'partials/assetablissement.html',
			controller: 'assEtablissementModCtl',
			resolve:{
				parsed: function () {
					return $scope.parsed;
				},
				cas: function () {
					return $scope.modCasquette;
				},
				index: function () {
					return $scope.index;
				},
				bouton: function () {
					return 'Modifier';
				}
			}
		});

		modal.result.then(function (cas) {
			Link.ajax([{action:'modCasquette', params:{cas:cas}}],
			function(){
				Link.del_verrou('casquette/'+cas.id);
				Link.context([{type:$scope.key},{type:'tags'}]);
			});
		},function(){
			Link.del_verrou('casquette/'+cas.id);
			Link.context([{type:$scope.key},{type:'tags'}]);
		});
	}
	$scope.assCasquette=function(etab){
		var modal = $uibModal.open({
			templateUrl: 'partials/asscasquette.html',
			controller: 'assIndividuModCtl',
			resolve: {
				parsed: function () {
					return $scope.parsed;
				}
			}
		});
		modal.result.then(function (cas) {
			Link.ajax([{action:'assCasquette', params:{id_etab:etab.id,id_cas:cas.id}}]);
			Link.context([{type:$scope.key},{type:'tags'}]);
		},function(){
			Link.context([{type:$scope.key},{type:'tags'}]);
		});
	}
	$scope.assTag=function(cas){
		var modal = $uibModal.open({
			templateUrl: 'partials/asstag.html',
			controller: 'assTagModCtl'
		});

		modal.result.then(function (tag) {
			var idx=-1;
			angular.forEach(Data.modele[$scope.key].casquettes,function(e,i){
				if (e.id==cas.id) idx=i;
			});
			if (idx>=0 && Data.modele[$scope.key].casquettes[idx].tags.indexOf(tag.id)<0) Data.modele[$scope.key].casquettes[idx].tags.push(tag.id);

			Link.ajax([{action:'addCasTag', params:{cas:{id:cas.id,id_contact:cas.id_contact}, tag:{id:tag.id}}}]);
		});
	}
	$scope.desAssEtablissement = function (cas) {
		cas.id_etab=0;
		Link.ajax([{action:'modCasquette', params:{cas:cas}}]);
	};
	$scope.desAssEtablissementCol = function (cas) {
		Link.ajax([{action:'desAssEtablissement', params:{cas:cas}}]);
	};
	$scope.addCasquetteMod=function(type){
		$scope.modCasquette={type:type,donnees:[]};
		var modal = $uibModal.open({
			templateUrl: 'partials/modcasquettemod.html',
			controller: 'modCasquetteModCtl',
			resolve:{
				cas: function () {
					return $scope.modCasquette;
				},
				index: function () {
					return $scope.index;
				},
				bouton: function () {
					return 'Ajouter';
				},
				contact: function () {
					return Data.modele[$scope.key];
				}
			}
		});

		modal.result.then(function (cas) {
			cas.id_contact=$routeParams.id;
			cas.nom=Data.modele[$scope.key].nom;
			cas.prenom=Data.modele[$scope.key].prenom;
			Link.ajax([{action:'addCasquette', params:{cas:cas}}]);
		});
	}
	$scope.delCasquette=function(cas){
		Link.ajax([{action:'delCasquette', params:{cas:cas}}]);
	}
	$scope.delThread=function(thread){
		Link.ajax([{action:'delSuivisThread', params:{id:thread.id}}]);
	}
	$scope.addSuivi=function(cas){
		$scope.addSuivisThreadMod(cas);
	}
	$scope.addSuivisThreadMod=function(cas){
		var modal = $uibModal.open({
			templateUrl: 'partials/addsuivisthreadmod.html',
			controller: 'addSuivisThreadModCtl',
			resolve: {
				suivis:cas.suivis
			}
		});
		modal.result.then(function (st) {
			st.id_casquette=cas.id;
			st.desc= st.desc ? st.desc : '';
			Link.ajax([{action:'addSuivisThread', params:{suivis_thread:st}}],function(r){$location.path('/addsuivi/'+ r.res);});
		});
	}
	$scope.modSuivisThread=function(thread){
		$scope.modSuivisThreadMod(thread);
	}
	$scope.modSuivisThreadMod=function(thread){
		var modal = $uibModal.open({
			templateUrl: 'partials/modsuivisthreadmod.html',
			controller: 'modSuivisThreadModCtl',
			resolve: {
				thread:thread
			}
		});
		modal.result.then(function (thread) {
			Link.ajax([{action:'modSuivisThread', params:{suivis_thread:thread}}]);
		});
	}
	$scope.CasTagClick = function(e,tag,cas) {
		if (e.shiftKey || e.ctrlKey){
			if ($window.confirm('Supprimer de la catégorie ?')) {
				$scope.delCasTag(tag,cas);
			}
		} else {
			$window.alert('Tag n°'+tag.id+'\n(maj+clic ou ctrl+clic pour enlever.)');
		}
	};
	$scope.delCasTag = function(tag,cas) {
		var idx=-1;
		angular.forEach(Data.modele[$scope.key].casquettes,function(e,i){
			if (e.id==cas.id) idx=i;
		});
		if (idx>=0) {
			var p=Data.modele[$scope.key].casquettes[idx].tags.indexOf(tag.id)
			if (p>=0) {
				Data.modele[$scope.key].casquettes[idx].tags.splice(p,1);
			}
		}
		Link.ajax([{action:'delCasTag', params:{cas:cas, tag:tag}}]);
	};
	$scope.dropOnEtabValidate=function(cas,data){
		var idx=data.idx;
		var d=cas.donnees[idx];
		return d.type=='email' || d.type=='tel' || d.type=='fax';
	};
	$scope.dropOnEtab=function(e,data,c,cas,ct){
		var idx=data.idx;
		var d=cas.donnees[idx];
		console.log(d,c,cas);
		var test=true;
		var l=d.label;
		var i=0;
		while(test){
			test=false;
			angular.forEach(cas.etab.donnees,function(cd){
				if (cd.label==l) {
					i++;
					l=d.label+' '+i;
					test=true;
				}
			});
		}
		d.label=l;
		cas.etab.donnees.push(d);
		cas.donnees_etab.push(d);
		cas.donnees.splice(idx,1);
		Link.ajax([{action:'modCasquette', params:{cas:cas.etab}},{action:'modCasquette', params:{cas:cas}}]);
	};
	$scope.dropOnCasValidate=function(cas,data){
		console.log(cas,data);
		var idx=data.idx;
		var d=cas.etab.donnees[idx];
		return d.type=='email' || d.type=='tel' || d.type=='fax';
	};
	$scope.dropOnCas=function(e,data,c,cas,ct){
		var idx=data.idx;
		var d=cas.etab.donnees[idx];
		console.log(d,c,cas);
		var test=true;
		var l=d.label;
		var i=0;
		while(test){
			test=false;
			angular.forEach(cas.donnees,function(cd){
				if (cd.label==l) {
					i++;
					l=d.label+' '+i;
					test=true;
				}
			});
		}
		d.label=l;
		cas.donnees.push(d);
		cas.donnees_etab.splice(idx,1);
		cas.etab.donnees.splice(idx,1);
		Link.ajax([{action:'modCasquette', params:{cas:cas.etab}},{action:'modCasquette', params:{cas:cas}}]);
	};
}]);
app.controller('doublonsTexteCtl', ['$scope', '$filter', '$http', '$location', '$routeParams', '$uibModal', '$window', 'Link', 'Data', 'hotkeys', function ($scope, $filter, $http, $location, $routeParams, $uibModal, $window, Link, Data, hotkeys) {
	$scope.getPage=function(init){
		var page;
		if (init) {
			if (!Data.pageDoublonsTexte) Data.pageDoublonsTexte=1;
			page=1;
		}
		page=Data.pageDoublonsTexte;
		Link.context([{type:'doublons_texte', params:{page:page, nb:$scope.itemsParPage}},{type:'tags'}]);
	};
	$scope.$watch('Data.pageDoublonsTexte',function(n,o){
		if (n!=o) {
			$scope.getPage();
		}
	});
	$scope.nonDoublonTexte=function(cas) {
		Link.ajax([{action:'nonDoublonTexte', params:{id_doublon:cas.id_doublon,id_contact:cas.id_contact}}]);
	}
	$scope.getPage(1);
	$scope.delContact=function(cas){
		Link.ajax([{action:'delContact', params:{cas:cas}}]);
	};
	$scope.dropValidate=function(cas,d){
		return cas.id_doublon==d.id_doublon && cas.id_contact!=d.id_contact;
	}
	$scope.dropOnCas=function(e,s,d,cas,ctrl){
		if(ctrl) {
			if (confirm("Vous êtes sur le point de déplacer la casquette / l'établissement. Sûr ?")) {
				s.id_contact=cas.id_contact;
				Link.ajax([{action:'moveCasquette', params:{cas:s}}]);
			}
		} else {
			if (confirm("Vous êtes sur le point de fusionner les casquettes / les établissements. Sûr ?\nCeci déplacera les suivis vers la fiche cible.")) {
				Link.ajax([{action:'mergeCasquette', params:{d:{id:cas.id},s:{id:s.id}}}]);
			}
		}
	};
}]);
app.controller('doublonsEmailCtl', ['$scope', '$filter', '$http', '$location', '$routeParams', '$uibModal', '$window', 'Link', 'Data', 'hotkeys', function ($scope, $filter, $http, $location, $routeParams, $uibModal, $window, Link, Data, hotkeys) {
	$scope.getPage=function(init){
		var page;
		if (init) {
			if (!Data.pageDoublonsEmail) Data.pageDoublonsEmail=1;
			page=1;
		}
		page=Data.pageDoublonsEmail;
		Link.context([{type:'doublons_email', params:{page:page, nb:$scope.itemsParPage}},{type:'tags'}]);
	};
	$scope.$watch('Data.pageDoublonsEmail',function(n,o){
		if (n!=o) {
			$scope.getPage();
		}
	});
	$scope.getPage(1);
	$scope.delCasquette=function(cas){
		Link.ajax([{action:'delCasquette', params:{cas:cas}}]);
	};
	$scope.delEmail=function(cas){
		Link.ajax([{action:'delEmailCasquette', params:{cas:cas}}]);
	};
	$scope.dropValidate=function(cas,d){
		return cas.id_doublon==d.id_doublon && cas.id_contact!=d.id_contact;
	}
	$scope.dropOnCas=function(e,s,d,cas,ctrl){
		if(ctrl) {
			if (confirm("Vous êtes sur le point de déplacer la casquette / l'établissement. Sûr ?")) {
				s.id_contact=cas.id_contact;
				Link.ajax([{action:'moveCasquette', params:{cas:s}}]);
			}
		} else {
			if (confirm("Vous êtes sur le point de fusionner les casquettes / les établissements. Sûr ?\nCeci déplacera les suivis vers la fiche cible.")) {
				Link.ajax([{action:'mergeCasquette', params:{d:{id:cas.id},s:{id:s.id}}}]);
			}
		}
	};
}]);
//mailing
app.controller('modmailCtl', ['$scope', '$http', '$location', '$routeParams', '$interval', '$uibModal', 'FileUploader', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $interval, $uibModal, FileUploader, Link, Data) {
	$scope.key='mail/'+$routeParams.id;
	$scope.Data=Data;
	Link.context([{type:$scope.key}],[$scope.key]);
	$scope.editorOptions = {
		height:"500px",
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
	$scope.save = function() {
			Link.ajax([{action:'modMail',params:{mail:Data.modele[$scope.key]}}])
	}
	if (!$scope.uploaders[$scope.key]) $scope.uploaders[$scope.key] = new FileUploader({
			url: 'upload.php',
		autoUpload:true,
		formData:[{id:$routeParams.id},{type:'mail'}]
	});
	$scope.delPj=function(pj){
		Link.ajax([{action:'delMailPj', params:{id:$routeParams.id,	pj:pj}}]);
	}
	$scope.envoyer=function(){
		var contexts=Data.contexts;
		var modal = $uibModal.open({
			templateUrl: 'partials/envoyer.html',
			controller: 'envoyerModCtl',
			resolve:{
				parsed: function () {
					return $scope.parsed;
				},
				type: function () {
					return 'mail';
				}
			}
		});
		modal.result.then(function (res) {
			var expediteur={id:res.expediteur.nom.id,nom:res.expediteur.nom.value,email:res.expediteur.email.value};
			res.expediteur=expediteur;
			Link.ajax([{action:'envoyer', params:{type:'mail', e:Data.modele[$scope.key], res:res}}], function(r){$location.path('/modenvoi/'+r.res);});
		},function(){Link.context(contexts);});
	}
	$scope.$watch('Data.modele["'+$scope.key+'"].verrou',function(n,o){
		if (n=='none') Link.set_verrou([$scope.key])
	});
	$scope.$on("$destroy", function(){
		if(!$scope.pristine($scope.key) && confirm("L'e-mail n'a pas été sauvé, sauver ?")) $scope.save();
		Link.del_verrou($scope.key);
	});
}]);
//mailing
app.controller('mailCtl', ['$scope', '$http', '$location', '$routeParams', '$interval', '$uibModal', 'FileUploader', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $interval, $uibModal, FileUploader, Link, Data) {
	$scope.key='mail/'+$routeParams.id;
	Link.context([{type:$scope.key}]);
	$scope.envoyer=function(){
		var contexts=Data.contexts;
		var modal = $uibModal.open({
			templateUrl: 'partials/envoyer.html',
			controller: 'envoyerModCtl',
			resolve:{
				parsed: function () {
					return $scope.parsed;
				},
				type: function () {
					return 'mail';
				}
			}
		});
		modal.result.then(function (res) {
			var expediteur={id:res.expediteur.nom.id,nom:res.expediteur.nom.value,email:res.expediteur.email.value};
			res.expediteur=expediteur;
			Link.ajax([{action:'envoyer', params:{type:'mail', e:Data.modele[$scope.key], res:res}}], function(r){$location.path('/modenvoi/'+r.res);});
		},function(){Link.context(contexts);});
	}
}]);
app.controller('mailsCtl', ['$scope', '$http', '$location', '$uibModal', 'Link', 'Data', function ($scope, $http, $location, $uibModal, Link, Data) {
	Link.context([{type:'mails'}]);
	$scope.addMailMod=function(type){
		$scope.addMail={};
		var modal = $uibModal.open({
			templateUrl: 'partials/addmailmod.html',
			controller: 'addMailModCtl',
			resolve:{
				mail: function () {
					return $scope.addMail;
				}
			}
		});

		modal.result.then(function (mail) {
			Link.ajax([{action:'addMail',params:{mail:mail}}],function(r){
				$location.path('/modmail/'+ r.res);
			});
		});
	};
	$scope.delMail=function(mail){
		Link.ajax([{action:'delMail', params:{mail:mail}}]);
	}
}]);
app.controller('newsCtl', ['$scope', '$http', '$location', '$uibModal', 'Link', 'Data', function ($scope, $http, $location, $uibModal, Link, Data) {
	Link.context([{type:'newss'}]);
	$scope.filtre={}
	$scope.Data=Data;
	$scope.addNewsMod=function(type){
		$scope.addNews={};
		var modal = $uibModal.open({
			templateUrl: 'partials/addnewsmod.html',
			controller: 'addNewsModCtl',
			resolve:{
				news: function () {
					return $scope.addNews;
				}
			}
		});

		modal.result.then(function (news) {
			Link.ajax([{action:'addNews', params:{news:news}}],function(r){
				$location.path('/modnews/'+ r.res);
			});
		});
	};
	$scope.delNews=function(news){
		Link.ajax([{action:'delNews',params:{news:news}}]);
	}
	$scope.dupNews=function(news){
		Link.ajax([{action:'dupNews',params:{news:news}}]);
	}
	$scope.$watch('pageCourante.news',function(n,o){
		if (n!=o) Link.context([{type:'newss',params:{page:$scope.pageCourante.news,nb:$scope.itemsParPage,filtre:$scope.filtre}}]);
	});
	$scope.$watchCollection('filtre.news',function(n,o){
		if (n!=o) Link.context([{type:'newss',params:{page:$scope.pageCourante.news,nb:$scope.itemsParPage,filtre:$scope.filtre.news}}]);
	});
}]);
app.controller('modnewsCtl', ['$timeout', '$window', '$scope', '$http', '$location', '$routeParams', '$interval', '$sce', '$uibModal', 'FileUploader', 'Link', 'Data', function ($timeout, $window, $scope, $http, $location, $routeParams, $interval, $sce, $uibModal, FileUploader, Link, Data) {
	$scope.mini={bool:false};
	$scope.Data=Data;
	$scope.key='news/'+$routeParams.id;
	Link.context([{type:$scope.key},{type:'modeles'}]);
	$scope.showFichiers=false;
	$scope.resizeNews=function(){
		var scale=1,
			dx=0,
			dy=0,
			pw=document.getElementById('news-container').clientWidth,
			ph=document.getElementById('news-container').clientHeight;
		if (pw<700) {
			scale=pw/700;
			angular.element(document.getElementById('news-container')).css('transform-origin','top left');
			angular.element(document.getElementById('news-container')).css('transform','scale('+scale+')');
		} else {
			angular.element(document.getElementById('news-container')).css('transform-origin','top left');
			angular.element(document.getElementById('news-container')).css('transform','scale(1)');
		}
	};
	$scope.pdf=function(){
		var data={
			type:'news_pdf',
			id_news:$routeParams.id
		};
		angular.element.redirect('doc.php',data,'POST','_blank');
	};
	$scope.publie=function(){
		Data.modele[$scope.key].publie=1;
		$scope.save();
	};
	$scope.unpublie=function(){
		Data.modele[$scope.key].publie=0;
		$scope.save();
	};
	$scope.modCats=[];
	$scope.buildModCats=function(){
		if (Data.modele.modeles){
			var tmp=[];
			angular.forEach(Data.modele.modeles, function(m){
				var tab=m.nom ? m.nom.split('::') : [''];
				if (tab.length>1) {
					theme=tab[0];
					if (tmp.indexOf(theme)<0) tmp.push(theme);
				}
			});
			tmp.sort();
			angular.forEach(Data.modele.modeles, function(m){
				var tab=m.nom ? m.nom.split('::') : [''];
				if (tab.length==1) {
					theme='Sans thème';
					if (tmp.indexOf(theme)<0) tmp.push(theme);
				}
			});
			var res=[];
			angular.forEach(tmp, function(nom){
				var tab=nom ? nom.split('_') : [''];
				if (tab.length>1) {
					res.push({label:tab[1],nom:nom});
				} else {
					res.push({label:nom,nom:nom});
				}
			});
			$scope.modCats=res;
		}
	};
	$scope.buildModCats();
	$scope.$watchCollection('Data.modele.modeles',function(o,n){
		if(o!=n) {
			$scope.buildModCats();
		}
	});
	$scope.prepNews=function(news){
		var n=angular.copy(news);
		for(var i=0;i<n.blocs.length;i++){
			delete n.blocs[i].verrou;
			delete n.blocs[i].html;
		}
		return n;
	}
	angular.element($window).on('resize', $scope.resizeNews);
	$scope.modSujetMod=function(){
		Link.set_verrou([$scope.key]);
		$scope.contactCopy=angular.copy(Data.modele[$scope.key]);
		var modal = $uibModal.open({
			templateUrl: 'partials/modsujetmod.html',
			controller: 'modSujetModCtl',
			resolve:{
				sujet: function () {
					return Data.modele[$scope.key].sujet;
				}
			}
		});
		modal.result.then(function (sujet) {
			Data.modele[$scope.key].sujet=sujet;
			Link.ajax([{action:'modNews', params:{news:$scope.prepNews(Data.modele[$scope.key])}}],function(){Link.del_verrou($scope.key);});
		}, function(){Link.del_verrou($scope.key);});
	}
	$scope.modNewsletterMod=function(){
		Link.set_verrou(['newsletter'+$scope.key]);
		var modal = $uibModal.open({
			templateUrl: 'partials/modnewslettermod.html',
			controller: 'modNewsletterModCtl',
			resolve:{
				idx: function () {
					return Data.modele[$scope.key].id_newsletter;
				}
			}
		});
		modal.result.then(function (idx) {
			Data.modele[$scope.key].id_newsletter=idx;
			Link.ajax([{action:'modNews', params:{news:$scope.prepNews(Data.modele[$scope.key])}}],function(){Link.del_verrou('newsletter'+$scope.key);});
		}, function(){Link.del_verrou('newsletter'+$scope.key);});
	}
	$scope.modNomCat=function(nomCat){
		var modal = $uibModal.open({
			templateUrl: 'partials/modnomcatmod.html',
			controller: 'modNomCatModCtl',
			resolve:{
				nomCat: function () {
					return nomCat;
				},
				bouton: function () {
					return 'Modifier';
				}
			}
		});
		modal.result.then(function (nomCatNew) {
			Link.ajax([{action:'modNomCat', params:{nom_cat_new:nomCatNew,nom_cat:nomCat}}]);
		});
	}
	$scope.addModeleMod=function(type){
		$scope.addModele={};
		var modal = $uibModal.open({
			templateUrl: 'partials/addmodelemod.html',
			controller: 'addModeleModCtl',
			resolve:{
				modele: function () {
					return $scope.addModele;
				}
			}
		});

		modal.result.then(function (modele) {
			Link.ajax([{action:'addModele', params:{modele:modele}}], function(r){
				$location.path('/modmodele/'+ r.res[0]);
			});
		});
	};
	$scope.save = function() {
		if (!$scope.pristine($scope.key)) {
			Link.ajax([{action:'modNews', params:{news:$scope.prepNews(Data.modele[$scope.key])}}]);
		}
	};
	$scope.drop = function(e,s,d,c){
		if (c=="order") {
			var src=angular.copy(Data.modele[$scope.key].blocs[s-1]);
			Data.modele[$scope.key].blocs.splice(d,0,src);
			if (s-1>d)
				Data.modele[$scope.key].blocs.splice(s,1);
			else
				Data.modele[$scope.key].blocs.splice(s-1,1)
			$scope.save();
		}
		if (c=="bloc") {
			$scope.addBloc(e,s,d);
		}
	};
	$scope.addBloc = function(e,d,i){
		var bloc={id_modele:d.id, id:Math.random().toString(36).substr(2, 9)};
		Data.modele[$scope.key].blocs.splice(i,0,bloc);
		$scope.save();
	};
	$scope.modBlocMod=function(bloc){
		if (!bloc.id) {
			bloc.id=Math.random().toString(36).substr(2, 9);
			$scope.save();
		}
		Link.set_verrou(['newsbloc/'+$routeParams.id+'/'+bloc.id]);
		var modal = $uibModal.open({
			templateUrl: 'partials/modblocmod.html',
			controller: 'modBlocModCtl',
			resolve:{
				trust: function () {
					return $scope.trust;
				},
				bloc: function () {
					return angular.copy(bloc);
				},
				pjs: function () {
					return angular.copy(Data.modele[$scope.key].pjs);
				}
			}
		});
		modal.result.then(function (bloc) {
			Data.modele[$scope.key].blocs.forEach(function(e,i){
				if (e.id==bloc.id) Data.modele[$scope.key].blocs[i]=bloc;
			});
			Link.ajax([{action:'modNews', params:{news:$scope.prepNews(Data.modele[$scope.key])}},{action:'del_verrou', type:'newsbloc/'+$routeParams.id+'/'+bloc.id}]);
		}, function(){Link.del_verrou('newsbloc/'+$routeParams.id+'/'+bloc.id);});
	};
	$scope.delBloc=function(i){
		Data.modele[$scope.key].blocs.splice(i,1);
		$scope.save();
	};
	if (!$scope.uploaders[$scope.key]) $scope.uploaders[$scope.key] = new FileUploader({
			url: 'upload.php',
		autoUpload:true,
		formData:[{id:$routeParams.id},{type:'news'}]
	});
	$scope.delPj=function(pj){
		Link.ajax([{action:'delNewsPj', params:{id:$routeParams.id, pj:pj}}]);
	}
	$scope.delModele=function(modele){
		Link.ajax([{action:'delModele', params:{modele:modele}}]);
	}
	$scope.envoyer=function(){
		var nbpj=0;
		angular.forEach(Data.modele[$scope.key].pjs,function(e){
			if (!e.used) nbpj++;
		});
		if (nbpj==0 || nbpj>0 && confirm("Cette newsletter a "+nbpj+" piece(s) jointe(s).\nSi ce n'est pas normal, pensez à supprimer les images non utilisées dans la newsletter.")) {
			var contexts=angular.copy(Data.contexts);
			var modal = $uibModal.open({
				templateUrl: 'partials/envoyer.html',
				controller: 'envoyerModCtl',
				resolve:{
					parsed: function () {
						return $scope.parsed;
					},
					type: function () {
						return 'mail';
					}
				}
			});
			modal.result.then(function (res) {
				var expediteur={id:res.expediteur.nom.id,nom:res.expediteur.nom.value,email:res.expediteur.email.value};
				res.expediteur=expediteur;
				Link.ajax([{action:'envoyer', params:{type:'news', e:Data.modele[$scope.key], res:res}}], function(r){$location.path('/modenvoi/'+r.res);});
			},function(){Link.context(contexts);});
		}
	}
	$scope.$on("$destroy", function(){
		angular.element($window).off('resize', $scope.resizeNews);
	});
	waitUntil(function(){return document.getElementById('news-container') && document.getElementById('news-container').clientWidth>0},$scope.resizeNews);
}]);
app.controller('modmodeleCtl', ['$scope', '$http', '$location', '$routeParams', '$interval', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $interval, Link, Data) {

	$scope.key='modele/'+$routeParams.id;
	Link.context([{type:$scope.key}],[$scope.key]);
	$scope.save = function() {
		Link.ajax([{action:'modModele', params:{modele:Data.modele[$scope.key]}}]);
	}
	$scope.$on("$destroy", function(){
		Link.del_verrou($scope.key);
	});
}]);

//envois
app.controller('envoisCtl', ['$scope', '$http', '$location', '$uibModal', 'Link', 'Data', function ($scope, $http, $location, $uibModal, Link, Data) {
	Link.context([{type:'envois',params:{page:$scope.pageCourante.envois,nb:$scope.itemsParPage}}, {type:'imap'}, {type:'casquettes_mail_erreur',params:{page:$scope.pageCourante.erreur,nb:$scope.itemsParPage}},{type:'impacts',params:{page:$scope.pageCourante.impacts,nb:$scope.itemsParPage,id_envoi:-1,id_news:-1,id_mail:-1}}]);
	$scope.$watch('pageCourante.envois',function(n,o){
		if (n!=o) Link.context([{type:'envois',params:{page:$scope.pageCourante.envois,nb:$scope.itemsParPage}}, {type:'imap'}, {type:'casquettes_mail_erreur',params:{page:$scope.pageCourante.erreur,nb:$scope.itemsParPage}},{type:'impacts',params:{page:$scope.pageCourante.impacts,nb:$scope.itemsParPage,id_envoi:-1,id_news:-1,id_mail:-1}}]);
	});
	$scope.$watch('pageCourante.erreur',function(n,o){
		if (n!=o) Link.context([{type:'envois'}, {type:'imap'}, {type:'casquettes_mail_erreur',params:{page:$scope.pageCourante.erreur,nb:$scope.itemsParPage}},{type:'impacts',params:{page:$scope.pageCourante.impacts,nb:$scope.itemsParPage,id_envoi:-1,id_news:-1,id_mail:-1}}]);
	});
	$scope.$watch('pageCourante.impacts',function(n,o){
		if (n!=o) Link.context([{type:'envois'}, {type:'imap'}, {type:'casquettes_mail_erreur',params:{page:$scope.pageCourante.erreur,nb:$scope.itemsParPage}},{type:'impacts',params:{page:$scope.pageCourante.impacts,nb:$scope.itemsParPage,id_envoi:-1,id_news:-1,id_mail:-1}}]);
	});
	$scope.checkImap=function(){
		Link.ajax([{action:'checkImap', params:{}}]);
	};
}]);
app.controller('modenvoiCtl', ['$scope', '$http', '$location', '$routeParams', '$interval', '$sce', '$uibModal', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $interval, $sce, $uibModal, Link, Data) {
	$scope.key='envoi/'+$routeParams.id;
	$scope.getPage=function(){
		Link.context([{type:$scope.key,params:{
			boite:{page:$scope.pageCourante.boite,nb:$scope.itemsParPage},
			succes:{page:$scope.pageCourante.succes,nb:$scope.itemsParPage},
			erreur:{page:$scope.pageCourante.erreur,nb:$scope.itemsParPage},
			impact:{page:$scope.pageCourante.impact,nb:$scope.itemsParPage}
		}}]);
	}
	$scope.Data=Data;
	$scope.tabSwitch=1;
	$scope.pageCourante={};
	$scope.pageCourante.boite=1;
	$scope.pageCourante.erreur=1;
	$scope.pageCourante.succes=1;
	$scope.pageCourante.impact=1;
	$scope.$watchCollection('pageCourante',function(){
		$scope.getPage();
	});
	$scope.itemsParPage=10;
	$scope.$watch('envoi.boite_envoi.length',function(){
		if (Data.modele[$scope.key]) {
			if (Data.modele[$scope.key].boite_envoi && Data.modele[$scope.key].boite_envoi.length >0) {
				$scope.tabSwitch=2;
			} else if (Data.modele[$scope.key].succes_log && Data.modele[$scope.key].succes_log.length>0){
				$scope.tabSwitch=3;
			} else {
				$scope.tabSwitch=1;
			}
		}
	});
	$scope.play=function(){
		$scope.Data.modele[$scope.key].statut=0;
		Link.ajax([{action:'playEnvoi',params:{id:$routeParams.id}}]);
	}
	$scope.pause=function(){
		Link.ajax([{action:'pauseEnvoi',params:{id:$routeParams.id}}]);
	}
	$scope.restart=function(){
		$scope.Data.modele[$scope.key].statut=0;
		Link.ajax([{action:'restartEnvoi',params:{id:$routeParams.id}}]);
	}
	$scope.vide=function(){
		Link.ajax([{action:'videEnvoi',params:{id:$routeParams.id}}]);
	}
	$scope.delSchedule=function(date){
		Link.ajax([{action:'delScheduleEnvoi', params:{id_envoi:$routeParams.id}}]);
	}
	$scope.modSchedule=function(date){
		var test=false;
		var d=new Date();
		if (date) test=true;
		else date=d.getTime();
		var modal = $uibModal.open({
			templateUrl: 'partials/modscheduleenvoimod.html',
			controller: 'modScheduleEnvoiModCtl',
			resolve:{
				date: function () {
					return date;
				},
				bouton: function () {
					return 'Modifier';
				}
			}
		});
		modal.result.then(function (d) {
			console.log(d);
			if (test) Link.ajax([{action:'modScheduleEnvoi', params:{id_envoi:$routeParams.id,date:d}}]);
			else Link.ajax([{action:'addScheduleEnvoi', params:{id_envoi:$routeParams.id,date:d}}]);
		});
	}
}]);

//supports
app.controller('supportsCtl', ['$scope', '$http', '$location', '$uibModal', 'Link', 'Data', function ($scope, $http, $location, $uibModal, Link, Data) {
	Link.context([{type:'supports'}]);
	$scope.addSupportMod=function(type){
		$scope.addSupport={};
		var modal = $uibModal.open({
			templateUrl: 'partials/addsupportmod.html',
			controller: 'addSupportModCtl',
			resolve:{
				support: function () {
					return $scope.addSupport;
				}
			}
		});

		modal.result.then(function (support) {
			Link.ajax([{action:'addSupport',params:{support:support}}],function(r){
				$location.path('/modsupport/'+ r.res[0]);
			});
		});
	};
	$scope.delSupport=function(support){
		Link.ajax([{action:'delSupport', params:{support:support}}]);
	}
}]);
app.controller('modsupportCtl', ['$scope', '$http', '$location', '$routeParams', '$interval', '$uibModal', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $interval, $uibModal, Link, Data) {
	$scope.key='support/'+$routeParams.id;
	Link.context([{type:$scope.key}],[$scope.key]);
	var P=[];
	$scope.plan = function(){
		if (Data.modele[$scope.key] && Data.modele[$scope.key].nom) {
		var s=$scope.Data.modele[$scope.key];
		var Ptmp=[];
		for (var i=0;i<s.nb_lignes;i++){
			for (var j=0;j<s.nb_colonnes;j++){
				var o={};
				o.top=parseInt(s.mp_haut)+i*(s.h_page-s.mp_haut-s.mp_bas)/s.nb_lignes+parseInt(s.mc_haut);
				o.left=parseInt(s.mp_gauche)+j*(s.l_page-s.mp_gauche-s.mp_droite)/s.nb_colonnes+parseInt(s.mc_gauche);
				o.width=((s.l_page-s.mp_gauche-s.mp_droite)/s.nb_colonnes)-s.mc_gauche-s.mc_droite;
				o.height=((s.h_page-s.mp_haut-s.mp_bas)/s.nb_lignes)-s.mc_haut-s.mc_bas;
				Ptmp.push(o);
			}
		}
		if (!angular.equals(P,Ptmp)) P=Ptmp;
		}
		return P;
	}
	$scope.pdf=function(support){
		var contexts=Data.contexts;
		var modal = $uibModal.open({
			templateUrl: 'partials/pdf.html',
			controller: 'envoyerModCtl',
			resolve:{
				parsed: function () {
					return $scope.parsed;
				},
				type: function () {
					return 'adresse';
				}
			}
		});
		modal.result.then(function (res) {
			var data={
				verb:'getPdf',
				type:'publipostage',
				id_support:support.id,
				res:res
			};
			angular.element.redirect('doc.php',data,'POST','_blank');
			Link.context(contexts);
		},function(){Link.context(contexts);});
	}
	$scope.save=function(){
		Link.ajax([{action:'modSupport',params:{support:Data.modele[$scope.key]}}]);
	};
	$scope.$watch('Data.modele["'+$scope.key+'"].verrou',function(n,o){
		if (n=='none') Link.set_verrou([$scope.key])
	});
	$scope.$on("$destroy", function(){
		if(!$scope.pristine($scope.key) && confirm("Le support n'a pas été sauvé, sauver ?")) $scope.save();
		Link.del_verrou($scope.key);
	});
}]);
//templates
app.controller('templatesCtl', ['$scope', '$http', '$location', '$uibModal', 'Link', 'Data', function ($scope, $http, $location, $uibModal, Link, Data) {
	Link.context([{type:'templates'}]);
	$scope.addTemplateMod=function(type){
		$scope.addTemplate={};
		var modal = $uibModal.open({
			templateUrl: 'partials/addtemplatemod.html',
			controller: 'addTemplateModCtl',
			resolve:{
				template: function () {
					return $scope.addTemplate;
				}
			}
		});

		modal.result.then(function (template) {
			Link.ajax([{action:'addTemplate',params:{template:template}}],function(r){
				$location.path('/modtemplate/'+ r.res[0]);
			});
		});
	};
	$scope.delTemplate=function(template){
		Link.ajax([{action:'delTemplate', params:{template:template}}]);
	}
}]);
app.controller('modtemplateCtl', ['$scope', '$http', '$location', '$routeParams', '$interval', '$uibModal', 'FileUploader', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $interval, $uibModal, FileUploader, Link, Data) {
	$scope.key='template/'+$routeParams.id;
	Link.context([{type:$scope.key}],[$scope.key]);
	if (!$scope.uploaders[$scope.key]) $scope.uploaders[$scope.key] = new FileUploader({
		url: 'upload.php',
		autoUpload:true,
		formData:[{id:$routeParams.id},{type:'template'}]
	});
	$scope.delTpl=function(tpl){
		Link.ajax([{action:'delTpl', params:{id:$routeParams.id,tpl:tpl}}]);
	}
	$scope.pdf=function(){
		var contexts=Data.contexts;
		var modal = $uibModal.open({
			templateUrl: 'partials/pdf.html',
			controller: 'envoyerModCtl',
			resolve:{
				parsed: function () {
					return $scope.parsed;
				},
				type: function () {
					return 'tout';
				}
			}
		});
		modal.result.then(function (res) {
			var data={
				verb:'getPdf',
				type:'publipostage_template',
				template:Data.modele[$scope.key].template[0].path,
				res:res
			};
			angular.element.redirect('doc.php',data,'POST','_blank');
			Link.context(contexts);
		},function(){Link.context(contexts);});
	}
	$scope.save=function(){
		Link.ajax([{action:'modTemplate',params:{template:Data.modele[$scope.key]}}]);
	};
	$scope.$watch('Data.modele["'+$scope.key+'"].verrou',function(n,o){
		if (n=='none') Link.set_verrou([$scope.key])
	});
	$scope.$on("$destroy", function(){
		if(!$scope.pristine($scope.key) && confirm("Le template n'a pas été sauvé, sauver ?")) $scope.save();
		Link.del_verrou($scope.key);
	});
}]);



//suivis
app.controller('suivisCtl', ['$scope', '$http', '$location', '$uibModal', 'Link', 'Data', function ($scope, $http, $location, $uibModal, Link, Data) {
	$scope.getPage=function(init){
		var pagePr;
		var pageRe;
		var pageTe;
		if (init) {
			if (!Data.pageSuivisPr) Data.pageSuivisPr=1;
			if (!Data.pageSuivisRe) Data.pageSuivisRe=1;
			if (!Data.pageSuivisTe) Data.pageSuivisTe=1;
			Data.suiviGroup=0;
			page=1;
		}
		pagePr=Data.pageSuivisPr;
		pageRe=Data.pageSuivisRe;
		pageTe=Data.pageSuivisTe;
		group=Data.suivisGroup;
		Link.context([{type:'suivis', params:{pagePr:pagePr, pageRe:pageRe, pageTe:pageTe, group:group, nb:$scope.itemsParPage}}]);
	};
	$scope.$watch('Data.suivisGroup',function(n,o){
		if (n!=o) {
			Data.pageSuivisPr=1;
			Data.pageSuivisRe=1;
			Data.pageSuivisTe=1;
			$scope.getPage();
		}
	});
	$scope.$watch('Data.pageSuivisPr',function(n,o){
		if (n!=o) {
			$scope.getPage();
		}
	});
	$scope.$watch('Data.pageSuivisRe',function(n,o){
		if (n!=o) {
			$scope.getPage();
		}
	});
	$scope.$watch('Data.pageSuivisTe',function(n,o){
		if (n!=o) {
			$scope.getPage();
		}
	});
	$scope.getPage(1);
}]);
app.controller('modsuiviCtl', ['$scope', '$http', '$location', '$routeParams', '$interval', '$sce', '$uibModal', 'FileUploader', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $interval, $sce, $uibModal, FileUploader, Link, Data) {
	$scope.key='suivi/'+$routeParams.id;
	Link.context([{type:'suivi/'+$routeParams.id}]);
	$scope.ev={};
	$scope.test=true;
	$scope.casKey=0;
	$scope.contactKey=0;
	$scope.threadKey=0;
	$scope.$watchCollection('Data', function(){
		if ($scope.test && Data.modele[$scope.key]) {
			$scope.update();
			$scope.test=false;
		}
	});
	$scope.update=function(){
		if (Data.modele[$scope.key].statut==0) Link.set_verrou([$scope.key]);
		$scope.casKey=Data.modele[$scope.key].cas.id;
		$scope.contactKey='contact/'+Data.modele[$scope.key].cas.id_contact;
		$scope.threadKey='suivis_thread/'+Data.modele[$scope.key].id_thread;
		Link.context([{type:'suivi/'+$routeParams.id},{type:$scope.threadKey},{type:$scope.contactKey},{type:'tags'}]);
	};
	if (Data.modele[$scope.key]) $scope.update();
	$scope.modSuivisThread=function(thread){
		$scope.modSuivisThreadMod(thread);
	}
	$scope.modSuivisThreadMod=function(thread){
		var modal = $uibModal.open({
			templateUrl: 'partials/modsuivisthreadmod.html',
			controller: 'modSuivisThreadModCtl',
			resolve: {
				thread:thread
			}
		});
		modal.result.then(function (thread) {
			Link.ajax([{action:'modSuivisThread', params:{suivis_thread:thread}}]);
		});
	}
	$scope.sv=false;
	$scope.cas={};
	$scope.showDesc={};
	$scope.editorOptions = {
		height:"300px",
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
		removeButtons:"Source,Save,NewPage,Preview,Print,Templates,Cut,Undo,Redo,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,HiddenField,Checkbox,TextField,Textarea,Select,Button,ImageButton,Radio,Strike,Subscript,Superscript,NumberedList,Outdent,Indent,BulletedList,CreateDiv,BidiLtr,BidiRtl,Language,Anchor,Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Styles,Format,Font,BGColor,ShowBlocks,About"
	};
	$scope.del = function() {
		Link.ajax([{action:'delSuivi',params:{id:$routeParams.id}}],function(data){
			var id=data.res[0];
			//console.log(id);
			if (id>0) $location.path('/modsuivi/'+id);
			else $location.path('/suivis/');
		});
	}
	$scope.save = function() {
		Data.modele[$scope.key].date=new Date(Data.modele[$scope.key].date).getTime();
		var s={};
		s.titre=Data.modele[$scope.key].titre;
		s.desc=Data.modele[$scope.key].desc;
		s.date=Data.modele[$scope.key].date;
		s.statut=Data.modele[$scope.key].statut;
		s.id=Data.modele[$scope.key].id;
		s.id_thread=Data.modele[$scope.key].id_thread;
		Link.ajax([{action:'modSuivi',params:{suivi:s}}],$scope.update);
	}
	$scope.next = function() {
		Data.modele[$scope.key].statut=1;
		Data.modele[$scope.key].date=new Date(Data.modele[$scope.key].date).getTime();
		var s={};
		s.titre=Data.modele[$scope.key].titre;
		s.desc=Data.modele[$scope.key].desc;
		s.date=Data.modele[$scope.key].date;
		s.statut=Data.modele[$scope.key].statut;
		s.id=Data.modele[$scope.key].id;
		s.id_thread=Data.modele[$scope.key].id_thread;
		Link.ajax([{action:'modSuivi',params:{suivi:s}}], function(){$location.path('/addsuivi/'+ Data.modele[$scope.key].id_thread)});
	}
	$scope.close = function() {
		Link.del_verrou($scope.key);
		Data.modele[$scope.key].statut=1;
		Data.modele[$scope.key].date=new Date(Data.modele[$scope.key].date).getTime();
		var s={};
		s.titre=Data.modele[$scope.key].titre;
		s.desc=Data.modele[$scope.key].desc;
		s.date=Data.modele[$scope.key].date;
		s.statut=Data.modele[$scope.key].statut;
		s.id=Data.modele[$scope.key].id;
		s.id_thread=Data.modele[$scope.key].id_thread;
		Link.ajax([{action:'modSuivi',params:{suivi:s}}]);
	}
	$scope.open = function() {
		Link.set_verrou([$scope.key]);
		Data.modele[$scope.key].statut=0;
		Data.modele[$scope.key].date=new Date(Data.modele[$scope.key].date).getTime();
		var s={};
		s.titre=Data.modele[$scope.key].titre;
		s.desc=Data.modele[$scope.key].desc;
		s.date=Data.modele[$scope.key].date;
		s.statut=Data.modele[$scope.key].statut;
		s.id=Data.modele[$scope.key].id;
		s.id_thread=Data.modele[$scope.key].id_thread;
		Link.ajax([{action:'modSuivi',params:{suivi:s}}]);
	}
	$scope.modSuivi=function(suivi){
		if (!Data.modele['suivi/'+suivi.id]) {
			suivi.cas=Data.modele[$scope.key].cas;
			Data.modele['suivi/'+suivi.id]=suivi;
			Data.modeleSrv['suivi/'+suivi.id]=suivi;
		}
		$location.path('/modsuivi/'+ suivi.id);
	}
	$scope.assCasquette=function(){
		var modal = $uibModal.open({
			templateUrl: 'partials/asscasquette.html',
			controller: 'assCasquetteModCtl',
			resolve: {
				parsed: function () {
					return $scope.parsed;
				}
			}
		});
		modal.result.then(function (cas) {
			Data.modele[$scope.key].id_casquette=cas.id;
			Data.modele[$scope.key].cas=cas;
			$scope.save();
		}, function(){
			$scope.update();
		});
	}
	$scope.toggle_acl_group=function(g){
		if (Data.modele[$scope.threadKey].acl.group.indexOf(g.id)>=0) Link.ajax([{action:'delAcl',params:{type_ressource:'suivis_threads',id_ressource:Data.modele[$scope.key].id_thread,type_acces:'group',id_acces:g.id}}]);
		else Link.ajax([{action:'addAcl',params:{type_ressource:'suivis_threads',id_ressource:Data.modele[$scope.key].id_thread,type_acces:'group',id_acces:g.id,level:3}}]);
	}
	$scope.toggle_acl_user=function(u){
		if (Data.modele[$scope.threadKey].acl.user.indexOf(u.id)>=0) Link.ajax([{action:'delAcl',params:{type_ressource:'suivis_threads',id_ressource:Data.modele[$scope.key].id_thread,type_acces:'user',id_acces:u.id}}]);
		else Link.ajax([{action:'addAcl',params:{type_ressource:'suivis_threads',id_ressource:Data.modele[$scope.key].id_thread,type_acces:'user',id_acces:u.id,level:3}}]);
	}

	$scope.$on("$destroy", function(){
		Link.del_verrou($scope.key);
	});
}]);
app.controller('addsuiviCtl', ['$scope', '$http', '$location', '$routeParams', '$interval', '$sce', '$uibModal', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $interval, $sce, $uibModal, Link, Data) {
	$scope.suivi={
		titre:'',
		desc:'',
		date:new Date().getTime(),
		statut:0,
		id_thread:$routeParams.id
	};
	$scope.editorOptions = {
		height:"500px",
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
		removeButtons:"Source,Save,NewPage,Preview,Print,Templates,Cut,Undo,Redo,Copy,Paste,PasteText,PasteFromWord,Find,Replace,SelectAll,Scayt,Form,HiddenField,Checkbox,TextField,Textarea,Select,Button,ImageButton,Radio,Strike,Subscript,Superscript,NumberedList,Outdent,Indent,BulletedList,CreateDiv,BidiLtr,BidiRtl,Language,Anchor,Image,Flash,Table,HorizontalRule,Smiley,SpecialChar,PageBreak,Iframe,Styles,Format,Font,BGColor,ShowBlocks,About"
	};
	$scope.addSuivi=function(cas){
		$scope.suivi.date=new Date($scope.suivi.date).getTime();
		Link.ajax([{action:'addSuivi',params:{suivi:$scope.suivi}}],function(r){$location.path('/modsuivi/'+ r.res);});
	}
}]);


//admin
app.controller('adminCtl', ['$scope', '$http', '$location', 'Link', 'Data', function ($scope, $http, $location, Link, Data) {
	Link.context([{type:'log'}]);
	$scope.page={users:1,groups:1};
	$scope.setVerrou=function(){
		Link.set_verrou(['config']);
		$scope.stopVerrou=$scope.$watchCollection('Data.modele.config',function(n,o){
			if (n!=o) Link.set_verrou(['config']);
		});
	}
	$scope.delVerrou=function(){
		if(!$scope.pristine('config') && confirm("La configuration n'a pas été sauvée, sauver ?")) $scope.save();
		Link.del_verrou('config');
		$scope.stopVerrou();
	}
	$scope.delUser=function(id){
		Link.ajax([{action:'delUser',params:{id:id}}]);
	};
	$scope.delGroup=function(id){
		Link.ajax([{action:'delGroup',params:{id:id}}]);
	};
	$scope.routeVerb=function (verb) {
		var tab=verb.replace(/\W+/g, '-')
		.replace(/([a-z\d])([A-Z])/g, '$1-$2').split('-');
		return tab[1].toLowerCase();
	}
	$scope.dump=function (o) {
		return JSON.stringify(o, null, 4)
	}
	$scope.show={};
	$scope.setConfig=function(){
		Link.ajax([{action:'setConfig',params:{config:Data.modele.config.config}}]);
	}
}]);
app.controller('addUserCtl', ['$scope', '$http', '$location', 'Link', 'Data', function ($scope, $http, $location, Link, Data) {
	Link.context([]);
	$scope.newUser={};
	$scope.addUser=function(){
		Link.ajax([{action:'addUser',params:{name:$scope.newUser.name,login:$scope.newUser.login,pwd:$scope.newUser.pwd}}],function(){
			$location.path('/admin');
		});
	};
	$scope.loginExists= function(login){
		var test=false;
		angular.forEach(Data.modele.users,function(u){
			if (u.login==login){
				test=true;
			}
		});
		return test;
	}
}]);
app.controller('addGroupCtl', ['$scope', '$http', '$location', 'Link', 'Data', function ($scope, $http, $location, Link, Data) {
	Link.context([]);
	$scope.newGroup={};
	$scope.addGroup=function(){
		Link.ajax([{action:'addGroup',params:{nom:$scope.newGroup.nom}}],function(){
			$location.path('/admin');
		});
	};
}]);
app.controller('moiCtl', ['$scope', '$http', '$location', '$timeout', 'Link', 'Data', function ($scope, $http, $location, $timeout, Link, Data) {
	$scope.key='user/'+Data.user.id;
	Link.context([],[$scope.key]);
	$scope.modUser={};
	$timeout(function(){
		$scope.modUser.id=Data.user.id;
		$scope.modUser.name=Data.user.name;
		$scope.modUser.login=Data.user.login;
	},500);
	$scope.$watch('Data.user.name',function(n,o){
		if (n && n!=o) $timeout(function(){
			$scope.modUser.id=Data.user.id;
			$scope.modUser.name=Data.user.name;
			$scope.modUser.login=Data.user.login;
		},500);
	});
	$scope.toggle_group=function(g){
		if (g.users && g.users.indexOf($scope.modUser.id)>=0) Link.ajax([{action:'delUserGroup',params:{id_user:Data.user.id,id_group:g.id}}]);
		else Link.ajax([{action:'addUserGroup',params:{id_user:Data.user.id,id_group:g.id}}]);
	}
	$scope.mod=function(){
		Link.ajax([{action:'modUser',params:{id:Data.user.id,login:Data.user.login,name:$scope.modUser.name,pwd:$scope.modUser.pwd}}],function(){
			$location.path('/admin');
		});
	};
}]);
app.controller('modUserCtl', ['$scope', '$http', '$location', '$routeParams', '$timeout', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $timeout, Link, Data) {
	$scope.key='user/'+$routeParams.id;
	Link.context([],[$scope.key]);
	$scope.modUser={};
	$timeout(function(){
		$scope.modUser.id=$routeParams.id;
		$scope.modUser.name=Data.modele.users[$routeParams.id].name;
		$scope.modUser.login=Data.modele.users[$routeParams.id].login;
	},500);
	$scope.$watchCollection('Data.modele.users',function(n,o){
		if (n && n!=o) $timeout(function(){
			$scope.modUser.id=$routeParams.id;
			$scope.modUser.name=Data.modele.users[$routeParams.id].name;
			$scope.modUser.login=Data.modele.users[$routeParams.id].login;
		},500);
	});
	$scope.mod=function(){
		Link.ajax([{action:'modUser',params:{id:$routeParams.id,login:Data.modele.users[$routeParams.id].login,name:$scope.modUser.name,pwd:$scope.modUser.pwd}}],function(){
			$location.path('/admin');
		});
	};
	$scope.toggle_group=function(g){
		if (g.users && g.users.indexOf($scope.modUser.id)>=0) Link.ajax([{action:'delUserGroup',params:{id_user:$routeParams.id,id_group:g.id}}]);
		else Link.ajax([{action:'addUserGroup',params:{id_user:$routeParams.id,id_group:g.id}}]);
	}
	$scope.$on("$destroy", function(){
		Link.del_verrou($scope.key);
	});
}]);
app.controller('modGroupCtl', ['$scope', '$http', '$location', '$routeParams', '$timeout', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $timeout, Link, Data) {
	$scope.Data=Data;
	$scope.key='group/'+$routeParams.id;
	$scope.i=$routeParams.id;
	Link.context([],[$scope.key]);
	$scope.mod=function(){
		Link.ajax([{action:'modGroup',params:{id:$routeParams.id,nom:Data.modele.groups[$routeParams.id].nom}}],function(){
			$location.path('/admin');
		});
	};
	$scope.toggle_group=function(u){
		if (Data.modele.groups[$routeParams.id].users && Data.modele.groups[$routeParams.id].users.indexOf(u.id)>=0) Link.ajax([{action:'delUserGroup',params:{id_user:u.id,id_group:$routeParams.id}}]);
		else Link.ajax([{action:'addUserGroup',params:{id_user:u.id,id_group:$routeParams.id}}]);
	}
	$scope.$on("$destroy", function(){
		Link.del_verrou($scope.key);
	});
}]);

app.controller('addSuivisThreadModCtl', ['$scope', '$uibModalInstance', '$uibModal', '$location', 'Data', 'suivis', function ($scope, $uibModalInstance, $uibModal, $location, Data, suivis) {
	$scope.Data=Data;
	$scope.suivis=suivis;
	$scope.form={};
	$scope.st={nom:'Suivi'};
	$scope.select=function(thread){
		$uibModalInstance.dismiss();
		$location.path('/addsuivi/'+thread.id);
	}
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.ok = function () {
		if ($scope.form.SuivisThread.$valid){
			$uibModalInstance.close($scope.st);
		}
	};
}]);
app.controller('modSuivisThreadModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'thread', function ($scope, $uibModalInstance, $uibModal, thread) {
	$scope.thread=angular.copy(thread);
	$scope.form={};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.ok = function () {
		if ($scope.form.modSuivisThread.$valid){
			$uibModalInstance.close($scope.thread);
		}
	};
}]);
app.controller('modContactModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'contact', function ($scope, $uibModalInstance, $uibModal, contact) {

	$scope.form={};
	$scope.contact=angular.copy(contact);

	$scope.permute= function() {
		var a=$scope.contact.prenom;
		$scope.contact.prenom=$scope.contact.nom;
		$scope.contact.nom=a;
	}
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.ok = function () {
		if ($scope.form.modContact.$valid){
			$uibModalInstance.close($scope.contact);
		}
	};
}]);
app.controller('modSujetModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'sujet', function ($scope, $uibModalInstance, $uibModal, sujet) {

	$scope.form={};
	$scope.sujet=angular.copy(sujet);
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.ok = function () {
		if ($scope.form.modSujet.$valid){
			$uibModalInstance.close($scope.sujet);
		}
	};
}]);
app.controller('modNewsletterModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Data', 'idx', function ($scope, $uibModalInstance, $uibModal, Data, idx) {

	$scope.Data=Data;
	$scope.form={};
	$scope.newsletter=Data.modele.config.config.news.newsletters.value[idx];
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.ok = function () {
		var idx= $scope.newsletter ? $scope.newsletter.nom.id : -1 ;
		$uibModalInstance.close(idx);
	};
}]);
app.controller('modScheduleEnvoiModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Data', 'date', function ($scope, $uibModalInstance, $uibModal, Data, date) {

	$scope.altInputFormats = ['M!/d!/yyyy'];
	$scope.dateOptions = {
		formatYear: 'yy',
		startingDay: 1
	};
	$scope.form={};
	$scope.date=new Date(date);
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.ok = function () {
		var date= $scope.date.getTime();
		$uibModalInstance.close(date);
	};
}]);
app.controller('modCasquetteModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Data', 'cas', 'index', 'bouton', function ($scope, $uibModalInstance, $uibModal, Data, cas, index, bouton) {

	$scope.Data=Data;
	$scope.hasTagList=function(tl,cl){
		var tab=tl.split(',');
		for(var i=0;i<cl.length;i++) {
			if (tab.indexOf(cl[i])>=0) return true;
		};
		return false;
	}
	$scope.descendants=function(tag){
		var tab=[];
		angular.forEach(Data.modele.tags,function(e){
			if (e.id_parent==tag.id) tab.push(e);
		});
		if (tab.length==0) return tab;
		else {
			angular.forEach(tab,function(e){
				tab=tab.concat($scope.descendants(e));
			});
		}
		tab.sort(function(a,b) {
			return $scope.formatDescTag($scope.descTagParent(a,parent.id)).localeCompare($scope.formatDescTag($scope.descTagParent(b,parent.id)));
		});
		return tab;
	}
	$scope.descTagRec=function(tag,id_parent){
		var h=[tag];
		if (tag.id_parent!=id_parent && tag.id_parent!=0){
			angular.forEach($scope.descTagRec(Data.modele.tags[tag.id_parent],id_parent), function(t){
				h.push(t);
			});
		}
		return h;
	};
	$scope.descTag=function(tag){
		var h=$scope.descTagRec(tag,0);
		return h.reverse();
	};
	$scope.descTagParent=function(tag,id_parent){
		var h=$scope.descTagRec(tag,id_parent);
		return h.reverse();
	};
	$scope.formatDescTag=function(t){
		var tab=[];
		angular.forEach(t,function(e){tab.push(e.nom);});
		return tab.join('>');
	};
	$scope.parNomTag = function(tags,parent) {
		var t = angular.copy(tags);
		if (t) {

		}
		return t;
	};
	$scope.tagB={};
	$scope.tagL={};
	if (!cas.tags) cas.tags=[];
	angular.forEach($scope.Data.modele.tags,function(p){
		if (p.type=='boutons') {
			$scope.tagB[p.id]={};
			angular.forEach($scope.descendants(p),function(e){
				if (cas.tags.indexOf(e.id)>=0) $scope.tagB[p.id][e.id]=true;
				else $scope.tagB[p.id][e.id]=false;
			});
		}
	});
	//console.log($scope.tagB);
	angular.forEach($scope.Data.modele.tags,function(p){
		if (p.type=='liste') {
			$scope.tagL[p.id]="0";
			angular.forEach($scope.descendants(p),function(e){
				if (cas.tags.indexOf(e.id)>=0) $scope.tagL[p.id]=e.id;
			});
		}
	});
	//console.log($scope.tagL);
	$scope.bouton=bouton;
	$scope.index=index;
	$scope.addChampPerso=function(){
		var modal = $uibModal.open({
			templateUrl: 'partials/addchamppersomod.html',
			controller: 'addChampPersoModCtl'
		});

		modal.result.then(function (d) {
			$scope.addDonnee(d.type,d.label);
		});
	};

	$scope.delDonnee=function(cas,label){
		var idx=$scope.index('label',cas.donnees,label);
		cas.donnees.splice(idx,1);
	}

	$scope.cas=angular.copy(cas);
	if (!$scope.cas.nom && $scope.cas.type==1) $scope.cas.nom="Perso";
	if (!$scope.cas.nom && $scope.cas.type==2) $scope.cas.nom="Siège";
	$scope.form={};
	$scope.$watch('tagB',function(o,n){
		angular.forEach($scope.tagB,function(p){
			angular.forEach(p,function(e,k){
				var idx=$scope.cas.tags.indexOf(k)
				if (e && idx<0) $scope.cas.tags.push(k);
				if (!e && idx>=0) $scope.cas.tags.splice(idx,1);
			});
		});
	}, true);
	$scope.$watch('tagL',function(o,n){
		angular.forEach($scope.tagL,function(p,k){
			angular.forEach($scope.descendants(Data.modele.tags[k]),function(e){
				var idx=$scope.cas.tags.indexOf(e.id)
				if (p==e.id && idx<0) $scope.cas.tags.push(p);
				if (p!=e.id && idx>=0) $scope.cas.tags.splice(idx,1);
			});
		});
	}, true);
	$scope.ok = function () {
		if ($scope.form.modCas.$valid){
			angular.forEach($scope.cas.donnees,function(d){
				if (d.type=='tel') {
					var tmp=d.value.replace(/ /g,'').replace(/\./g,'');
					var l=tmp.length;
					if (tmp.length>=10) {
						d.value=tmp.substring(0,l-8)+' '+tmp.substring(l-8,l-6)+' '+tmp.substring(l-6,l-4)+' '+tmp.substring(l-4,l-2)+' '+tmp.substring(l-2);
					}
				}
			});
			$uibModalInstance.close($scope.cas);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.addDonnee = function(type,label){
		var d={};
		var i=0;
		var l='';
		var test=true;
		if (label) {
			l=label;
		} else {
			switch(type) {
				case 'tel':
					l='Téléphone';
					break;
				case 'fax':
					l='Fax';
					break;
				case 'email':
					l='E-mail';
					break;
				case 'fonction':
					l='Fonction';
					break;
				case 'civilite':
					l='Civilité';
					break;
				case 'adresse':
					l='Adresse';
					break;
				case 'note':
					l='Note';
					break;
			}
		}
		d.label=l;
		while(test){
			test=false;
			angular.forEach($scope.cas.donnees,function(cd){
				if (cd.label==l) {
					i++;
					l=d.label+' '+i;
					test=true;
				}
			});
		}
		d.label=l;
		switch(type) {
			case 'tel':
				d.type='tel';
				d.value='';
				break;
			case 'fax':
				d.type='tel';
				d.value='';
				break;
				break;
			case 'email':
				d.type='email';
				d.value='';
				break;
			case 'fonction':
				d.type='fonction';
				d.value='';
				break;
			case 'civilite':
				d.type='civilite';
				d.value='';
				break;
			case 'adresse':
				d.type='adresse';
				d.value={adresse:'',cp:'',ville:'',pays:'France'};
				break;
			case 'note':
				d.type='note';
				d.value='';
				break;
			case 'text':
				d.type='text';
				d.value='';
				break;
		}
		$scope.cas.donnees.push(d);
	}
	$scope.hasType = function(type,label){
		var test=false
		if (label) {
			angular.forEach($scope.cas.donnees,function(d){
				if (d.type==type && d.label==label) {
					test=true;
				}
			});
		} else {
			angular.forEach($scope.cas.donnees,function(d){
				if (d.type==type) {
					test=true;
				}
			});
		}
		return test;
	}
}]);
app.controller('envoyerModCtl', ['$scope', '$uibModalInstance', '$uibModal', '$http', 'Link', 'Data', 'parsed', 'type', function ($scope, $uibModalInstance, $uibModal, $http, Link, Data, parsed, type) {
	$scope.parsed=parsed;
	$scope.type=type;
	$scope.res={};
	$scope.Data=Data;
	$scope.res.expediteur=Data.modele.config.config.mailing.expediteurs.value[0];
	$scope.page={courante:1};
	$scope.itemsParPage=10;
	$scope.maxSize=5;
	$scope.min=function(a,b){
		return Math.min(a,b);
	};
	$scope.fullQuery=function(){
		if ($scope.type=='mail') return Data.mainQuery=='' ? ':email' : '('+Data.mainQuery+')&:email';
		if ($scope.type=='adresse') return Data.mainQuery=='' ? ':adresse' : '('+Data.mainQuery+')&:adresse';
		return Data.mainQuery;
	}
	Link.context([{type:'casquettes',params:{query:$scope.parsed.back($scope.fullQuery()),page:$scope.page.courante,nb:$scope.itemsParPage}},{type:'panier'},{type:'tags'}]);
	$scope.$watch('page.courante',function(o,n){
		if (o!=n) $scope.getPage($scope.page.courante);
	});
	$scope.addPanier=function(nouveaux){
		Link.ajax([{action:'addPanier', params:{nouveaux:nouveaux}}]);
	};
	$scope.delPanier=function(nouveaux){
		Link.ajax([{action:'delPanier', params:{nouveaux:nouveaux}}]);
	};
	$scope.panierAdd=function(cas){
		var nouveaux=[cas.id];
		Data.modele.panier.push(cas.id);
		$scope.addPanier(nouveaux);
	};
	$scope.panierDel=function(cas){
		var nouveaux=[cas.id];
		Data.modele.panier.splice(Data.modele.panier.indexOf(cas.id),1);
		$scope.delPanier(nouveaux);
	};
	$scope.dansPanier=function(cas){
		if (Data.user.id>0 && Data.modele.panier) {
			return Data.modele.panier.indexOf(cas.id)>=0;
		}
	};
	$scope.getPage=function(page){
		Link.context([{type:'casquettes', params:{page:page, nb:$scope.itemsParPage, query:$scope.parsed.back($scope.fullQuery())}},{type:'panier'},{type:'tags'}]);
	}
	$scope.ok = function () {
		$scope.res.query=$scope.parsed.back($scope.fullQuery());
		$uibModalInstance.close($scope.res);
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('modSelectionModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Data', 'parsed', 'modSel', 'bouton', function ($scope, $uibModalInstance, $uibModal, Data, parsed, modSel, bouton) {
	$scope.parsed=parsed;
	$scope.modSel=modSel;
	$scope.Data=Data;
	$scope.bouton=bouton;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.modSel.$valid){
			$uibModalInstance.close($scope.modSel);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('modTagModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Link', 'Data', 'moveok', 'movTag', 'descTag', 'modTag', 'bouton', function ($scope, $uibModalInstance, $uibModal, Link, Data, moveok, movTag, descTag, modTag, bouton) {
	$scope.Data=Data;
	$scope.moveok=moveok;
	$scope.movTag=movTag;
	$scope.descTag=descTag;
	$scope.modTag=modTag;
	$scope.assTag=function(){
		var modal = $uibModal.open({
			templateUrl: 'partials/movtag.html',
			controller: 'movTagModCtl',
			resolve: {
				moveok: function () {
					return $scope.moveok;
				},
				modTag: function () {
					return $scope.modTag;
				}
			}
		});
		modal.result.then(function (tag) {
			$scope.movTag($scope.modTag,tag);
		});
	}
	if (!$scope.modTag.color) $scope.modTag.color='#333333';
	$scope.bouton=bouton;
	$scope.form={};
	$scope.descendantSpecial=function (tag) {
		var tab=[];
		angular.forEach(Data.modele.tags,function(t){
			if (t.id_parent==tag.id) tab.push(t);
		});
		if (tab.length==0) return false;
		else {
			var res=false;
			angular.forEach(tab,function(e){
				if (e.type) res=true;
				else res=res || $scope.descendantSpecial(e);
			});
			return res;
		}
	};
	$scope.ancestorSpecial=function (tag) {
		if (tag.id_parent==0) return false;
		else {
			var p=Data.modele.tags[tag.id_parent];
			if (p.type) return true;
			else return $scope.ancestorSpecial(Data.modele.tags[p.id]);
		}
	};
	$scope.hasSpecial=function (cas) {
		angular.forEach(cas.tags,function(id){Data.modele.tags[id]
			if ($scope.ancestorSpecial(Data.modele.tags[id])) return true;
		});
		return false;
	};
	$scope.ok = function () {
		if ($scope.form.modTag.$valid){
			$uibModalInstance.close($scope.modTag);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('modNomCatModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'nomCat', 'bouton', function ($scope, $uibModalInstance, $uibModal, nomCat, bouton) {
	$scope.nomCat=nomCat;
	$scope.bouton=bouton;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.nomCat.$valid){
			$uibModalInstance.close($scope.nomCat);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('modMessageModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'message', function ($scope, $uibModalInstance, $uibModal, message) {
	$scope.m=message;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.modMessage.$valid){
			$uibModalInstance.close($scope.m);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('addContactModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'contact', 'Data', 'Link', function ($scope, $uibModalInstance, $uibModal, contact, Data, Link) {
	Link.context([{type:'check_nom', params:{page:1, nb:$scope.itemsParPage, query:''}}]);
	$scope.page={courante:1};
	$scope.query='';
	$scope.itemsParPage=10;
	$scope.maxSize=5;
	$scope.contact=contact;
	$scope.Data=Data;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.addContact.$valid){
			$uibModalInstance.close($scope.contact);
		}
	};
	$scope.getPage=function(page){
		Link.context([{type:'check_nom', params:{page:page, nb:$scope.itemsParPage, query:$scope.query}}]);
	}
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.$watch('contact.nom',debounce(function(n,o){
		if (o!=n) {
			$scope.query=$scope.contact.nom ? '::text/'+$scope.contact.nom+'*:: AND ::type/'+contact.type+'::' : '';
			$scope.getPage($scope.page.courante);
		}
	},300));
	$scope.$watch('page.courante',function(o,n){
		if (o!=n) $scope.getPage($scope.page.courante);
	});

}]);
app.controller('addNewsModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'news', function ($scope, $uibModalInstance, $uibModal, news) {
	$scope.news=news;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.addNews.$valid){
			$uibModalInstance.close($scope.news);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('modBlocModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'bloc', 'pjs', 'Data', 'trust', function ($scope, $uibModalInstance, $uibModal, bloc, pjs, Data, trust) {
	$scope.Data=Data;
	$scope.bloc=bloc;
	$scope.pjs=pjs;
	$scope.trust=trust;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.modBloc.$valid){
			$uibModalInstance.close($scope.bloc);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.editorOptions = {
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
	$scope.addSchema=function(d){
		var s=angular.copy(d.schema);
		d.valeur.push(s);
	};
	$scope.drop = function(e,s,d,c,list){
		if (c=="listorder") {
			console.log(s.idx,d);
			list.splice(d,0,angular.copy(list[s.idx-1]));
			if (s.idx-1>d)
				list.splice(s.idx,1);
			else
				list.splice(s.idx-1,1)
		}
	};
	$scope.validate=function(a,b,c){
		console.log(a,b,c)
		return a.listid==c;
	}
}]);
app.controller('addModeleModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'modele', function ($scope, $uibModalInstance, $uibModal, modele) {
	$scope.modele=modele;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.addModele.$valid){
			$uibModalInstance.close($scope.modele);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('addMailModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'mail', function ($scope, $uibModalInstance, $uibModal, mail) {
	$scope.mail=mail;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.addMail.$valid){
			$uibModalInstance.close($scope.mail);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('addSupportModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'support', function ($scope, $uibModalInstance, $uibModal, support) {
	$scope.support=support;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.addSupport.$valid){
			$uibModalInstance.close($scope.support);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('addTemplateModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'template', function ($scope, $uibModalInstance, $uibModal, template) {
	$scope.template=template;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.addTemplate.$valid){
			$uibModalInstance.close($scope.template);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('assEtablissementModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Link', 'Data', 'cas', 'index', 'parsed', function ($scope, $uibModalInstance, $uibModal, Link, Data, cas, index, parsed) {
	$scope.parsed=parsed;
	$scope.Data=Data;
	$scope.cas=cas;
	$scope.index=index;
	$scope.page={courante:1};
	$scope.itemsParPage=10;
	$scope.maxSize=5;
	$scope.query='';
	$scope.min=function(a,b){
		return Math.min(a,b);
	};
	$scope.$watch('page.courante',function(o,n){
		if (o!=n) $scope.getPage($scope.page.courante);
	});
	$scope.getPage=function(page){
		Link.context([{type:'etabs', params:{page:page, nb:$scope.itemsParPage, query:$scope.parsed.back($scope.query) + ' AND ::type/2::'}}]);
	}
	$scope.assEtablissement = function (e) {
		$scope.cas.id_etab=e.id;
		$scope.cas.nom_cas=e.nom;
		$uibModalInstance.close($scope.cas);
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.getPage(1);
}]);
app.controller('assCasquetteModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Link', 'Data', 'parsed', function ($scope, $uibModalInstance, $uibModal, Link, Data, parsed) {
	$scope.parsed=parsed;
	$scope.Data=Data;
	$scope.page={courante:1};
	$scope.itemsParPage=10;
	$scope.maxSize=5;
	$scope.query='';
	$scope.min=function(a,b){
		return Math.min(a,b);
	};
	$scope.$watch('page.courante',function(o,n){
		if (o!=n) $scope.getPage($scope.page.courante);
	});
	$scope.getPage=function(page){
		Link.context([{type:'casquettes_sel', params:{page:page, nb:$scope.itemsParPage, query:$scope.parsed.back($scope.query)}}]);
	}
	$scope.assCasquette = function (e) {
		$uibModalInstance.close(e);
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.getPage(1);
}]);
app.controller('assIndividuModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Link', 'Data', 'parsed', function ($scope, $uibModalInstance, $uibModal, Link, Data, parsed) {
	$scope.parsed=parsed;
	$scope.Data=Data;
	$scope.page={courante:1};
	$scope.itemsParPage=10;
	$scope.maxSize=5;
	$scope.query='';
	$scope.min=function(a,b){
		return Math.min(a,b);
	};
	$scope.$watch('page.courante',function(o,n){
		if (o!=n) $scope.getPage($scope.page.courante);
	});
	$scope.getPage=function(page){
		Link.context([{type:'casquettes_sel', params:{page:page, nb:$scope.itemsParPage, query:$scope.parsed.back($scope.query) + ' AND ::type/1::'}}]);
	}
	$scope.assCasquette = function (e) {
		$uibModalInstance.close(e);
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.getPage(1);
}]);
app.controller('assTagModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Data',  function ($scope, $uibModalInstance, $uibModal, Data) {
	$scope.Data=Data;
	$scope.descTagRec=function(tag){
		var h=[tag];
		if (tag.id_parent!=0){
			angular.forEach($scope.descTagRec($scope.Data.modele.tags[tag.id_parent]), function(t){
				h.push(t);
			});
		}
		return h;
	};
	$scope.descTag=function(tag){
		var h=$scope.descTagRec(tag);
		return h.reverse();
	};
	$scope.notNull=function(){
		return function( item ) {
			return item.id != 0;
		};
	}
	$scope.assTag = function (tag) {
		$uibModalInstance.close(tag);
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.currentPage=1;
	$scope.itemsParPage=5;
	$scope.maxSize=5;

}]);
app.controller('movTagModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Data', 'moveok', 'modTag',  function ($scope, $uibModalInstance, $uibModal, Data, moveok, modTag) {
	$scope.Data=Data;
	$scope.moveok=moveok;
	$scope.modTag=modTag;
	$scope.descTagRec=function(tag){
		var h=[tag];
		if (tag.id_parent!=0){
			angular.forEach($scope.descTagRec($scope.Data.modele.tags[tag.id_parent]), function(t){
				h.push(t);
			});
		}
		return h;
	};
	$scope.descTag=function(tag){
		var h=$scope.descTagRec(tag);
		return h.reverse();
	};
	$scope.notNull=function(){
		return function( item ) {
			return item.id != 0;
		};
	}
	$scope.assTag = function (tag) {
		$uibModalInstance.close(tag);
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
	$scope.currentPage=1;
	$scope.itemsParPage=5;
	$scope.maxSize=5;

}]);
app.controller('addChampPersoModCtl', ['$scope', '$uibModalInstance', function ($scope, $uibModalInstance) {
	$scope.form={};
	$scope.types=[
		{type:'note',display:'Texte long'},
		{type:'text',display:'Texte court'},
		{type:'tel',display:'Téléphone'},
		{type:'email',display:'E-mail'}
	];
	$scope.d={label:'',type:'text'};
	$scope.ok = function () {
		if ($scope.form.addPerso.$valid){
			$uibModalInstance.close($scope.d);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('addNbContactsModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'Link', 'Data', function ($scope, $uibModalInstance, $uibModal, Link, Data) {
	$scope.Data=Data;
	$scope.choosen=[];
	$scope.liste={txt:'',contacts:[]};
	$scope.delTag=function(id){
		var i=$scope.choosen.indexOf(id);
		$scope.choosen.splice(i,1);
	};
	$scope.descTag=function(tag){
		var h=$scope.descTagRec(tag);
		return h.reverse();
	};
	$scope.descTagRec=function(tag){
		var h=[tag];
		if (tag.id_parent!=0){
			angular.forEach($scope.descTagRec(Data.modele.tags[tag.id_parent]), function(t){
				h.push(t);
			});
		}
		return h;
	};
	$scope.parseTxt=function(){
		var email = /(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/g;
		var emailName = /(\"([^\"]+)\"\s+)?(([^,\n]+)\s+)?\<((([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,})))\>/g;
		var tab;
		while ((tab = emailName.exec($scope.liste.txt)) !== null) {
			var c={nom:tab[4],mail:tab[5]};
			var test=true;
			for (i=0;i<$scope.liste.contacts.length;i++) {
				if ($scope.liste.contacts[i].mail==c.mail) {
					test=false;
					break;
				}
			}
			if (test) $scope.liste.contacts.push(c);
		}
		while ((tab = email.exec($scope.liste.txt)) !== null) {
			var c={nom:'',mail:tab[0]};
			var test=true;
			for (i=0;i<$scope.liste.contacts.length;i++) {
				if ($scope.liste.contacts[i].mail==c.mail) {
					test=false;
					break;
				}
			}
			if (test) $scope.liste.contacts.push(c);
		}
	}
	$scope.assTag=function(cas){
		var modal = $uibModal.open({
			templateUrl: 'partials/asstag.html',
			controller: 'assTagModCtl'
		});

		modal.result.then(function (tag) {
			if ($scope.choosen.indexOf(tag.id)<0) $scope.choosen.push(tag.id)
		});
	}
	$scope.ok = function () {
		$uibModalInstance.close({tags:$scope.choosen,contacts:$scope.liste.contacts});
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
app.controller('addNbCsvModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'FileUploader', 'Link', 'Data', function ($scope, $uibModalInstance, $uibModal, FileUploader, Link, Data) {
	$scope.Data=Data;
	$scope.choosen=[];
	$scope.rows=0;
	$scope.map=[];
	$scope.header=[];
	$scope.hash='';
	$scope.filename='';
	$scope.i=0;
	$scope.uploader = {};
	$scope.help=function(id){
		$uibModal.open({
			templateUrl: 'partials/inc/help_'+id+'.html'
		});
	};
	$scope.reset=function(){
		$scope.rows=0;
		$scope.map=[];
		$scope.hash='';
		$scope.filename='';
		$scope.header=[];
		$scope.i=0;
		$scope.initUploader();
	}
	$scope.precedent=function(){
		if ($scope.i>0) $scope.i--;
	};
	$scope.suivant=function(){
		if ($scope.i+1<$scope.exemples.length) $scope.i++;
	};
	$scope.exemples=[];
	$scope.liste={txt:'',contacts:[]};
	$scope.delTag=function(id){
		var i=$scope.choosen.indexOf(id);
		$scope.choosen.splice(i,1);
	};
	$scope.initUploader=function(){
		$scope.uploader = new FileUploader({
			url: 'upload.php',
			autoUpload:true,
			formData:[{type:'nbcsv'}],
			onSuccessItem: function(item, response, status, headers) {
				if(response.hash){
					console.log(response);
					$scope.hash=response.hash;
					$scope.filename=response.filename;
					$scope.exemples=response.exemples;
					$scope.rows=response.rows;
					$scope.map=response.map;
					$scope.header=response.header;
				}
			},
			queueLimit:1
		});
	}
	$scope.initUploader();
	$scope.descTag=function(tag){
		var h=$scope.descTagRec(tag);
		return h.reverse();
	};
	$scope.descTagRec=function(tag){
		var h=[tag];
		if (tag.id_parent!=0){
			angular.forEach($scope.descTagRec(Data.modele.tags[tag.id_parent]), function(t){
				h.push(t);
			});
		}
		return h;
	};
	$scope.assTag=function(cas){
		var modal = $uibModal.open({
			templateUrl: 'partials/asstag.html',
			controller: 'assTagModCtl'
		});

		modal.result.then(function (tag) {
			if ($scope.choosen.indexOf(tag.id)<0) $scope.choosen.push(tag.id)
		});
	}
	$scope.ok = function () {
		$uibModalInstance.close({tags:$scope.choosen,hash:$scope.hash,map:$scope.map});
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
}]);
