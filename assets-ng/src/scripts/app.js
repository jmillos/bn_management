// import 'bootstrap/dist/css/bootstrap.min.css';
import angular from 'angular';
import QuickShippingOrderDirective from './directives/quick-shipping-order.directive';
import FieldNeighborhoodDirective from './directives/field-neighborhood.directive';
import './styles.scss';

angular.module('wcBonsterShipping', [

])
.directive('quickShippingOrder', QuickShippingOrderDirective)
.directive('fieldNeighborhood', FieldNeighborhoodDirective);

angular.element(document).ready(function() {
	var element = "body";
	if(typeof wc_bonster_admin_meta_boxes === "object" && wc_bonster_admin_meta_boxes.hasOwnProperty('element_ngapp_angularjs')){
		element = wc_bonster_admin_meta_boxes.element_ngapp_angularjs;
	}
    angular.bootstrap(angular.element(element), ['wcBonsterShipping']);
});