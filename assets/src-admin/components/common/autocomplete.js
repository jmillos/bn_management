import React, { Component } from 'react'
import mapError from 'redux-form-material-ui/lib/mapError'
import MUIAutoComplete from 'material-ui/AutoComplete'
import IconButton from 'material-ui/IconButton';
import ContentClear from 'material-ui/svg-icons/content/clear';

class Autocomplete extends Component {
    constructor(props){
        super(props)

        this.state = { selected: false, initial: false }
    }

    componentWillMount(){
      if(this.props.meta.initial){
          this.setState({ selected: true, initial: true })
      }
    }

    componentWillUpdate(nextProps, nextState){
      if(nextProps.meta.initial !== this.props.meta.initial && nextProps.meta.initial){
          this.setState({ selected: true, initial: true })
      }
    }

    clear(){
      this.setState({ selected: false })
      this.props.input.onChange(null)
    }

    render(){
        const { input, onNewRequest, dataSourceConfig, meta, floatingLabelText } = this.props
        // console.log(this.props);

        if (!this.state.selected) {
          const mapProps = {
              ...mapError(this.props),
              onNewRequest: value => {
                  input.onChange(
                      typeof value === 'object' && dataSourceConfig
                          ? value[dataSourceConfig.value]
                              : ( typeof value === 'object' || Array.isArray(value) ? JSON.stringify(value):value )
                  )
                  this.setState({ selected: true, initial: false })
                  if (onNewRequest) {
                      onNewRequest(value)
                  }
              }
          }
          return <MUIAutoComplete {...mapProps} />
        }else{
          const styles = {
            smallIcon: {
              width: 16,
              height: 16,
              padding: 0,
            },
            small: {
              height: 24,
              position: 'absolute',
              // right: '2px',
              top: 31,
              width: 24,
            }
          }
          const inputValue = this.state.initial ? meta.initial:input.value
          const val = typeof inputValue === 'string' ? JSON.parse(inputValue):inputValue
          return (
            <div className="input-readonly">
              {
                floatingLabelText ? <div className="label">{floatingLabelText}</div>:null
              }
              <span>{val.text}</span>
              {
                this.props.withBtnClear && this.props.withBtnClear === true ? <IconButton
                    tooltip="Remover"
                    iconStyle={styles.smallIcon}
                    style={styles.small}
                    onTouchTap={this.clear.bind(this)}>
                  <ContentClear />
                </IconButton>:null
              }
            </div>
          )
        }

    }
}

export default Autocomplete
