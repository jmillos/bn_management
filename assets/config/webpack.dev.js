const webpackMerge = require('webpack-merge');
const commonConfig = require('./webpack.common.js');

module.exports = webpackMerge(commonConfig, {

    /**
     * Disable debug mode for production.
     *
     * See: http://webpack.github.io/docs/configuration.html#debug
     */
    // debug: true,

    devServer: {
        port: 8080,
        historyApiFallback: true,
        contentBase: './',
        hot: true,
        inline: true,
        host: 'localhost', //'192.168.2.196',
        https: false
    }
});
