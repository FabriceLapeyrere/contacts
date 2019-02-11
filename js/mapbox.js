app.directive('mymapbox', ['$rootScope', function($rootScope){
		return {
			restrict: 'A',
			scope: {
				mbLayers:'=',
				mbSources:'='
			},
			link: function(scope,element,attrs) {
				var map = new mapboxgl.Map({
					container: element[0],
					style: 'mapbox://styles/mapbox/streets-v9',
					zoom: 0
				});
				var updateSources=function(){
					angular.forEach(scope.mbSources,function(s,k) {
						//console.log('source',k,s);
						var source=map.getSource(k);
						if (source===undefined) source=map.addSource(k,s);
						else source.setData(s.data);
					});
				};
				var updateLayers=function(){
					for(var i=0;i<scope.mbLayers.length;i++) {
						var l=scope.mbLayers[i];
						var k=l.id;
						var layer=map.getLayer(k);
						//console.log('layer',k,l,layer);
						if (layer===undefined) map.addLayer(l);
					};
				};
				map.on('load', function (event) {
					updateSources();
					updateLayers();
					scope.$watchCollection('mbSources', function(newValue, oldValue) {
							updateSources();
						},
					true);
					scope.$watchCollection('mbLayers', function(newValue, oldValue) {
							updateLayers();
						},
					true);
					$rootScope.$applyAsync($rootScope.$broadcast('mapboxglMap:ready', event));
				});
				map.on('moveend', function (event) {
					$rootScope.$applyAsync($rootScope.$broadcast('mapboxglMap:moveend', event));
				});
				map.on('click', function (event) {
					event.originalEvent.preventDefault();
					event.originalEvent.stopPropagation();
					var features = map.queryRenderedFeatures(event.point, { layers: ['cluster'] });
					if (features.length > 0) {
						var feature = features[0];
						$rootScope.$broadcast('mapboxglMap:featureClick', feature);
					}
				});
				map.on('mousemove', function (event) {
					var features = map.queryRenderedFeatures(event.point, { layers: ['cluster'] });
					map.getCanvas().style.cursor = (features.length) ? 'pointer' : '';
				});
			}
		}
	}
]);

