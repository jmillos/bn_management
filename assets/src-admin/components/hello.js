import React, { Component } from 'react'
import ReactWebComponent from 'react-web-component'
// import { register } from 'web-react-components'

class Hello extends Component {
  render() {
    return <small>Hello World!</small>;
  }
}

// customElements.define('hello', Hello);
/*window.addEventListener('WebComponentsReady', function(e) {
  // Create elements here
  	alert('Hola WEBC')
  	var script = document.createElement('script');
    script.async = true;
    script.src = 'webcomponentsjs/webcomponents-lite.min.js';
    script.onload = onload;
    document.head.appendChild(script);
	ReactWebComponent.create(<Hello />, 'my-component');
});*/

// register(Hello, 'my-component');
ReactWebComponent.create(<Hello />, 'my-component');
