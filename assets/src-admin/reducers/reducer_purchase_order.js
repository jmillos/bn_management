import { FETCH_SEARCH_SUPPLIERS } from '../actions/types'

export default function( state = { data: {}, suppliersFound: [] }, action ){
    switch (action.type) {
        case FETCH_SEARCH_SUPPLIERS:
            return { ...state, suppliersFound: action.payload }
            break;

        default:
            return { ...state }
    }
}
