import './sass/bootstrap.scss'
import './sass/main.scss'

import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import injectTapEventPlugin from 'react-tap-event-plugin'
import { Provider } from 'react-redux'
import { createStore, applyMiddleware } from 'redux'
import { composeWithDevTools } from 'redux-devtools-extension'
import ReduxPromise from 'redux-promise'
import ReduxThunk from 'redux-thunk'

import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider'
import getMuiTheme from 'material-ui/styles/getMuiTheme'
injectTapEventPlugin()

import reducers from './reducers'
import Utils from './lib/utils'
import Shipping from './components/shipping'
import PurchaseOrder from './components/purchase_order'
import ExpenseMetabox from './components/expense_metabox'
import PayCourier from './components/pay_courier'

const muiTheme = getMuiTheme({
    zIndex: {
        layer: 99999,
        popover: 99999,
    }
});

const createStoreWithMiddleware = composeWithDevTools( applyMiddleware(ReduxPromise, ReduxThunk) )(createStore);

export const store = createStoreWithMiddleware(reducers)

/*** Shipping ***/
var el = document.querySelector('.shippingScheduleComponent')
if(el){
  const props = Utils.getAttrsFromElement(el)
  ReactDOM.render(
    <Provider store={store}>
      <Shipping {...props} />
    </Provider>
    , el)
}
/***************************************************************/

/*** Purchase Order ***/
var el = document.querySelector('.purchaseOrderComponent')
if(el){
  const props = Utils.getAttrsFromElement(el)
  ReactDOM.render(
    <Provider store={store}>
      <MuiThemeProvider>
        <PurchaseOrder {...props} />
      </MuiThemeProvider>
    </Provider>
    , el)
}
/***************************************************************/

/*** Expense Metabox ***/
var el = document.querySelector('.expenseMainMetaboxComponent')
if(el){
  const props = Utils.getAttrsFromElement(el)
  ReactDOM.render(
    <Provider store={store}>
      <MuiThemeProvider>
        <ExpenseMetabox {...props} />
      </MuiThemeProvider>
    </Provider>, el)
}
/***************************************************************/

/*** Pay to Courier ***/
var els = document.querySelectorAll('.payCourierComponent')
if(els.length > 0){
    for (var i = 0; i < els.length; i++) {
        const el = els[i]

        const props = Utils.getAttrsFromElement(el)
        let storeComponent = createStoreWithMiddleware(reducers)
        ReactDOM.render(
          <Provider store={storeComponent}>
            <MuiThemeProvider muiTheme={muiTheme}>
              <PayCourier {...props} />
            </MuiThemeProvider>
          </Provider>, el)
    }
}
/***************************************************************/
