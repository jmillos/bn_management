import { SEND_PAY_COURIER } from '../actions/types'

export default function( state = { linkExpense: null }, action ){
    switch (action.type) {
        case SEND_PAY_COURIER:
            return { ...state, linkExpense: action.payload.data.link }

        default:
            return { ...state }
    }
}
