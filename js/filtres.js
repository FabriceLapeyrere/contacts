app.filter('toArray', function () {
  return function (obj) {
    if (!angular.isObject(obj)) return obj;
    var tab=[];
    angular.forEach(obj,function(v,k){
		v.uuid=k;
        if(k!='$$hashKey') tab.push(v);
    })
    return tab;
  };
});
app.filter('suivisCas', function() {
    return function(suivis,idcas) {
        var res=[];
	    angular.forEach(suivis, function(suivi){
		    if (suivi.id_casquette==idcas) res.push(suivi);
	    });
        return res;
    }
});
app.filter('prochains', function() {
    return function(suivis) {
        var res=[];
	var date=new Date().getTime();
        angular.forEach(suivis, function(suivi){
		if (suivi.statut==0 && suivi.date>date-24*3600000) res.push(suivi);
	});
        return res;
    }
});
app.filter('retard', function() {
    return function(suivis) {
        var res=[];
	var date=new Date().getTime();
        angular.forEach(suivis, function(suivi){
		if (suivi.statut==0 && suivi.date<=date-24*3600000) res.push(suivi);
	});
        return res;
    }
});
app.filter('notNote', function() {
    return function(donnees) {
        var res=[];
	    angular.forEach(donnees, function(d){
		    if (d.type!='note') res.push(d);
	    });
        return res;
    }
});
app.filter('termines', function() {
    return function(suivis) {
        var res=[];
	angular.forEach(suivis, function(suivi){
		if (suivi.statut==1) res.push(suivi);
	});
        return res;
    }
});
app.filter('startFrom', function() {
    return function(input, start) {
        start = +start; //parse to int
        return input.slice(start);
    }
});
app.filter('hasDonnee', function() {
    return function(cass) {
	var res=[];
        angular.forEach(cass, function(cas){
		if (cas.donnees.length>0) res.push(cas);
	});
        return res;
    }
});
app.filter('notUsed', function() {
    return function(ms) {
	var res=[];
        angular.forEach(ms, function(m){
		if (!m.used) res.push(m);
	});
        return res;
    }
});
app.filter('sansErreur', function() {
    return function(ms) {
	var res=[];
        angular.forEach(ms, function(m){
		if (m.erreurs=='') res.push(m);
	});
        return res;
    }
});
app.filter('avecErreur', function() {
    return function(ms) {
	var res=[];
        angular.forEach(ms, function(m){
		if (m.erreurs!='') res.push(m);
	});
        return res;
    }
});
app.filter('exists', function() {
    return function(ee) {
	var res=[];
        angular.forEach(ee, function(e){
		if (e.id>0) res.push(e);
	});
        return res;
    }
});
app.filter('hasgroup', function() {
    return function(ee,g) {
	var res=[];
        angular.forEach(ee, function(e){
		if (g==0 || e.groups.indexOf(parseInt(g))>=0) res.push(e);
	});
        return res;
    }
});
app.filter('collaborateurs', function() {
    return function(casquettes,id) {
	var res=[];
        angular.forEach(casquettes, function(c){
		if (c.type==1){
			if (c.id_etab==id) res.push(c);
		}
	});
        return res;
    }
});
app.filter('structures', function() {
    return function(casquettes) {
	var res=[];
        angular.forEach(casquettes, function(c){
		if (c.type==2) res.push(c);
	});
        return res;
    }
});
app.filter('image', function() {
    return function(pjs) {
	var res=[];
        angular.forEach(pjs, function(pj){
		if (pj.mime=="image/png" || pj.mime=="image/jpeg") res.push(pj);
	});
        return res;
    }
});
app.filter('nl2br', function() {
    var span = document.createElement('span');
    return function(input) {
        if (!input) return input;
        var lines = input.split('\n');

        for (var i = 0; i < lines.length; i++) {
            span.innerText = lines[i];
            span.textContent = lines[i];
            lines[i] = span.innerHTML;
        }
        return lines.join('<br />');
    }
});
app.filter('nomCat', function() {
    return function(modeles,cat) {
		var res=[];
		angular.forEach(modeles, function(m){
			var tab=m.nom.split('::');
			if (tab.length>1 && cat==tab[0]) {
				res.push(m);
			}
			if (tab.length==1 && cat=='Sans thème') {
				res.push(m);
			}
		});
        return res;
    }
});
app.filter('nomModele', function() {
    return function(m) {
		var res='';
		var tab=m.split('::');
		if (tab.length>1) {
			res=tab[1];
		}
		if (tab.length==1) {
			res=tab[0];
		}
		return res;
    }
});

