import React, { Component } from 'react'

export default function(props){
    const { input: { value }, valIsJsonString } = props
    const val = valIsJsonString === true ? JSON.parse(value).text:value

    return (
        <div className="input-readonly">
            { props.label ? <div className="label">{props.label}</div>:null }
            <span>{val}</span>
        </div>
    )
}
