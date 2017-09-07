var app= angular.module('contacts', ['ngRoute','ngDragDrop','ui.bootstrap', 'toggle-switch', 'angularFileUpload','ngSanitize','cfp.hotkeys','ngCkeditor','fakeWs','luegg.directives','ngTouch','ngAudio']);

app.config(['$routeProvider', function($routeProvider) {
	$routeProvider.when('/login', {templateUrl: 'partials/login.html', controller: 'loginCtl'});
	//casquettes
	$routeProvider.when('/contacts', {templateUrl: 'partials/contacts.html', controller: 'contactsCtl', hotkeys: [
		['s', 'Ajoute/Enleve le contact au panier', 'sel()'],
		['o', 'Selectionne le contact précédent', 'up()'],
		['l', 'Selectionne le contact suivant', 'down()'],
		['k', 'Page précédente', 'prev()'],
		['m', 'Page suivante', 'next()']
	]});
	$routeProvider.when('/modcontact/:id', {templateUrl: 'partials/modcontact.html', controller: 'modcontactCtl'});
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
	//suivis
	$routeProvider.when('/modsuivi/:id', {templateUrl: 'partials/modsuivi.html', controller: 'modsuiviCtl'});
	$routeProvider.when('/addsuivi/:id/:id_suivi', {templateUrl: 'partials/addsuivi.html', controller: 'addsuiviCtl'});
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
app.controller('mainCtl', ['$scope', '$http', '$location', '$timeout', '$interval', '$uibModal', '$q', '$window', '$sce', 'Link', 'Data', 'ngAudio', function ($scope, $http, $location, $timeout, $interval, $uibModal, $q, $window, $sce, Link, Data, ngAudio) {
	Data.mainQuery='';
	Data.pageContacts=1;
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
	$scope.sound = ngAudio.load("img/sonar.mp3");
	$scope.query_history={c:-1,tab:[]};
	$scope.Data=Data;
	$scope.params='test';
	$scope.done=false;
	$scope.brand='';
	$scope.tagsOpen=[];
	$scope.panier=[];
	$scope.scroll=0;
	$scope.filtre={suivis:0,tags:{}};
	$scope.total={};
	$scope.total.casquettes=0;
	$scope.selected={index:0};
	$scope.ts={};
	$scope.tt={};
	$scope.tabs={};
	$scope.tabs.admin={};
	$scope.pageCourante={};
	$scope.pageCourante.tags={};
	$scope.pageCourante.envois=1;
	$scope.pageCourante.news=1;
	$scope.pageCourante.mails=1;
	$scope.afterLogin='';
	$scope.pageCourante.suivis={};
	$scope.pageCourante.suivis.prochains=1;
	$scope.pageCourante.suivis.retard=1;
	$scope.pageCourante.suivis.termines=1;
	Data.pageContacts=1;
	$scope.initScroll=0;
	$scope.parser={};
	$scope.path=function(){return $location.path();}
	$scope.$watch('Data.user.id',function(){
		if (Data.user.id>=0){
			Link.init();
		 }
	});
	$scope.requete=function(data,callback){
		$http.post('ajax.php',data).then(function(msg){
			if (msg.data.auth) {
				callback(msg.data);
			} else {
				$scope.afterLogin=$location.path();
				$location.path('/login');		
				Data.user.id=-1;		
			}
		});
	};
	$scope.ajax=function(data,callback){
		if (!data) var data={};
		$http.post('ajax.php',data).then(function(msg){
			if (msg.data.auth) {
				if (callback) callback(msg.data);
			} else {
				$scope.afterLogin=$location.path();
				$location.path('/login');		
				Data.user.id=-1;		
			}
		});
	}
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
	$scope.isAncestor=function(tag,ancestor){
		if (tag.id_parent==0 && tag.id!=ancestor.id) return false;
		else if (tag.id_parent==ancestor.id || tag.id==ancestor.id) return true;
		else return $scope.isAncestor(Data.modele.tags[tag.id_parent],ancestor);
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
		Data.modele.panier.push(cas.id);
		$scope.addPanier(nouveaux);
		var i = (typeof i !== 'undefined') ? i : -1;
		if (i>=0) $scope.selected.index=i;
	};
	$scope.panierDel=function(cas,i){
		var nouveaux=[cas.id];
		Data.modele.panier.splice(Data.modele.panier.indexOf(cas.id),1);
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
	$scope.descTagRec=function(tag){
		var h=[tag];
		if (tag.id_parent!=0){
			angular.forEach($scope.descTagRec(Data.modele.tags[tag.id_parent]), function(t){
				h.push(t);
			});
		}
		return h;
	};
	$scope.descTag=function(tag){
		var h=$scope.descTagRec(tag);
		return h.reverse();
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
				return tagA.nom.localeCompare(tagB.nom);
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
	$scope.requete({uid:Data.uid, data:{}},function(data){
		Data.user=data.user;
	});
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
app.controller('loginCtl', ['$scope', '$http', '$location', 'Link', 'Data', function ($scope, $http, $location, Link, Data) {
	if (Data.user.id==-1) {
		$scope.Data=Data;
		$scope.msgtxt='';
		$scope.login=function(){
			var data={
				verb:'login',
				login:$scope.user.login,
				password:$scope.user.password,
				uid:Data.uid,
				data:null
			};
			$scope.requete(data,function(data){
				Data.logged=true;
				Data.user=data.user;
				$scope.canLink=false;
				Link.init();
				if ($scope.afterLogin!='/login') $location.path($scope.afterLogin);
				else $location.path('/');
			});
		};
	} else {
		$location.path('/');
	}
}]);



//casquettes
app.controller('contactsCtl', ['$scope', '$http', '$location', '$timeout', '$interval', '$window', '$uibModal','Link', 'Data', function ($scope, $http, $location, $timeout, $interval, $window, $uibModal, Link, Data) {
	$scope.Data=Data;
	$scope.panierKey='panier';	
	$scope.itemsParPage=10;
	$scope.itemsParPageTag=20;
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
		if (e.keyCode==38) $scope.historyPrev();
		if (e.keyCode==40) $scope.historyNext();
		if (e.keyCode==13) $scope.getPage(1);
	};
	$scope.normalizedNom = function(tag) {
		return removeDiacritics(tag.nom);
	};
	$scope.help=function(id){
		$uibModal.open({
			templateUrl: 'partials/inc/help_'+id+'.html'
		});
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
		if (Data.mainQuery!='') {
			if(c) {
				txt= '&' + txt;
				Data.mainQuery= '(' + Data.mainQuery + ')' + txt;
			} else {
				txt= '|' + txt;
				Data.mainQuery= Data.mainQuery + txt;
			}
		} else {
			Data.mainQuery= Data.mainQuery + txt;
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
	},1000));
	$scope.$watch('Data.modele.casquettes',function(n,o){
		if (n!=o && Data.modele.casquettes && Data.modele.casquettes.collection[$scope.selected.index]) {
			if ($scope.selected.index<0) $scope.selected.index=$scope.itemsParPage-1;
			if ($scope.selected.index>$scope.itemsParPage-1) $scope.selected.index=0;
			$scope.courant.id=Data.modele.casquettes.collection[$scope.selected.index].id;
			$scope.ajustScroll();
		}
	});
	$scope.$watch('Data.pageContacts',debounce(function(n,o){
		if (n!=o && Data.modele.casquettes && Data.pageContacts>0 && Data.pageContacts<1+(Data.modele.casquettes.total/$scope.itemsParPage)) {
			$scope.getPage();   
			if (n<o && $scope.selected.index==$scope.itemsParPage-1) $scope.selected.index=$scope.itemsParPage-1;
			if (n<o && $scope.selected.index!=$scope.itemsParPage-1) $scope.selected.index=0;
			if (n>o) $scope.selected.index=0;
		}
	},1000));
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
				Data.pageContacts=1;
				$scope.selected.index=0;
				page=1;
			}
			else page=Data.pageContacts;
			Link.context([{type:'casquettes', params:{page:page, nb:$scope.itemsParPage, query:query}},{type:'tags'},{type:'selections'},{type:'panier'}]);
		}
	};
	$scope.delContact=function(cas){
		Link.ajax([{action:'delContact', params:{cas:cas}}]);		
	};
	$scope.delCasquettesPanier=function(cas){
		Link.ajax([{action:'delCasquettesPanier', params:{}}]);		
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
		if (Data.pageContacts<=Data.modele.casquettes.total/$scope.itemsParPage) {
			Data.pageContacts++;
		}
	};
	$scope.prev=function(){
		if (Data.pageContacts>1) {
			Data.pageContacts--;
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
		Link.ajax([{action:'addCasTag', params:{cas:cas, tag:tag}}]);
	};
	$scope.delCasTag = function(tag,cas) {
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

app.controller('modcontactCtl', ['$scope', '$filter', '$http', '$location', '$routeParams', '$uibModal', '$window', 'Link', 'Data', function ($scope, $filter, $http, $location, $routeParams, $uibModal, $window, Link, Data) {
	$scope.key='contact/'+$routeParams.id;
	Link.context([{type:$scope.key},{type:'tags'},{type:'suivis'}]);
	$scope.sv={};
	$scope.ev={};
	$scope.svDesc={};
	$scope.idx=-1;
	$scope.modContact=function(contact){
		Link.ajax([{action:'modContact', params:{id:$routeParams.id, nom:contact.nom, prenom:contact.prenom}},
			{action:'del_verrou',type:$scope.key}]);
	}
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
				Link.context([{type:$scope.key},{type:'tags'},{type:'suivis'}]);
			});
		},function(){
			Link.del_verrou('casquette/'+cas.id);
			Link.context([{type:$scope.key},{type:'tags'},{type:'suivis'}]);
		});
	}
	$scope.assTag=function(cas){
		var modal = $uibModal.open({
			templateUrl: 'partials/asstag.html',
			controller: 'assTagModCtl'
		});

		modal.result.then(function (tag) {
			Link.ajax([{action:'addCasTag', params:{cas:cas, tag:tag}}]);
		});
	}
	$scope.desAssEtablissement = function (cas) {
		cas.id_etab=0;
		Link.ajax([{action:'modCasquette', params:{cas:cas}}]);
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
		if ($filter('toArray')(Data.modele[$scope.key].casquettes).length>1) {
			Link.ajax([{action:'delCasquette', params:{cas:cas}}]);
		}
	}
	$scope.addSuivi=function(cas){
		$location.path('/addsuivi/'+ cas.id +'/0');
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
		Link.ajax([{action:'delCasTag', params:{cas:cas, tag:tag}}]);
	};
}]);
//mailing
app.controller('modmailCtl', ['$scope', '$http', '$location', '$routeParams', '$interval', '$uibModal', 'FileUploader', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $interval, $uibModal, FileUploader, Link, Data) {
	$scope.key='mail/'+$routeParams.id;
	$scope.Data=Data;
	Link.context([{type:$scope.key}],[$scope.key]);
	$scope.editorOptions = {
		language: 'fr'
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
	$scope.envoyer=function(mail){
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
	$scope.envoyer=function(mail){
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
				var tab=m.nom.split('::');
				if (tab.length>1) {
					theme=tab[0];
					if (tmp.indexOf(theme)<0) tmp.push(theme);
				}
			});
			tmp.sort();
			angular.forEach(Data.modele.modeles, function(m){
				var tab=m.nom.split('::');
				if (tab.length==1) {
					theme='Sans thème';
					if (tmp.indexOf(theme)<0) tmp.push(theme);
				}
			});
			var res=[];
			angular.forEach(tmp, function(nom){
				var tab=nom.split('_');
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
		var bloc={id_modele:d.id};
		Data.modele[$scope.key].blocs.splice(i,0,bloc);
		$scope.save();
	};
	$scope.modBlocMod=function(i){
		Link.set_verrou(['newsbloc/'+$routeParams.id+'/'+i]);
		var modal = $uibModal.open({
			templateUrl: 'partials/modblocmod.html',
			controller: 'modBlocModCtl',
			resolve:{
				trust: function () {
					return $scope.trust;
				},
				bloc: function () {
					return angular.copy(Data.modele[$scope.key].blocs[i]);
				},
				pjs: function () {
					return angular.copy(Data.modele[$scope.key].pjs);
				}
			}
		});
		modal.result.then(function (bloc) {
			Data.modele[$scope.key].blocs[i]=bloc;
			Link.ajax([{action:'modNews', params:{news:$scope.prepNews(Data.modele[$scope.key])}},{action:'del_verrou', type:'newsbloc/'+$routeParams.id+'/'+i}]);
		}, function(){Link.del_verrou('newsbloc/'+$routeParams.id+'/'+i);});
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
	$scope.envoyer=function(mail){
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
	$scope.pageCourante={};
	$scope.pageCourante.envois=1;
	$scope.pageCourante.erreur=1;
	Link.context([{type:'envois'}, {type:'imap'}, {type:'casquettes_mail_erreur',params:{page:$scope.pageCourante.erreur,nb:$scope.itemsParPage}}]);	
	$scope.$watch('pageCourante.erreur',function(n,o){
		if (n!=o) Link.context([{type:'envois'}, {type:'imap'}, {type:'casquettes_mail_erreur',params:{page:$scope.pageCourante.erreur,nb:$scope.itemsParPage}}]);
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



//suivis
app.controller('suivisCtl', ['$scope', '$http', '$location', '$uibModal', 'Link', 'Data', function ($scope, $http, $location, $uibModal, Link, Data) {
	Link.context([{type:'suivis'}]);
}]);
app.controller('modsuiviCtl', ['$scope', '$http', '$location', '$routeParams', '$interval', '$sce', '$uibModal', 'FileUploader', 'Link', 'Data', function ($scope, $http, $location, $routeParams, $interval, $sce, $uibModal, FileUploader, Link, Data) {
	$scope.key='suivi/'+$routeParams.id;
	Link.context([{type:'suivi/'+$routeParams.id}]);
	$scope.ev={};
	$scope.test=true;
	$scope.casKey=0;
	$scope.contactKey=0;
	$scope.$watchCollection('Data', function(){
		if ($scope.test && Data.modele[$scope.key]) {
			$scope.update();
			$scope.test=false;
		}
	});
	$scope.update=function(){
		if (Data.modele[$scope.key].statut==0) Link.set_verrou([$scope.key]);
		$scope.casKey=Data.modele[$scope.key].id_casquette;
		$scope.contactKey='contact/'+Data.modele[$scope.key].cas.id_contact;
		Link.context([{type:'suivi/'+$routeParams.id},{type:$scope.contactKey},{type:'tags'},{type:'suivis'}]);
	};
	$scope.sv=false;
	$scope.cas={};
	$scope.showDesc={};
	$scope.editorOptions = {
		language: 'fr',
		toolbar: 'lite',
		height:'300px'
	};
	$scope.del = function() {
		Link.ajax([{action:'delSuivi',params:{id:$routeParams.id}}],function(){
			$location.path('/suivis');
		});
	}
	$scope.save = function() {
		Data.modele[$scope.key].date=new Date(Data.modele[$scope.key].date).getTime();
		Link.ajax([{action:'modSuivi',params:{suivi:Data.modele[$scope.key]}}],$scope.update);
	}
	$scope.next = function() {
		Data.modele[$scope.key].statut=1;
		Data.modele[$scope.key].date=new Date(Data.modele[$scope.key].date).getTime();
		Link.ajax([{action:'modSuivi',params:{suivi:Data.modele[$scope.key]}}], function(){$location.path('/addsuivi/'+ Data.modele[$scope.key].id_casquette+'/'+ $routeParams.id);});
	}
	$scope.close = function() {
		Link.del_verrou($scope.key);
		Data.modele[$scope.key].statut=1;
		Data.modele[$scope.key].date=new Date(Data.modele[$scope.key].date).getTime();
		Link.ajax([{action:'modSuivi',params:{suivi:Data.modele[$scope.key]}}]);
	}
	$scope.open = function() {
		Link.set_verrou([$scope.key]);
		Data.modele[$scope.key].statut=0;
		Data.modele[$scope.key].date=new Date(Data.modele[$scope.key].date).getTime();
		Link.ajax([{action:'modSuivi',params:{suivi:Data.modele[$scope.key]}}]);
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
		if (Data.modele[$scope.key].acl.group.indexOf(g.id)>=0) Link.ajax([{action:'delAcl',params:{type_ressource:'suivis',id_ressource:$routeParams.id,type_acces:'group',id_acces:g.id}}]);
		else Link.ajax([{action:'addAcl',params:{type_ressource:'suivis',id_ressource:$routeParams.id,type_acces:'group',id_acces:g.id,level:3}}]);
	}
	$scope.toggle_acl_user=function(u){
		if (Data.modele[$scope.key].acl.user.indexOf(u.id)>=0) Link.ajax([{action:'delAcl',params:{type_ressource:'suivis',id_ressource:$routeParams.id,type_acces:'user',id_acces:u.id}}]);
		else Link.ajax([{action:'addAcl',params:{type_ressource:'suivis',id_ressource:$routeParams.id,type_acces:'user',id_acces:u.id,level:3}}]);
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
		id_casquette:$routeParams.id,
		id_precedent:$routeParams.id_suivi
	};
	$scope.editorOptions = {
		language: 'fr',
		toolbar: 'lite',
		height:'300px'
	};
	$scope.addSuivi=function(cas){
		$scope.suivi.date=new Date($scope.suivi.date).getTime();
		Link.ajax([{action:'addSuivi',params:{suivi:$scope.suivi}}],function(r){$location.path('/modsuivi/'+ r.res);});
	}
}]);
	

//admin
app.controller('adminCtl', ['$scope', '$http', '$location', 'Link', 'Data', function ($scope, $http, $location, Link, Data) {
	Link.context([{type:'log'}]);
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

app.controller('modContactModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'contact', function ($scope, $uibModalInstance, $uibModal, contact) {

	$scope.form={};
	$scope.contact=angular.copy(contact);
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
app.controller('modCasquetteModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'cas', 'index', 'bouton', function ($scope, $uibModalInstance, $uibModal, cas, index, bouton) {

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
	$scope.hasType = function(type){
		var test=false
		angular.forEach($scope.cas.donnees,function(d){
			if (d.type==type) {
				test=true;
			}
		});
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
app.controller('addContactModCtl', ['$scope', '$uibModalInstance', '$uibModal', 'contact', function ($scope, $uibModalInstance, $uibModal, contact) {
	$scope.contact=contact;
	$scope.form={};
	$scope.ok = function () {
		if ($scope.form.addContact.$valid){
			$uibModalInstance.close($scope.contact);
		}
	};
	$scope.cancel = function () {
		$uibModalInstance.dismiss();
	};
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
		toolbar: 'lite',
		height:'300px'
	};
	
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
		Link.context([{type:'etabs', params:{page:page, nb:$scope.itemsParPage, query:$scope.parsed.back($scope.query)}}]);
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



