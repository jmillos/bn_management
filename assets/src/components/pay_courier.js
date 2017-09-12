import React, { Component } from 'react'
import {connect} from 'react-redux'
import { bindActionCreators } from 'redux';
import { reduxForm, Field, FieldArray, formValueSelector } from 'redux-form'
import {
  DatePicker,
  TextField,
} from 'redux-form-material-ui'
import { dateTimeFormat } from '../lib/utils'
import { required } from '../lib/form_validators'

import { sendPayCourier } from '../actions/actions_pay_courier'
import Modal from './common/modal'
import PayCourierForm from './pay_courier/form'

class PayCourier extends Component {
    state = {
        displayModal: false
    }

    displayModal(){
        this.setState({ displayModal: true }, () => this.refs.modal.show())
    }

    onSubmit(props){
        this.props.sendPayCourier(1, props)
            .then(() => {
                this.refs.modal.hide()
                // this.setState({ displayModal: true })
            })
    }

    render(){
        return (
            <div className="pay-courier">
                <button type="button" className="btn btn-link" onClick={() => this.displayModal()}>Pagar</button>

                {
                    this.state.displayModal === true ?
                        <Modal ref="modal" sizeClass="modal-xs">
                            <PayCourierForm
                                data={this.props.data}
                                onSubmit={this.onSubmit.bind(this)} />
                        </Modal>:null
                }
            </div>
        )
    }
}

PayCourier = reduxForm({
  form: 'PayCourierForm',
})(PayCourier);

function mapStateToProps(state) {
    return {

    };
}

function mapDispatchToProps(dispatch) {
  return bindActionCreators({ sendPayCourier }, dispatch);
}

export default connect(mapStateToProps, mapDispatchToProps)(PayCourier)
