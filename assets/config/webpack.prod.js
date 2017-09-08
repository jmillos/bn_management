const webpack = require('webpack');
const path = require('path');

module.exports = {
    output: {
        path: path.join(__dirname, '/dist'),
        filename: '[name].[chunkhash].bundle.js',
        // publicPath: publicPath,
        sourceMapFilename: '[name].[chunkhash].map'
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
            comments: false
        })
    ]
}
