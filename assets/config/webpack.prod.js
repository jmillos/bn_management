const webpack = require('webpack');
const path = require('path');
const webpackMerge = require('webpack-merge');
const commonConfig = require('./webpack.common.js');

module.exports = webpackMerge(commonConfig, {
    output: {
        path: path.join(__dirname, '../js'),
        filename: 'bonster.bundle.js',
        // publicPath: publicPath,
        sourceMapFilename: 'bonster.map'
    },

    plugins: [
        new webpack.optimize.UglifyJsPlugin({
            beautify: false,
            mangle: {
                screw_ie8: true,
                keep_fnames: true
            },
            compress: {
                screw_ie8: true
            },
            sourceMap: true,
            comments: false
        })
    ]
})
