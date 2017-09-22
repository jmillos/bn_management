import axios from 'axios'

import { AJAX_URL, SHIPPING_NONCE } from '../config'
import { FETCH_SEARCH_PRODUCTS } from './types'

export function fetchSearchProducts(term, nonce) {
    const request = axios.get(
      AJAX_URL, {
        params: {
          action: 'woocommerce_json_search_products',
          security: nonce,
          term,
        }
      }
    );

    return dispatch => {
      request.then(response => {
        const items = response.data
        let retItems = []

        Object.keys(items).forEach(key => {
          const name = items[key].replace('&ndash;', '-')
          retItems.push({ value: key, text: name })
        })

        dispatch({ type: FETCH_SEARCH_PRODUCTS, payload: retItems })
      })
    }
}
