import axios from 'axios'

import { SEND_PAY_COURIER } from './types'
import { AJAX_URL, BONSTER_NONCE } from '../config'

export function sendPayCourier(orderId, data){
    const request = axios.post(AJAX_URL, data)

    console.log(orderId, data);

    return {
        type: SEND_PAY_COURIER,
        payload: request
    }
}
