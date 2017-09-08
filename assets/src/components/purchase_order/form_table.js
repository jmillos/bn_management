import React, { Component } from 'react'
import {Field} from 'redux-form'
import FlatButton from 'material-ui/FlatButton'
import ActionAdd from 'material-ui/svg-icons/content/add';
import MUIAutoComplete from 'material-ui/AutoComplete'
import {
    AutoComplete,
    Checkbox,
    DatePicker,
    TimePicker,
    RadioButtonGroup,
    SelectField,
    Slider,
    TextField,
    Toggle
} from 'redux-form-material-ui'

import {required, email} from '../../lib/form_validators'
import RowItem from './row_item'

class FormTable extends Component {
    componentDidMount(){
      if(this.props.fields.length == 0)
        this.addDefaultRow()
    }

    addDefaultRow(){
      const defaultValues = {qty: 1, tax: 16, total: 0}
      this.props.fields.push(defaultValues)
    }

    render() {
      const { fields, ...others } = this.props

      return (
          <table className="form-table table table-sm table-responsive">
              <thead className="thead-default">
                  <tr>
                      <th style={{ width: '5%' }}></th>
                      <th style={{ width: '35%' }}>Nombre Item</th>
                      <th style={{ width: '10%' }}>Cantidad</th>
                      <th>Después</th>
                      <th>Costo</th>
                      <th>IVA (%)</th>
                      <th>Total ($)</th>
                      <th></th>
                  </tr>
              </thead>
              <tbody>
                  {
                    fields.map((item, index) => {
                      return <RowItem key={index} fields={fields} item={item} index={index} {...others} />
                    })
                  }
                  <tr>
                    <td colSpan="8" className="btn-add">
                      {
                          this.props.disabled !== true ?
                              <FlatButton
                                  label="Agregar otro item"
                                  labelPosition="after"
                                  primary={true}
                                  icon={<ActionAdd />}
                                  onClick={this.addDefaultRow.bind(this)} />:null
                      }
                    </td>
                  </tr>
              </tbody>
          </table>
      )
  }
}

export default FormTable
