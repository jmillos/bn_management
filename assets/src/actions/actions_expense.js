import axios from 'axios'

import { FETCH_SEARCH_DEPARTMENTS, FETCH_SEARCH_SUBDEPARTMENTS } from './types'
import { AJAX_URL, BONSTER_NONCE } from '../config'

export function fetchSearchDepartments(parent){
    const request = axios.get(
        AJAX_URL, {
            params: {
                action: 'bn_search_departments',
                security: BONSTER_NONCE,
                parent,
            }
        }
    );

    return {
      type: parent === 0 ? FETCH_SEARCH_DEPARTMENTS:FETCH_SEARCH_SUBDEPARTMENTS,
      payload: request
    }
}
