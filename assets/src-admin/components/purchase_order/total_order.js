import React, { Component } from 'react'
import { Field } from 'redux-form'

class TotalOrder extends Component {
    constructor(props){
        super(props)

        this.state = { totalOrder: {} }
    }

    componentWillReceiveProps(nextProps){
        this.setState({
            totalOrder: this.calcTotals(nextProps)
        }, () => {
          nextProps.input.onChange(this.state.totalOrder)
        })
    }

    calcTotals(props){
        let tax = {}
        let totalTax = 0
        let total = 0
        let subtotal = 0
        let qty = 0
        if(props.items){
          props.items.map((i) => {
              let taxTmp = props.totalsAre == 'exclusive' ? i.total*(i.tax/100):i.total-i.total/(1+i.tax/100)
              if(tax[i.tax]){
                  tax[i.tax] += taxTmp
              }else{
                  tax[i.tax] = taxTmp
              }

              qty += parseFloat(i.qty)
              subtotal += i.total
              totalTax += taxTmp
              total += props.totalsAre == 'exclusive' ? i.total+taxTmp:i.total
          })
        }

        return { tax, totalTax, totalCost: Math.round(total), totalQty: qty, subtotal }
    }

    render(){
        const { tax, totalQty, subtotal, totalCost } = this.state.totalOrder

        return (
            <div className="total-order">
                <table className="table">
                  <tbody>
                    <tr>
                      <td>Unidades totales</td>
                      <td>
                          {totalQty}
                      </td>
                    </tr>
                    <tr>
                      <td>Subtotal</td>
                      <td>${subtotal}</td>
                    </tr>
                    {
                      tax ? Object.keys(tax).map((key) => {
                        return (
                          <tr key={key}>
                            <td>IVA <i>({key}%)</i></td>
                            <td>${Math.round(tax[key])}</td>
                          </tr>
                        )
                      }):null
                    }
                    <tr>
                      <td>Costo total</td>
                      <td>${totalCost}</td>
                    </tr>
                  </tbody>
                </table>
            </div>
        )
    }
}

export default TotalOrder
