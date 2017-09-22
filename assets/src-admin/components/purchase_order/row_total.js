import React, { Component } from 'react'
import TextField from 'material-ui/TextField';
import mapError from 'redux-form-material-ui/lib/mapError'

class RowTotal extends Component {
    constructor(props){
        super(props)

        this.state = { total: 0 }
    }

    componentWillReceiveProps(nextProps){
        this.setState({
            total: this.calcTotal()
        }, () => {
          nextProps.input.onChange(this.state.total)
        })
    }

    calcTotal(){
        const { fields, index } = this.props
        let qty = parseFloat(fields.get(index).qty)
        qty = !isNaN(qty) ? qty:0
        let cost = parseFloat(fields.get(index).cost)
        cost = !isNaN(cost) ? cost:0
        const total = qty*cost
        // props.input.onChange(total)
        return total
    }

    render(){
        // return <TextField {...mapError(this.props)} />
        return <div className="input-readonly">{this.state.total}</div>
    }
}

export default RowTotal
