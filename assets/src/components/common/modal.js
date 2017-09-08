import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import { store } from '../../index';
import { Provider } from 'react-redux';

class Modal extends Component {
    constructor(props){
      super(props)

      this.state = {
        show: false
      }
    }

    componentDidMount(){
        this.modalTarget = document.createElement('div');
        this.modalTarget.className = 'modal fade';
        document.body.appendChild(this.modalTarget);
        // this._render();
    }

    componentWillUpdate(){
        this._render();
    }

    componentDidUpdate(){
        this._render();
    }

    componentWillUnmount(){
        ReactDOM.unmountComponentAtNode(this.modalTarget);
        document.body.removeChild(this.modalTarget);
        document.body.classList.remove('modal-open')
    }

    _render(){
        ReactDOM.render(
            <Provider store={store}>
                <div className={`modal-dialog ${this.props.sizeClass ? this.props.sizeClass:''}`}>
                  <div className="modal-content">
                    {/* <div className="modal-header">
                      <h4 className="modal-title">Large modal</h4>
                      <button type="button" className="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                      </button>
                    </div> */}
                    <button type="button" className="close" onClick={() => this.hide()} aria-label="Close">
                      <span aria-hidden="true">×</span>
                    </button>
                    <div className="modal-body">
                      {this.props.children}
                    </div>
                  </div>
                </div>
            </Provider>,
            this.modalTarget
        );
    }

    render(){
        return <noscript />;
    }

    show(){
      this.setState({ show: true })
      this.modalTarget.classList.add('show')
      document.body.classList.add('modal-open')
    }

    hide(){
      this.setState({ show: false })
      this.modalTarget.classList.remove('show')
      document.body.classList.remove('modal-open')
    }
}

export default Modal;
