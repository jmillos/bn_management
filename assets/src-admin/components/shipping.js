import React, { Component } from 'react'
import {connect} from 'react-redux'
import _ from 'lodash'

import Timeline from 'react-calendar-timeline/lib'
import moment from 'moment'

import * as actions from '../actions/actions_shipping'

import Modal from './common/modal'

moment.locale('es');

class Shipping extends Component {
    constructor(props) {
        super(props);

        this.orderId = wc_bonster_admin_meta_boxes.order_id

        /*const groups = [
                {id: '1', title: 'group 1'},
                {id: '2', title: 'group 2'},
                {id: '3', title: 'group 3'},
                {id: '4', title: 'group 4'},
                {id: '5', title: 'group 5'},
            ]
            const items = [
            {id: 1, group: '1', title: 'item 1', startprops: moment([2017, 1, 25, 8,]), endprops: moment().add(1, 'hour')},
            {id: 2, group: '2', title: 'item 2', startprops: moment().add(-0.5, 'hour'), endprops: moment().add(0.5, 'hour')},
            {id: 3, group: '1', title: 'item 3', startprops: moment([2017, 1, 25, 8,]), endprops: moment().add(1, 'hour')}
        ]

        this.state = {
        couriers: [...groups],
        events: [...items]
        };*/

        this.onItemMove = this.onItemMove.bind(this)
        this.onAddEvent = this.onAddEvent.bind(this);
        this.onDeleteEvent = this.onDeleteEvent.bind(this);
        console.log('props', this.props);
    }

    componentWillMount(){
        this.props.setShippingOrder(this.props.current)
        this.props.fetchCouriers()
    }

    showModal(){
        this.refs.modal.show();

        // const tm = this.refs.timeline
        this.refs.timeline.resize()
        this.refs.timeline.onScroll()
        // tm.showPeriod(moment(tm.state.visibleTimeStart), 'hour')
        // this.refs.timeline.props.onTimeChange(tm.state.visibleTimeStart, tm.state.visibleTimeEnd, tm.updateScrollCanvas)
    }

    onItemMove(itemId, dragTime, newGroupOrder){
        console.log('onItemMove', itemId, moment(dragTime).format('YYYY-MM-DD HH:mm:ss'), newGroupOrder);
        const { couriers, shippingOrder } = this.props
        if(!couriers[newGroupOrder]){
            return false
        }

        const groupId = couriers[newGroupOrder].id
        const event = {
            ...shippingOrder,
            group: groupId,
            start: moment(dragTime).format('YYYY-MM-DD HH:mm:ss'),
            end: moment(dragTime).add(60/this.props.config.maxShippingByRoute, 'minutes').format('YYYY-MM-DD HH:mm:ss'),
        }

        this.props.assignManualOrder(itemId, event).then(() => {
            let courier = _.find(this.props.couriers, {id: event.group})
            let newShippingOrder = {
                courierId: event.group,
                courierName: courier.title,
                id: event.id,
                shippingDate: event.start,
                zoneName: shippingOrder.zoneName
            }
            this.props.setShippingOrder(newShippingOrder)
        })
    }

    onAddEvent(group, time, e){
        // console.log(group, time)

        const shippingOrder = this.props.shippingOrder

        if(!shippingOrder.manualAssignment){
            return false
        }

        const newEvent = {
            id: `${shippingOrder.id}`,
            group: group.id,
            title: `${shippingOrder.zoneName} #${shippingOrder.id}`,
            start: moment(time).format('YYYY-MM-DD HH:mm:ss'),
            end: moment(time).add(60/this.props.config.maxShippingByRoute, 'minutes').format('YYYY-MM-DD HH:mm:ss'),
            className: 'current-order',
            canMove: true,
            canResize: true,
            canChangeGroup: true,
        }

        console.log('newEvent', newEvent);
        this.props.assignManualOrder(shippingOrder.id, newEvent).then(() => {
            let courier = _.find(this.props.couriers, {id: newEvent.group})
            let newShippingOrder = {
                courierId: newEvent.group,
                courierName: courier.title,
                id: newEvent.id,
                shippingDate: newEvent.start,
                zoneName: shippingOrder.zoneName
            }
            this.props.setShippingOrder(newShippingOrder)
        })
    }

    onDeleteEvent(itemId, e){
        if(this.props.shippingOrder.id === itemId && confirm(`Esta seguro de eliminar #${this.props.shippingOrder.id}?`)){
            this.props.deleteShippingOrder(itemId)
        }
    }

    render() {
        const shippingOrder = this.props.shippingOrder
        const style = { position: 'fixed', top: 0, bottom: 0, left: 0, right: 0, backgroundColor: 'white', zIndex: 99999 }

        return (
            <div>
                <Modal ref="modal">
                    <Timeline ref="timeline"
                        groups={this.props.couriers}
                        items={this.props.events}
                        dragSnap={5*60*1000}
                        defaultTimeStart={moment(this.props.current.shippingDate).startOf('hour').toDate()}
                        defaultTimeEnd={moment(this.props.current.shippingDate).startOf('hour').add(1, 'hour').toDate()}
                        // fixedHeader='fixed'
                        sidebarContent={<span>Mensajeros</span>}
                        fullUpdate={true}
                        keys={{
                            groupIdKey: 'id',
                            groupTitleKey: 'title',
                            itemIdKey: 'id',
                            itemTitleKey: 'title',    // key for item div content
                            itemDivTitleKey: 'title', // key for item div title (<div title="text"/>)
                            itemGroupKey: 'group',
                            itemTimeStartKey: 'start',
                            itemTimeEndKey: 'end'
                        }}
                        canMove={false} // defaults
                        onItemMove={this.onItemMove}
                        // canSelect={true}
                        // itemsSorted={true}
                        // itemTouchSendsClick={false}
                        stackItems={true}
                        // useResizeHandle={true}
                        onCanvasDoubleClick={this.onAddEvent}
                        onItemDoubleClick={this.onDeleteEvent}
                        onTimeChange={(visibleTimeStart, visibleTimeEnd, updateScrollCanvas) => {
                            console.log('onTimeChange', moment(visibleTimeStart).format(), moment(visibleTimeEnd).format())
                            updateScrollCanvas(visibleTimeStart, visibleTimeEnd)
                            this.props.fetchEvents(
                                this.orderId,
                                moment(visibleTimeStart).format('YYYY-MM-DD HH:mm:ss'),
                                moment(visibleTimeEnd).format('YYYY-MM-DD HH:mm:ss')
                            )
                        }} />
                </Modal>

                {/*-------- Message info order assigned -------*/}
                {
                    shippingOrder.courierId ?
                    <div style={{marginBottom: '5px'}}>
                        La orden de envió fue asignada a <b>{shippingOrder.courierName}</b> - <i>{moment(shippingOrder.shippingDate).format('dddd h:mma, MMMM D/YYYY')}</i>
                    </div> : null
                }

                {/*-------- Message order can't be assigned -------*/}
                {
                    shippingOrder.manualAssignment && !shippingOrder.courierId ?
                    <div className="error">La orden de envió no pudo ser asignada, intentelo manualmente.</div> : null
                }

                {/*-------- Button for assigning courier -------*/}
                {
                    shippingOrder.manualAssignment || shippingOrder.courierId ?
                    <button className="page-title-action" type="button" onClick={() => this.showModal()}>Abrir Agenda</button> :
                    <button
                        className="page-title-action"
                        type="button"
                        onClick={() => {
                            this.props.assignCourier(this.orderId).then(() => {
                                this.props.fetchEvents(
                                    this.orderId,
                                    moment(this.props.current.shippingDate).startOf('hour').format('YYYY-MM-DD HH:mm:ss'),
                                    moment(this.props.current.shippingDate).startOf('hour').add(1, 'hour').format('YYYY-MM-DD HH:mm:ss')
                                )
                            })
                        }}>
                        Asignar Mensajero
                    </button>
                }
            </div>
        );
    }
}

function mapStateToProps({ events }) {
    return { couriers: events.couriers, events: events.currentEvents, shippingOrder: events.shippingOrder };
}

export default connect(mapStateToProps, actions)(Shipping);
