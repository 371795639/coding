var App = angular.module("App",[]);
//验证码
App.directive( "verifyDirective", function() {
    return {
        restrict: "A",
		replace: true,
		template:"<img style='cursor:pointer' title='click to refresh' ng-click='refresh()' src='{{imgSrc}}'/>",
		scope:{
		  },
		 controller:function($scope, $element, $attrs, $transclude) {
		 // 控制器逻辑放在这里
			 //console.log($attrs.imgSrc);
			 $scope.imgSrc=$attrs.imgSrc;
			 $scope.refresh=function(){
				 $scope.imgSrc=$attrs.imgSrc+"/"+Math.random();
			 }
		 }
        }
})