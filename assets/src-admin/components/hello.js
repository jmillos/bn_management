import React, { Component } from 'react'
import ReactWebComponent from 'react-web-component'

class Hello extends Component {
  render() {
    return <small>Hello World!</small>;
  }
}

// customElements.define('hello', Hello);
ReactWebComponent.create(<Hello />, 'my-component');
