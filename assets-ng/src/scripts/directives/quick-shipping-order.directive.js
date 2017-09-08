import template from './quick-shipping-order.html';
import controller from '../controllers/quick-shipping-order.controller';
import 'script-loader!moment';
import 'style-loader!fullcalendar/dist/fullcalendar.css';
import 'script-loader!fullcalendar';
import 'style-loader!fullcalendar-scheduler/dist/scheduler.css';
import 'script-loader!fullcalendar-scheduler';

/*export default class QuickShippingOrderDirective {
    constructor() {
        this.template = template;
        this.restrict = 'E';
        this.scope = {
        	shippingData: "=bonsShippingData"
        };

        this.controller = controller;
        this.controllerAs = 'vm';
        this.bindToController = true;
    }

    // Directive compile function
    compile() {
		return this.link;
    }

    // Directive link function
    link($scope, $element, $attrs, $controller) {
    	var todayDate = moment().startOf('day');
		var YESTERDAY = todayDate.clone().subtract(1, 'day').format('YYYY-MM-DD');
		var TODAY = todayDate.format('YYYY-MM-DD');
		var TOMORROW = todayDate.clone().add(1, 'day').format('YYYY-MM-DD');

	    var fullcal = $element.find('.wrap-calendar').fullCalendar({
		    defaultView: 'timelineDay',
			resourceAreaWidth: "20%",
			// slotWidth: 20,
			editable: true,
			aspectRatio: 1.85,
			scrollTime: '06:00',
			minTime: "06:00",
			maxTime: "18:30",
			header: {
				left: 'promptResource today prev,next',
				center: 'title',
				right: 'timelineDay,timelineThreeDays,agendaWeek,month'
			},
			views: {
				timelineThreeDays: {
					type: 'timeline',
					duration: { days: 3 }
				}
			},
		    events: [
		        { id: '1', resourceId: 'b', start: TODAY + 'T06:00:00', end: TODAY + 'T06:15:00', title: 'event 1' },
				{ id: '2', resourceId: 'c', start: TODAY + 'T07:00:00', end: TODAY + 'T07:15:00', title: 'event 2' },
				{ id: '4', resourceId: 'a', start: TODAY + 'T11:15:00', end: TODAY + 'T11:30:00', title: 'event 3' },
				{ id: '4', resourceId: 'b', start: TODAY + 'T06:15:00', end: TODAY + 'T06:30:00', title: 'event 4' },
				{ id: '5', resourceId: 'c', start: TODAY + 'T13:30:00', end: TODAY + 'T13:00:00', title: 'event 5' }
		    ],
		    resourceLabelText: 'Mensajeros',
		    resources: [
		        { id: 'a', title: 'Auditorium A' },
				{ id: 'b', title: 'Auditorium B', eventColor: 'green' },
				{ id: 'c', title: 'Auditorium C', eventColor: 'orange' },
		    ]
		    // other options go here...
		});
    	// console.log(fullcal)
    }
}*/

let quickShippingOrder = () => {
  return {
    template,
    restrict: 'E',
    // replace: true,
    scope: {
    	shippingData: "=bonsShippingData",
    	shippingCurrent: "=bonsShippingCurrent",
    	shippingConfig: "=bonsShippingConfig",
    },
  	controller,
  	controllerAs: 'vm',
  	/*link: ($scope, $element, $attrs, $controller) => {
	},*/
	bindToController: true,
  };
};

export default quickShippingOrder;