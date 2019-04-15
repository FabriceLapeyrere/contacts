app.directive('sticky', ['$timeout', function($timeout){
	return {
		restrict: 'A',
		scope: {
			offset: '@',
			stickyClass: '@'
		},
		link: function($scope, $elem, $attrs){
			$timeout(function(){
				var offsetTop = $scope.offset || 0,
					stickyClass = $scope.stickyClass || '',
					$window = angular.element(window),
					doc = document.documentElement,
					initialPositionStyle = $elem.css('position'),
					stickyLine,
					scrollTop;


				// Set the top offset
				//
				$elem.css('top', offsetTop+'px');


				// Get the sticky line
				//
				function setInitial(){
					stickyLine = $elem[0].offsetTop - offsetTop;
					checkSticky();
				}

				// Check if the window has passed the sticky line
				//
				function checkSticky(){
					scrollTop = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);

					if ( scrollTop >= stickyLine ){
						$elem.addClass(stickyClass);
						$elem.css('position', 'fixed');
					} else {
						$elem.removeClass(stickyClass);
						$elem.css('position', initialPositionStyle);
					}
				}


				// Handle the resize event
				//
				function resize(){
					$elem.css('position', initialPositionStyle);
					$timeout(setInitial);
				}


				// Attach our listeners
				//
				$window.on('scroll', checkSticky);

				setInitial();
			});
		},
	};
}]);
app.directive('onScroll', function($timeout) {
	return {
		scope: {
			onScroll: '&onScroll',
		},
		link: function(scope, element) {
			var scrollDelay = 250,
				scrollThrottleTimeout,
				throttled = false,
				scrollHandler = function() {
					if (!throttled) {
						scope.onScroll();
						throttled = true;
						scrollThrottleTimeout = $timeout(function(){
							throttled = false;
						}, scrollDelay);
					}
				};

			element.on("scroll", scrollHandler);

			scope.$on('$destroy', function() {
				element.off('scroll', scrollHandler);
			});
		}
	};
});
app.directive('autoFocus', function($timeout) {
	return {
		restrict: 'AC',
		link: function(_scope, _element) {
			$timeout(function(){
				_element[0].focus();
		}, 500);
		}
	};
});
app.directive('loading', function($timeout) {
	return {
		restrict: 'A',
		template: "<div class='loader-container' ng-if='wait'><img class='loader' src='img/loader.gif' /></div><div ng-if='!wait' class='cache'><ng-transclude></ng-transclude></div>",
		transclude: true,
		scope:{data:'=', loading:'=', action:'&'},
		link: function(scope, element, attrs) {
			var keys=scope.loading ? scope.loading.split(',') : [];
			scope.first={};
			angular.forEach(keys, function(k,i){
				keys[i]=k.trim();
				scope.first[k.trim()]=true;
			})
			element.children('.cache').removeClass("cache");
			scope.wait=false;
			scope.$watchCollection('data',function(){
				scope.wait=false;
				angular.forEach(keys, function(k){
					if(scope.data.modele[k]===undefined) scope.wait=true;
				})
			})
			scope.testAction=function(k){
				var test=false;
				if (scope.first[k]) {
					var test=true;
					angular.forEach(keys, function(k){
						if(scope.data.modele[k]===undefined) test=false;
					});
				}
				return test;
			}
			angular.forEach(keys, function(k){
				if (scope.testAction(k)) {
					console.log('action');
					scope.action();
					scope.first[k]=false;
				} else {
					console.log(scope.loading,scope.first[k],keys,'register','modele-update-'+k);
					scope.$on('modele-update-'+k, function(event, data){
						if (scope.testAction(k)) {
							console.log('action');
							scope.action();
							scope.first[k]=false;
						}
					});
				}
			});
		}
	};
});
app.directive("deferredCloak", function () {
	return {
		restrict: 'A',
		link: function (scope, element, attrs) {
			attrs.$set("deferredCloak", undefined);
			element.removeClass("deferred-cloak");
		}
	};
});
app.directive('locked', [
	function(){
		return {
			template: "<div ng-class=\"{lockedWrap:data.modele.verrous[key] && data.modele.verrous[key]!=data.uid}\">\n"+
				"<div ng-class=\"{locked:data.modele.verrous[key] && data.modele.verrous[key]!=data.uid}\" ng-transclude></div>\n"+
				"<span ng-if=\"data.modele.verrous[key] && data.modele.verrous[key]!=data.uid\" class='locked-by'>{{data.modele.logged.byUid[data.modele.verrous[key]].name}} modifie ceci ... </span>\n"+
				"</div>\n",
			restrict: 'E',
			scope: {
				data: '=',
				key: '='
			},
			transclude:true
		}
	}
]);
app.directive('ngConfirmClick', [
	function(){
		return {
			restrict: 'A',
			link: function(scope, element, attrs){
				element.bind('click', function(e){
					var message = attrs.ngConfirmMessage;
					var action = attrs.ngConfirmClick;
					if(message && confirm(message)){
						scope.$eval(action);
					}
				});
			}
		}
	}
]);
app.directive('ngAllowTab', [
	function(){
		return {
			restrict: 'A',
			link: function(scope, element, attrs){
				element.bind('keydown', function(e){
					if (e.keyCode == 9) { // tab
						var input = this.value; // as shown, `this` would also be textarea, just like e.target
						var remove = e.shiftKey;
						var posstart = this.selectionStart;
						var posend = this.selectionEnd;
						// if anything has been selected, add one tab in front of any line in the selection
						if (posstart != posend) {
							posstart = input.lastIndexOf('\n', posstart) + 1;
							var compensateForNewline = input[posend-1] == '\n';
							var before = input.substring(0,posstart);
							var after = input.substring(posend-(compensateForNewline?1:0));
							var selection = input.substring(posstart,posend);

							// now add or remove tabs at the start of each selected line, depending on shift key state
							// note: this might not work so good on mobile, as shiftKey is a little unreliable...
							if (remove) {
								if (selection[0] == '\t') selection = selection.substring(1);
								selection = selection.split('\n\t').join('\n');
							} else {
								selection = selection.split('\n');
								if (compensateForNewline) selection.pop();
								selection = '\t'+selection.join('\n\t');
							}

							// put it all back in...
							this.value = before+selection+after;
							// reselect area
							this.selectionStart = posstart;
							this.selectionEnd = posstart + selection.length;
						} else {
							var val = this.value;
							this.value = val.substring(0,posstart) + '\t' + val.substring(posstart);
							this.selectionEnd = element.selectionStart = posstart + 1;
						}
						e.preventDefault(); // dont jump. unfortunately, also/still doesnt insert the tab.
					}
				});
			}
		}
	}
]);
app.directive('dynTpl', ['$compile',
	function($compile){
		return {
			restrict: 'A',
			scope: {
				tpl:'=',
				data:'='
			},
			link: function(scope,element,attrs) {
				scope.$watch('tpl',function(){
					element.html(scope.tpl);
					$compile(element.contents())(scope);
				});
			}
		};
	}
]);
app.directive('mobDblclick',
	function () {

		const DblClickInterval = 300; //milliseconds

		var firstClickTime;
		var waitingSecondClick = false;

		return {
			restrict: 'A',
			link: function (scope, element, attrs) {
				element.bind('click', function (e) {

					if (!waitingSecondClick) {
						firstClickTime = (new Date()).getTime();
						waitingSecondClick = true;

						setTimeout(function () {
							waitingSecondClick = false;
						}, DblClickInterval);
					}
					else {
						waitingSecondClick = false;

						var time = (new Date()).getTime();
						if (time - firstClickTime < DblClickInterval) {
							scope.$apply(attrs.mobDblclick);
						}
					}
				});
			}
		};
	});
