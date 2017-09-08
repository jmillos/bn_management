var self;
class QuickShippingOrderController {
    constructor($scope, $element, $timeout, $http) {
        //Object.assign(this, $scope['vm']);
        self = this;
        this.$element = $element;
        this.$http = $http;

        if(typeof wc_bonster_admin_meta_boxes === "object" && wc_bonster_admin_meta_boxes.hasOwnProperty('order_id'))
            this.currentOrderId = wc_bonster_admin_meta_boxes.order_id;

        console.log('this', $scope.vm, this);
        this.shippingCurrent = $scope.vm.shippingCurrent;
        this.shippingConfig = $scope.vm.shippingConfig;
        if ($scope.vm.shippingData !== false) {
            this.eventAssigned = $scope.vm.shippingData;
            this.isAssignedCourier = true;
            this.renderCalendar();
        }
    }

    assignCourier() {
        return this.$http({
            method: 'GET',
            url: wc_bonster_admin_meta_boxes.ajax_url,
            params: { action: 'wc_bonster_shipping_assign', security: wc_bonster_admin_meta_boxes.bonster_shipping_nonce, order_id: this.currentOrderId }
        }).success(function(data) {
            console.log(data);
            self.isAssignedCourier = data.success;

            if (data.success === true) {
                self.eventAssigned = data.eventAssigned;
                self.renderCalendar();
            } else {
                self.errorAssigned = true;
                self.renderCalendar();
            }
            // return data.data.toJSON();
        }).
        error(function(data, status) {
            // called asynchronously if an error occurs
            // or server returns response with an error status.
            alert(status);
        });
    }

    renderCalendar() {
        // var todayDate = moment().startOf('day');
        // var YESTERDAY = todayDate.clone().subtract(1, 'day').format('YYYY-MM-DD');
        // var TODAY = todayDate.format('YYYY-MM-DD');
        // var TOMORROW = todayDate.clone().add(1, 'day').format('YYYY-MM-DD');
        var fullcal;
        fullcal = this.$element.find('.wrap-calendar').fullCalendar({
            schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
            defaultView: 'timelineDay',
            resourceAreaWidth: "20%",
            slotWidth: 75,
            slotDuration: '00:60:00',
            editable: false,
            aspectRatio: 1.85,
            scrollTime: '12:00',
            minTime: self.shippingConfig.scheduleCourier.start, //"06:00",
            maxTime: self.shippingConfig.scheduleCourier.end,
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
            events: {
                url: wc_bonster_admin_meta_boxes.ajax_url,
                data: {
                    action: 'wc_bonster_shipping_events',
                    security: wc_bonster_admin_meta_boxes.bonster_shipping_nonce,
                    order_id: this.currentOrderId
                },
                cache: true
            }
            /*{ id: '1', resourceId: 'b', start: TODAY + 'T06:00:00', end: TODAY + 'T06:15:00', title: 'event 1' },
                { id: '2', resourceId: 'c', start: TODAY + 'T07:00:00', end: TODAY + 'T07:15:00', title: 'event 2' },
                { id: '4', resourceId: 'a', start: TODAY + 'T11:15:00', end: TODAY + 'T11:30:00', title: 'event 3' },
                { id: '4', resourceId: 'b', start: TODAY + 'T06:15:00', end: TODAY + 'T06:30:00', title: 'event 4' },
                { id: '5', resourceId: 'c', start: TODAY + 'T13:30:00', end: TODAY + 'T13:00:00', title: 'event 5' }*/
            ,
            resourceLabelText: 'Mensajeros',
            resources: {
                url: wc_bonster_admin_meta_boxes.ajax_url,
                data: {
                    action: 'wc_bonster_get_couriers',
                    security: wc_bonster_admin_meta_boxes.bonster_shipping_nonce,
                }
            },
            selectable: true,
            // selectHelper: true,
            select: function(start, end, jsEvent, view, resource) {
                console.log(start, end, resource);
                // fullcal.fullCalendar("removeEvents", "chunked-helper");
                if(self.isAssignedCourier === false && !self.addedCurrentOrder){
                    fullcal.fullCalendar("renderEvent", { id: self.currentOrderId, resourceId: resource.id, title: self.shippingCurrent.zoneName + ' #' + self.currentOrderId, start: start, end: end, editable: true, color: self.shippingConfig.scheduleCourier.colorCurrentEvent }, true);
                    self.addedCurrentOrder = true
                }
                // fullcal.fullCalendar( 'render' );
            },
            eventRender: function(event, element) {
                if(self.isAssignedCourier === false && event.id === self.currentOrderId){
                    element.on('click', function() {
                        if (confirm('Esta seguro de eliminar ' + event.title + '?')) {
                            fullcal.fullCalendar('removeEvents', event.id);
                            self.addedCurrentOrder = false;
                        }
                    });
                }
            },
            customButtons: {
                promptResource: {
                    text: 'Asignar orden de envio',
                    click: function() {
                        if(self.addedCurrentOrder === true){
                            var events = fullcal.fullCalendar( 'clientEvents', self.currentOrderId );
                            console.log(events)
                            if( Array.isArray(events) && events.length > 0 ){
                                var eventObj = events[0];
                                var resource = fullcal.fullCalendar( 'getResourceById', eventObj.resourceId );
                                var event = {
                                    id: eventObj.id,
                                    resourceId: eventObj.resourceId,
                                    resourceName: resource.title,
                                    start: eventObj.start.format('YYYY-MM-DD HH:mm:ss'),
                                    end: eventObj.end.format('YYYY-MM-DD HH:mm:ss'),
                                };
                                self.sendShippingOrderAssigned(event);
                            }                         
                        }else{

                        }
                    }
                }
            }
            /*resources: [
                { id: '2', title: 'Auditorium A' },
                { id: '3', title: 'Auditorium B', eventColor: 'green' },
                { id: '4', title: 'Auditorium C', eventColor: 'orange' },
            ]*/
            // other options go here...
        })

        // if(typeof this.eventAssigned === "object" && this.eventAssigned.hasOwnProperty('shippingDate'))
        fullcal.fullCalendar('gotoDate', this.shippingCurrent.shippingDate);

        console.log(fullcal)
    }

    sendShippingOrderAssigned(event){
        return this.$http({
            method: 'POST',
            url: wc_bonster_admin_meta_boxes.ajax_url,
            params: { action: 'wc_bonster_shipping_assign_manual', security: wc_bonster_admin_meta_boxes.bonster_shipping_nonce, order_id: this.currentOrderId },
            data: event,
        }).success(function(data) {
            self.isAssignedCourier = data.success;

            if (data.success === true) {
                self.eventAssigned = data.eventAssigned;
                self.errorAssigned = false;                
                angular.element('#TB_closeWindowButton').click();
            } else {
                self.errorAssigned = true;
            }
        }).error(function(data, status) {
            alert(status);
        });
    }
}

export default QuickShippingOrderController;
