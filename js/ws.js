'use strict';
var fakeWs= angular.module('fakeWs', []);
fakeWs.value('Data', {
	user: {
		firstname: '',
		lastname: '',
		id:-1
	},
	uid:0,
	accept_anonymous:false,
	contexts:[],
	modele:{},
	modeleSrv:{},
	modeleFresh:true,
	max_contexts_length:20,
	offline:false,
	locked:false,
	logged:false,
	startUrl:'/contacts',
	canLink:false
});
fakeWs.factory('Link',['Data', '$rootScope', '$window', '$interval', '$location', '$q', function(Data, $rootScope, $window, $interval, $location, $q) {
	var link={};
	var next=function(){};
	var isEqual=function(a,b){
		return JSON.stringify(angular.copy(a), null, 4)==JSON.stringify(angular.copy(b), null, 4);
	};
	link.retry={};
	link.context=function(contexts,verrous){
		Data.contexts=contexts;
		if (!Data.accept_anonymous) {
			var hasconfig=false, hasverrous=false, haschat=false, haslogged=false, hasusers=false, hasusersall=false, hasgroups=false;
			for(var i=0;i<contexts.length;i++){
				if (contexts[i].type=='config') hasconfig=true;
				if (contexts[i].type=='verrous') hasverrous=true;
				if (contexts[i].type=='chat') haschat=true;
				if (contexts[i].type=='logged') haslogged=true;
				if (contexts[i].type=='users') hasusers=true;
				if (contexts[i].type=='usersall') hasusersall=true;
				if (contexts[i].type=='groups') hasgroups=true;
			}
			if (!hasconfig) Data.contexts.push({type:'config'});
			if (!hasverrous) Data.contexts.push({type:'verrous'});
			if (!haschat) Data.contexts.push({type:'chat'});
			if (!haslogged) Data.contexts.push({type:'logged'});
			if (!hasusers) Data.contexts.push({type:'users'});
			if (!hasusersall) Data.contexts.push({type:'usersall'});
			if (!hasgroups) Data.contexts.push({type:'groups'});
		}
		var actions=[{action:'update_contexts', contexts:Data.contexts}];
		if (verrous) {
			for(var i=0;i<verrous.length;i++){
				actions.push({action:'set_verrou', verrou:verrous[i]});
			}
		}
		if (Data.user && (Data.user.id>0 || Data.accept_anonymous && Data.user.id==-2)) link.ajax(actions);
		else link.ajax([]);
	};
	link.logout=function(){
		link.ajax([{action:'logout'}]);
		$window.location.href=$window.location.origin + angular.element(document).find('base').attr('href');
	};
  	link.ajax=function(data, callback){
		if (Data.canLink) {
			console.log('ajax');
			if (!callback) callback=function(){};
			Data.modeleFresh=false;
			link.ws.send({data:data},
				function(res){
					Data.modeleFresh=true;
					console.log('ajax ok');
					if (res.data.user &&  (res.data.user.id>0 || Data.accept_anonymous && res.data.user.id==-2)) {
						Data.user=res.data.user;
						Data.uid=res.data.uid;
						if (Data.logged==false) $location.path(Data.startUrl);
						Data.logged=true;
					} else {
						console.log('user not logged');
						Data.logged=false;
						Data.user= {
							firstname: '',
							lastname: '',
							id:-1
						};
						Data.uid=0;
						if (Data.accept_anonymous) {
							var data=[{
								action:'public-login',
								params: {}
							}];
							link.ajax(data);
						} else $location.path('/login');
					}
					callback(res.data);
					$rootScope.$broadcast('data-update');
				}
			)
		}
	};
	link.set_verrou = function(verrous) {
		var actions=[];
		for (var i=0; i<verrous.length;i++) {
			verrous[i]=verrous[i];
			actions.push({action:'set_verrou', verrou:verrous[i]});
		}
		link.ajax(actions);
	}
	link.del_verrou = function(key) {
		var verrou=key;
		link.ajax([{action:'del_verrou', type:verrou}]);
	};
	var wsUrl= location.protocol=='https:' ? 'wss://' + location.host + ':' + (parseInt(document.getElementById('ws-port').value)+1000) : 'ws://' + location.host + ':' + document.getElementById('ws-port').value;
	console.log(location.protocol,wsUrl);
	link.ws = new WebSocketR2(wsUrl);
	link.initSocket=function(e){
		console.log("Connection established!");
		console.log('init');
		Data.offline=false;
		Data.canLink=true;
		$rootScope.$broadcast('link-ready');
		if ($location.path()!='/login') Data.startUrl=$location.path();
		link.ajax([],function(){link.context(Data.contexts);});
	}
	link.ws.onopen(link.initSocket);
	link.ws.onreopen(link.initSocket);
	link.ws.onclose(function(){
		Data.offline=true;
		Data.canLink=false;
	});
	link.updateModele=function(k,v){
		var diffSrv= rfc6902.createPatch(Data.modeleSrv[k], v);
		var diff= rfc6902.createPatch(Data.modele[k], v);
		var d=[];
		var dSrv=[];
		var dC=[];
		for(var i=0;i<diffSrv.length;i++) {
			if (diffSrv[i].path.indexOf("$$")==-1 && diffSrv[i].path.indexOf("uuid")==-1) {
				dSrv.push(diffSrv[i]);
			}
		}
		for(var i=0;i<diff.length;i++) {
			if (diff[i].path.indexOf("$$")==-1 && diff[i].path.indexOf("uuid")==-1) {
				dC.push(diff[i]);
			}
		}
		for(var i=0;i<dC.length;i++) {
			var test=false;
			for(var j=0;j<dSrv.length;j++) {
				if (dSrv[j].path.indexOf(dirname(dC[i].path))>=0) test=true;
			}
			if (test) d.push(dC[i]);
		}
		console.log(dSrv,dC);
		console.log(d);
		rfc6902.applyPatch(Data.modele[k],d);
	}
	link.ws.onmessage(function(r) {
		if (r.data.user && (r.data.user.id>0 || Data.accept_anonymous && r.data.user.id==-2)) {
			Data.user=r.data.user;
			Data.uid=r.data.uid;
			if (Data.logged==false) $location.path(Data.startUrl);
			Data.logged=true;
		} else {
			console.log('user not logged');
			Data.logged=false;
			Data.user= {
				firstname: '',
				lastname: '',
				id:-1
			};
			Data.uid=0;
			if (Data.accept_anonymous) {
				var data=[{
					action:'public-login',
					params: {}
				}];
				link.ajax(data);
			} else $location.path('/login');
		}
		if (r.data.user && (r.data.user.id>0 || Data.accept_anonymous && r.data.user.id==-2) && r.data.modele) {
			console.log('data received',r.data.modele);
			var params={};
			var types=['casquettes','carte','cluster','etabs'];
			for(var i=0;i<Data.contexts.length;i++){
				if(types.indexOf(Data.contexts[i].type)>=0) {
					params[Data.contexts[i].type]=Data.contexts[i].params;
					delete params[Data.contexts[i].type].id;
				}
			}
			angular.forEach(r.data.modele, function(v,k){
				if (types.indexOf(k)>=0) {
					//console.log(isEqual(v.params,params[k]),v.params,params[k]);
					if (isEqual(v.params,params[k])) {
						if (!Data.modele[k]) Data.modele[k]=v;
						else {
							link.updateModele(k,v);
						}
						Data.modeleSrv[k]=angular.copy(v);
					}
				} else {
					if (!Data.modele[k]) Data.modele[k]=v;
					else {
						link.updateModele(k,v);
					}
					Data.modeleSrv[k]=angular.copy(v);
				}
				console.log('broadcast','modele-update-'+k);
				$rootScope.$broadcast('modele-update-'+k);
			});
		}
		$rootScope.$broadcast('data-update');
	});
	return link;
}]);
