import React, { Component } from 'react'
import { reduxForm, Field, FieldArray, formValueSelector } from 'redux-form'
import MuiThemeProvider from 'material-ui/styles/MuiThemeProvider';
import {
  DatePicker,
  TextField,
} from 'redux-form-material-ui'
import { dateTimeFormat } from '../../lib/utils'
import { required } from '../../lib/form_validators'
import RaisedButton from 'material-ui/RaisedButton'

class PayCourierForm extends Component {
    componentWillMount(){
      this.props.initialize( this.preFormatData() )
    //   this.formPost.addEventListener('submit', this.onSubmit.bind(this))
    }

    onSubmit(props){
        this.props.onSubmit(props)
    }

    preFormatData(){
          let { data } = this.props

          if(data && data.date){
              data.date = new Date(data.date)
          }else{
              const date = new Date()
              date.setDate(date.getDate())
              data.date = date
          }

          return data
    }

    render(){
        return (
            <MuiThemeProvider>
                <form className="row pay-courier-form form-material" onSubmit={this.props.handleSubmit(this.onSubmit.bind(this))}>
                    <div className="col-md-6">
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
                    <div className="col-md-6">
                        <Field
                          name="value"
                          className="input-wrap"
                          component={TextField}
                          hintText="Valor"
                          floatingLabelText="Valor"
                          validate={required} />
                    </div>
                    <div className="col-md-6 offset-md-3" style={{ marginTop: 20 }}>
                        <RaisedButton
                            type="submit"
                            label="Registrar Pago"
                            primary={true} />
                    </div>
                </form>
            </MuiThemeProvider>
        )
    }
}

PayCourierForm = reduxForm({
  form: 'PayCourierForm',
})(PayCourierForm);

export default PayCourierForm
