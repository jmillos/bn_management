var self;
class FieldNeighborhoodController {
    constructor($element, $timeout) {
    	self = this;
        $timeout(function(){
        	angular.element('#shipping_postcode').attr('readonly', 'readonly');
        	if (jQuery().select2) {
                $element.find('.'+self.args.id).select2({
                	placeholder: self.args.placeholder,
                });
            }
        });
    }

    changeSelect(){
    	angular.element('#shipping_postcode').val( this.inputNeighborhood.postcode );
    	angular.element('#shipping_postcode').trigger('change');
    }
}

export default FieldNeighborhoodController;