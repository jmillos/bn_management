import React, { Component } from 'react'
import {Field} from 'redux-form'
import MUIAutoComplete from 'material-ui/AutoComplete'
import AutoComplete from '../common/autocomplete'
import {
    // AutoComplete,
    Checkbox,
    DatePicker,
    TimePicker,
    RadioButtonGroup,
    SelectField,
    Slider,
    TextField,
    Toggle
} from 'redux-form-material-ui'
import FloatingActionButton from 'material-ui/FloatingActionButton';
import ActionDelete from 'material-ui/svg-icons/action/delete';

import {required, email} from '../../lib/form_validators'
import { SEARCH_PRODUCTS_NONCE } from '../../config'
import RowTotal from './row_total'

function renderReadonly(props){
  console.log(props);
  return (
    <div className="input-readonly">{props.input.value}</div>
  )
}

class RowItem extends Component {
    constructor(props){
      super(props)

      this.onSearch = this.onSearch.bind(this)
    }

    onSearch(searchText, dataSource, params){
        const { source } = params

        if(source == "change")
            this.props.fetchSearchProducts(searchText, SEARCH_PRODUCTS_NONCE)
    }

    onSelect(value){
      console.log(value);
    }

    render() {
      const { fields, item, index, disabled } = this.props
      const styles = {
        smallIcon: {
          width: 24,
          height: 24,
          padding: 0,
        },
        small: {
          width: 16,
        }
      }

      return (
        <tr className="row-item">
            <td>
                <div className="input-readonly">{index+1}</div>
            </td>
            <td>
                <Field
                    name={`${item}.name`}
                    className="input-sm"
                    component={AutoComplete}
                    disabled={disabled}
                    filter={MUIAutoComplete.fuzzyFilter}
                    onUpdateInput={this.onSearch}
                    // onNewRequest={value => fields.get(index).name = value}
                    // dataSourceConfig={{ text: 'text', value: 'value' }}
                    dataSource={this.props.productsFound}
                    openOnFocus
                    hintText="Empieza a escribir referencia o nombre"
                    validate={required} />
            </td>
            <td>
                <Field name={`${item}.qty`} className="input-sm" component={TextField} disabled={disabled} hintText="Cantidad" validate={required} />
            </td>
            <td>
                <Field name={`${item}.after`} component={renderReadonly} disabled={disabled} hintText="Despues" />
            </td>
            <td>
                <Field name={`${item}.cost`} className="input-sm" component={TextField} disabled={disabled} hintText="Costo" validate={required} />
            </td>
            <td>
                <Field name={`${item}.tax`} className="input-sm" component={TextField} disabled={disabled} hintText="IVA" validate={required} />
            </td>
            <td>
                <Field name={`${item}.total`} component={RowTotal} disabled={disabled} hintText="Total" fields={fields} index={index} />
            </td>
            <td>
              <div className="btn-delete">
                <FloatingActionButton backgroundColor="red" iconStyle={styles.smallIcon} onTouchTap={() => {fields.remove(index)}}>
                  <ActionDelete style={styles.small} />
                </FloatingActionButton>
              </div>
            </td>
        </tr>
      )
  }
}

export default RowItem
