import axios from 'axios'

import { SEND_PAY_COURIER } from './types'
import { API_URL, API_NONCE } from '../config'

axios.defaults.headers.common['X-WP-Nonce'] = API_NONCE;

export function sendPayCourier(orderId, data){
    const request = axios.post(`${API_URL}bn_expenses`, data)

    console.log(orderId, data);

    return {
        type: SEND_PAY_COURIER,
        payload: request
    }
}
