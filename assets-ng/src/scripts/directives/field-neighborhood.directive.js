import template from './field-neighborhood.html';
import controller from '../controllers/field-neighborhood.controller';

let fieldNeighborhood = () => {
    return {
        template,
        restrict: 'E',
        replace: true,
        scope: {
            'neighborhoods': "=bonsNeighborhoods",
            'args': "=bonsArgs",
        },
        controller,
        controllerAs: 'vm',
        /*link: ($scope, $element, $attrs, $controller) => {
            
        },*/
        bindToController: true,
    };
};

export default fieldNeighborhood;
