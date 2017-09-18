import { combineReducers } from 'redux'
import { reducer as formReducer } from 'redux-form';

import ShippingOrdersReducer from './reducer_shipping_orders'
import PurchaseOrderReducer from './reducer_purchase_order'
import ExpenseReducer from './reducer_expense'
import PayCourierReducer from './reducer_pay_courier'
import WoocommerceReducer from './reducer_woocommerce'

const rootReducer = combineReducers({
    events: ShippingOrdersReducer,
    purchaseOrder: PurchaseOrderReducer,
    expense: ExpenseReducer,
    form: formReducer,
    payCourier: PayCourierReducer,
    woocommerce: WoocommerceReducer,
});

export default rootReducer;
