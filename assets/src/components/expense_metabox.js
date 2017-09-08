import React, { Component } from 'react'
import {connect} from 'react-redux'
import { bindActionCreators } from 'redux';
import { reduxForm, Field, FieldArray, formValueSelector } from 'redux-form'
import {
  Checkbox,
  DatePicker,
  TimePicker,
  RadioButtonGroup,
  SelectField,
  Slider,
  TextField,
  Toggle,
} from 'redux-form-material-ui'
import MUIAutoComplete from 'material-ui/AutoComplete'
import MenuItem from 'material-ui/MenuItem'
// import NumberFormat from 'react-number-format'

import { fetchSearchSuppliers } from '../actions/actions_purchase_order'
import { fetchSearchDepartments } from '../actions/actions_expense'
import AutoComplete from './common/autocomplete'
import { dateTimeFormat } from '../lib/utils'
import { required } from '../lib/form_validators'

class ExpenseMetabox extends Component {
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
    }

    componentWillMount(){
        this.props.fetchSearchDepartments(0)
        this.props.initialize( this.preFormatData() )
        this.formPost.addEventListener('submit', this.onSubmit.bind(this))
    }

    componentDidMount() {
        this.actionSubmit = this.props.handleSubmit(this.onSubmitLocal.bind(this))
    }

    preFormatData(){
        let { data } = this.props

        if (data) {
          if(data.date){
              data.date = new Date(data.date)
          }

          if (data.department) {
            data.department = parseInt(data.department)
            this.props.fetchSearchDepartments(data.department)
          }

          if (data.subdepartment) {
            data.subdepartment = parseInt(data.subdepartment)
          }

          if (data.value) {
            data.value = parseFloat(data.value)
          }
        }

        /*if (!data || !data.totals_are) {
            data.totals_are = 'exclusive'
        }*/

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
      this.refs.dataInput.value = JSON.stringify(props)
      setTimeout(() => this.btnPublish.click())
    }

    onSearchSuppliers(searchText, dataSource, params){
        console.log(searchText, dataSource, params);
        const { source } = params

        if(source == "change")
            this.props.fetchSearchSuppliers(searchText)
    }

    onChange(e, key, payload){
        console.log(e, key, payload);
        this.props.fetchSearchDepartments(key)
    }

    render(){
        return (
          <div className="expense-metabox form-material">
              <div className="row">
                  <div className="col-md-3">
                      <Field
                        className="input-date"
                        name="date"
                        component={DatePicker}
                        container="inline"
                        autoOk={true}
                        format={null}
                        DateTimeFormat={dateTimeFormat()}
                        locale="es-CO"
                        hintText="Fecha"
                        floatingLabelText="Fecha"
                        validate={required} />
                  </div>

                  <div className="col-md-3">
                      <Field
                        className="input-wrap"
                        name="reference"
                        component={TextField}
                        hintText="Referencia"
                        floatingLabelText="Referencia" />
                  </div>

                  <div className="col-md-3 input-autocomplete">
                      <Field
                        className="input-wrap"
                        name="supplier"
                        component={AutoComplete}
                        hintText="Proveedor"
                        floatingLabelText="Proveedor"
                        openOnFocus
                        filter={MUIAutoComplete.fuzzyFilter}
                        // dataSourceConfig={{ text: 'name', value: 'id' }}
                        onUpdateInput={this.onSearchSuppliers.bind(this)}
                        dataSource={this.props.suppliersFound}
                        withBtnClear={true}
                        validate={required} />
                  </div>
              </div>

              <div className="row">
                  <div className="col-md-3">
                      <Field
                        className="input-wrap"
                        name="department"
                        component={SelectField}
                        onChange={this.onChange.bind(this)}
                        hintText="Departamento"
                        floatingLabelText="Departamento"
                        validate={required}>
                        {
                          this.props.departments.map((item, key) => {
                            return <MenuItem key={key} value={item.ID} primaryText={item.post_title} />
                          })
                        }
                        {/* <MenuItem value="exclusive" primaryText="Impuestos excluidos" /> */}
                        {/* <MenuItem value="inclusive" primaryText="Impuestos incluidos" /> */}
                      </Field>
                  </div>
                  <div className="col-md-3">
                      <Field
                        className="input-wrap"
                        name="subdepartment"
                        component={SelectField}
                        hintText="Sub-Departamento"
                        floatingLabelText="Sub-Departamento"
                        validate={required}>
                        {
                          this.props.subdepartments.map((item, key) => {
                            return <MenuItem key={key} value={item.ID} primaryText={item.post_title} />
                          })
                        }
                      </Field>
                  </div>
                  <div className="col-md-3">
                      <Field
                        className="input-wrap"
                        name="transaction"
                        component={SelectField}
                        hintText="Transacción"
                        floatingLabelText="Transacción"
                        validate={required}>
                          <MenuItem value="Bancolombia" primaryText="Bancolombia" />
                          <MenuItem value="Citibank" primaryText="Citibank" />
                          <MenuItem value="Efectivo" primaryText="Efectivo" />
                          <MenuItem value="Efecty" primaryText="Efecty" />
                      </Field>
                  </div>
                  <div className="col-md-3">
                      <Field
                        name="value"
                        className="input-wrap"
                        component={TextField}
                        floatingLabelText="Valor"
                        hintText="Valor"
                        validate={required} />

                       {/* <NumberFormat
                         className="input-wrap"
                         hintText="Valor"
                         floatingLabelText="Valor"
                         customInput={TextField}
                         thousandSeparator={true}
                         decimalPrecision={2}
                         prefix={'$'} /> */}
                  </div>
              </div>

              <input ref="dataInput" name="dataExpense" type="hidden" />
          </div>
        )
    }
}

ExpenseMetabox = reduxForm({
  form: 'ExpenseMetaboxForm',
})(ExpenseMetabox);

function mapStateToProps(state) {
    return {
        suppliersFound: state.purchaseOrder.suppliersFound,
        departments: state.expense.departments,
        subdepartments: state.expense.subdepartments,
    };
}

function mapDispatchToProps(dispatch) {
  return bindActionCreators({ fetchSearchSuppliers, fetchSearchDepartments }, dispatch);
}

export default connect(mapStateToProps, mapDispatchToProps)(ExpenseMetabox)
