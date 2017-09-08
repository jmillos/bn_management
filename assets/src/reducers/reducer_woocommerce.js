import { FETCH_SEARCH_PRODUCTS } from '../actions/types'

export default function(state = { productsFound: [] }, action){
  switch (action.type) {
    case FETCH_SEARCH_PRODUCTS:
      return { ...state, productsFound: action.payload }

    default:
      return { ...state }
  }
}
