import { FETCH_SEARCH_DEPARTMENTS, FETCH_SEARCH_SUBDEPARTMENTS } from '../actions/types'

export default function( state = { departments: [], subdepartments: [] }, action ){
    switch (action.type) {
        case FETCH_SEARCH_DEPARTMENTS:
            return { ...state, departments: action.payload.data }
            break;

        case FETCH_SEARCH_SUBDEPARTMENTS:
            return { ...state, subdepartments: action.payload.data }
            break;

        default:
            return { ...state }
    }
}
