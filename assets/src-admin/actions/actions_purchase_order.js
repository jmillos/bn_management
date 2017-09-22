import axios from 'axios'

import { FETCH_SEARCH_SUPPLIERS } from './types'
import { AJAX_URL, BONSTER_NONCE } from '../config'

export function fetchSearchSuppliers(term){
    const request = axios.get(
        AJAX_URL, {
            params: {
                action: 'bn_search_suppliers',
                security: BONSTER_NONCE,
                term,
            }
        }
    );

    return dispatch => {
      request.then(response => {
        const items = response.data
        let retItems = []

        items.map(item => {
          retItems.push({ value: item.ID, text: item.post_title })
        })

        dispatch({ type: FETCH_SEARCH_SUPPLIERS, payload: retItems })
      })
    }
}
