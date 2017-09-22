import { FETCH_EVENTS, FETCH_COURIERS, SET_SHIPPING_ORDER, ASSIGN_COURIER, ASSIGN_MANUAL_ORDER, DELETE_SHIPPING_ORDER } from '../actions/types'

export default function (state = { couriers: [], currentEvents: [], shippingOrder: {courierId: null, courierName: null} }, action){
    switch (action.type) {
        case FETCH_EVENTS:
            return { ...state, currentEvents: [ ...action.payload ] }

        case FETCH_COURIERS:
            return { ...state, couriers: [ ...action.payload.data ] }

        case SET_SHIPPING_ORDER:
            return { ...state, shippingOrder: { ...state.shippingOrder, ...action.payload } }

        case ASSIGN_COURIER:
            return {
              ...state,
              shippingOrder: {
                ...state.shippingOrder,
                ...action.payload.data.eventAssigned,
                manualAssignment: !action.payload.data.success
              }
            }

        case ASSIGN_MANUAL_ORDER:
            return { ...state, currentEvents: [ ...state.currentEvents, action.payload ] }

        case DELETE_SHIPPING_ORDER:
            return { ...state, currentEvents: [ ...action.payload ], shippingOrder: { ...state.shippingOrder, courierId: null, courierName: null } }

        default:
            return state;
    }
}
