import React, { Component } from 'react'
import {connect} from 'react-redux'
import { bindActionCreators } from 'redux';
import { reduxForm, Field, FieldArray, formValueSelector } from 'redux-form'

import { fetchSearchProducts } from '../actions'
import { fetchSearchSuppliers } from '../actions/actions_purchase_order'
import { dateTimeFormat } from '../lib/utils'
import { required } from '../lib/form_validators'

import MenuItem from 'material-ui/MenuItem'
// import {RaisedButton} from 'material-ui';
import RaisedButton from 'material-ui/RaisedButton';
import MUIAutoComplete from 'material-ui/AutoComplete'
// import TextField from 'material-ui/TextField';
// import DatePicker from 'material-ui/DatePicker';
import AutoComplete from './common/autocomplete'
import {
  // AutoComplete,
  Checkbox,
  DatePicker,
  TimePicker,
  RadioButtonGroup,
  SelectField,
  Slider,
  TextField,
  Toggle,
} from 'redux-form-material-ui'

import FormTable from './purchase_order/form_table'
import TotalOrder from './purchase_order/total_order'

class PurchaseOrder extends Component {
  constructor(props){
    super(props)

    this.formSuccess = false
    this.formPost = document.getElementById('post')
    this.btnPublish = document.getElementById('publish')
    this.inputTitle = document.getElementById('title')

    if(props.isneworder === false){
        this.inputTitle.readOnly = true
        this.inputTitle.style.backgroundColor = '#ddd'
    }
    // this.onSubmit = this.onSubmit.bind(this)
  }

  componentDidMount() {
    this.actionSubmit = this.props.handleSubmit(this.onSubmitLocal.bind(this))

    // this.refs.reference // the Field
      // .getRenderedComponent() // on Field, returns ReduxFormMaterialUITextField
      // .getRenderedComponent() // on ReduxFormMaterialUITextField, returns TextField
      // .focus() // on TextField
  }

  componentWillMount(){
    this.props.initialize( this.preFormatData() )
    this.formPost.addEventListener('submit', this.onSubmit.bind(this))
  }

  onSearchSuppliers(searchText, dataSource, params){
      console.log(searchText, dataSource, params);
      const { source } = params

      if(source == "change")
          this.props.fetchSearchSuppliers(searchText)
  }

  preFormatData(){
        let { data } = this.props

        if(data && data.stock_due){
            data.stock_due = new Date(data.stock_due)
        }else{
            const date = new Date()
            date.setDate(date.getDate() + 12)
            data.stock_due = date
        }

        if (!data || !data.totals_are) {
            data.totals_are = 'exclusive'
        }

        return data
  }

  onSubmit(e) {
    if(this.formSuccess === false){
        e.preventDefault()
        this.actionSubmit()
    }
  }

  onSubmitLocal(props){
    this.formSuccess = true
    props.items.map(item => {
        item.qty = parseFloat(item.qty)
        item.cost = parseFloat(item.cost)
    })
    console.log(props);
    this.refs.dataInput.value = JSON.stringify(props)
    setTimeout(() => this.btnPublish.click())
  }

  isDisabled(){
      return this.props.orderstatus == 'received' || this.props.orderstatus == 'void' ? true:false
  }

  isEditable(){
    return this.props.orderstatus == 'auto-draft' || this.props.orderstatus == 'draft' ? true:false
  }

  render(){
    const { handleSubmit } = this.props;

    return(
      <div className="purchase-order form-material">
          <div className="row">
            <div className="col-md-3 input-autocomplete">
              <Field
                className="input-wrap"
                name="supplier"
                component={AutoComplete}
                disabled={this.isDisabled()}
                hintText="Proveedor"
                floatingLabelText="Proveedor"
                openOnFocus
                filter={MUIAutoComplete.fuzzyFilter}
                // dataSourceConfig={{ text: 'name', value: 'id' }}
                onUpdateInput={this.onSearchSuppliers.bind(this)}
                dataSource={this.props.suppliersFound}
                /*dataSource={[
                  { value: '1', text: 'Carnes Enriko' },
                  { value: '2', text: 'Pollos Bucanero' }
                ]}*/
                withBtnClear={this.isEditable()}
                validate={required} />
            </div>
            <div className="col-md-3">
              <Field
                className="input-wrap"
                name="reference"
                component={TextField}
                hintText="Referencia"
                disabled={this.isDisabled()}
                floatingLabelText="Referencia" />
            </div>
            <div className="col-md-3">
              <Field
                className="input-date"
                name="stock_due"
                component={DatePicker}
                container="inline"
                autoOk={true}
                format={null}
                DateTimeFormat={dateTimeFormat()}
                locale="es-CO"
                hintText="Fecha de espera estimada"
                floatingLabelText="Fecha de espera estimada"
                disabled={this.isDisabled()}
                validate={required} />
            </div>
            <div className="col-md-3">
              <Field
                className="input-wrap"
                name="totals_are"
                component={SelectField}
                disabled={this.isDisabled()}
                hintText="Totales con"
                floatingLabelText="Totales con"
                validate={required}>
                <MenuItem value="exclusive" primaryText="Impuestos excluidos" />
                <MenuItem value="inclusive" primaryText="Impuestos incluidos" />
              </Field>
            </div>
          </div>
          <div>
            {/* <FormTable items={items} /> */}
            <FieldArray
              name="items"
              component={FormTable}
              disabled={this.isDisabled()}
              productsFound={this.props.productsFound}
              fetchSearchProducts={this.props.fetchSearchProducts} />
          </div>
          <div className="wrap-total-order row justify-content-end">
              <div className="col-md-6">
                  {/* <TotalOrder items={this.props.items} totalsAre={this.props.totalsAre} /> */}
                  <Field name={`total_order`} component={TotalOrder} hintText="Total" items={this.props.items} totalsAre={this.props.totalsAre} />
              </div>
              <input ref="dataInput" name="dataPurchaseOrder" type="hidden" />
            {/* <RaisedButton label="Submit" onClick={handleSubmit(this.onSubmit.bind(this))} /> */}
          </div>
      </div>
    )
  }
}

const selector = formValueSelector('PurchaseOrderForm')
PurchaseOrder = reduxForm({
  form: 'PurchaseOrderForm',
  // initialValues: {
  //   items: [
  //     {
  //       qty: 1,
  //       tax: 16,
  //       total: 0,
  //       name: '{"value":"93","text":"#93 - Fresa"}',
  //       cost: '1234'
  //     }
  //   ],
  //   supplier: '{"value":"1","text":"Carnes Enriko"}',
  //   stock_due: new Date('2017-07-20T05:00:00.000Z'),
  //   totals_are: 'inclusive'
  // }
})(PurchaseOrder);

function mapStateToProps(state) {
    return {
      productsFound: state.woocommerce.productsFound,
      suppliersFound: state.purchaseOrder.suppliersFound,
      items: selector(state, 'items'),
      totalsAre: selector(state, 'totals_are')
    };
}

function mapDispatchToProps(dispatch) {
  return bindActionCreators({ fetchSearchProducts, fetchSearchSuppliers }, dispatch);
}

export default connect(mapStateToProps, mapDispatchToProps)(PurchaseOrder)
