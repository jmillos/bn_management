import { SEND_PAY_COURIER } from '../actions/types'

export default function( state = { sended: false }, action ){
    switch (action.type) {
        case SEND_PAY_COURIER:
            return { ...state, sended: action.payload }
            break;

        default:
            return { ...state }
    }
}
