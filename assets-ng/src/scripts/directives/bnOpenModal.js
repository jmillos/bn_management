import template from './bnOpenModal.html';
import controller from './bnOpenModal.controller.js';

let bnOpenModal = () => {
  return {
    template,
    restrict: 'E',
  	controller,
  	controllerAs: 'vm',
  };
};

export default bnOpenModal;