import axios from 'axios'
import moment from 'moment'

import { AJAX_URL, BONSTER_NONCE } from '../config'
import { FETCH_EVENTS, FETCH_COURIERS, SET_SHIPPING_ORDER, ASSIGN_COURIER, ASSIGN_MANUAL_ORDER, DELETE_SHIPPING_ORDER } from './types'

export function fetchEvents(orderId, start, end) {
    const request = axios.get(
      AJAX_URL, {
        params: {
          action: 'wc_bonster_shipping_events',
          security: BONSTER_NONCE,
          order_id: orderId,
          start,
          end
        }
      }
    );

    return dispatch => {
      request.then(response => {
        let events = response.data

        events.map((item) => {
          item.start = moment(item.start)
          item.end = moment(item.end)
        })

        dispatch({ type: FETCH_EVENTS, payload: events })
      })
    }
}

export function fetchCouriers(orderId){
  const request = axios.get(
    AJAX_URL, {
      params: {
        action: 'wc_bonster_get_couriers',
        security: BONSTER_NONCE
      }
    }
  );

  return {
    type: FETCH_COURIERS,
    payload: request
  }
}

export function setShippingOrder(shippingOrder){
  return {
    type: SET_SHIPPING_ORDER,
    payload: shippingOrder
  }
}

export function assignCourier(orderId){
  const request = axios.get(
    AJAX_URL, {
      params: {
        action: 'wc_bonster_shipping_assign',
        security: BONSTER_NONCE,
        order_id: orderId
      }
    }
  );

  return {
    type: ASSIGN_COURIER,
    payload: request
  }
}

export function assignManualOrder(orderId, event){
  const request = axios.post(
    AJAX_URL, event, {
      params: {
        action: 'wc_bonster_shipping_assign_manual',
        security: BONSTER_NONCE,
        order_id: orderId
      }
    }
  );

  return dispatch => {
    return new Promise(function(resolve, reject) {
      request.then(response => {
        let event = response.data.eventAssigned

        event.start = moment(event.start)
        event.end = moment(event.end)

        dispatch({ type: ASSIGN_MANUAL_ORDER, payload: event })
        resolve()
      }).catch((error) => reject(error))
    })
  }
}

export function deleteShippingOrder(orderId){
  const request = axios.get(
    AJAX_URL, {
      params: {
        action: 'wc_bonster_delete_shipping',
        security: BONSTER_NONCE,
        order_id: orderId
      }
    }
  );

  return (dispatch, getState) => {
    request.then(response => {
      const state = getState()
      let currentEvents = [ ...state.events.currentEvents ]
      let eventDeleted = _.remove(currentEvents, item => {
        return item.id == orderId
      })
      // console.log('eventDeleted', currentEvents, eventDeleted)

      dispatch({ type: DELETE_SHIPPING_ORDER, payload: currentEvents })
    })
  }
}
