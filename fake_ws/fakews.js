'use strict';
var fakeWs= angular.module('fakeWs', []);
fakeWs.value('Data', {
	user: {
		firstname: '',
		lastname: '',
		id:document.getElementById('uid').value
	},
	uid: Math.random().toString(36).substr(2, 9),
	contexts:[],
	modele:{},
	modeleSrv:{},
	modeleFresh:true,
	max_contexts_length:20,
	offline:false,
	locked:false,
    logged:false
});
fakeWs.factory('Link',['Data', '$http', '$rootScope', '$window', '$interval', '$location', '$q', function(Data, $http, $rootScope, $window, $interval, $location, $q) {
	var link={};
	var next=function(){};
    link.retry={};
	link.context=function(contexts,verrous,add){
        Data.contexts=contexts;
	    var hasconfig=false, haschat=false, haslogged=false, hasusers=false, hasusersall=false, hasgroups=false;
	    for(var i=0;i<contexts.length;i++){
		    if (contexts[i].type=='config') hasconfig=true;
		    if (contexts[i].type=='chat') haschat=true;
            if (contexts[i].type=='logged') haslogged=true;
            if (contexts[i].type=='users') hasusers=true;
            if (contexts[i].type=='usersall') hasusersall=true;
            if (contexts[i].type=='groups') hasgroups=true;
            if (!contexts[i].params) contexts[i].params={id:Data.user.id};
            else if (!contexts[i].params.id) contexts[i].params.id=Data.user.id;
	    }
        if (!hasconfig) Data.contexts.push({type:'config',params:{id:Data.user.id}});  
        if (!haschat) Data.contexts.push({type:'chat',params:{id:Data.user.id}});
        if (!haslogged) Data.contexts.push({type:'logged',params:{id:Data.user.id}});
        if (!hasusers) Data.contexts.push({type:'users',params:{id:Data.user.id}});
        if (!hasusersall) Data.contexts.push({type:'usersall',params:{id:Data.user.id}});
        if (!hasgroups) Data.contexts.push({type:'groups',params:{id:Data.user.id}});
        var actions=[{action:'update_contexts', contexts:Data.contexts}];
        if (verrous) {
            for(var i=0;i<verrous.length;i++){
                actions.push({action:'set_verrou', verrou:verrous[i]});
            }
        }
        link.ajax(actions);
    };
	link.link=function(){
        if(!Data.locked){
		    Data.locked=true;
            $http.post('link.php',{uid:Data.uid}).then(function(r){
                Data.user=r.data.user;            
                Data.offline=false;
	            if (r.data.modele) {
                    angular.forEach(r.data.modele, function(v,k){
                        Data.modele[k]=v;
                        Data.modeleSrv[k]=angular.copy(v);
                    });
                }
	            Data.locked=false;
	            link.link();
            },function(){
                Data.locked=false;
                Data.offline=true;
            });
        }
	};
    link.logout=function(){
        Data.logged=false;                
        $http.post('ajax.php',{verb:'logout', uid:Data.uid}).then(function(){
            $window.location.href=$window.location.origin + angular.element(document).find('base').attr('href');
		},function(){
            $window.location.href=$window.location.origin + angular.element(document).find('base').attr('href');
        });
    };
  	link.ajax=function(data, callback){
		if (!callback) callback=function(){};
		Data.modeleFresh=false;
		$http.post('ajax.php',{uid:Data.uid, data:data}).then(function(r){
			Data.modeleFresh=true;
			callback(r.data);
            if (r.data.auth) Data.logged=true;
			if (Data.logged && !r.data.auth) {
				link.logout();
			}
		});
	};
	link.check_link=function(){
        if(Data.offline) {
	        link.link();
            link.ajax([{action:'update_contexts', contexts:Data.contexts}]);    
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
	link.init = function(){
		link.link();
		$interval(link.check_link,1000);
		$window.addEventListener('beforeunload',function(){
		    link.ajax([{action:'kill_me',uid:Data.uid}]);
		});
	};
	return link;
}]);
