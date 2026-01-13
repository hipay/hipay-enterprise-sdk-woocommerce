const path = require('path');

const defaultConfig = {
    mode: process.env.NODE_ENV || 'development',
    resolve: {
        extensions: ['.js', '.jsx'],
    },
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env', '@babel/preset-react'],
                    },
                },
            },
        ],
    },
    externals: {
        '@wordpress/element': 'window.wp.element',
        '@wordpress/i18n': 'window.wp.i18n',
        '@wordpress/html-entities': 'window.wp.htmlEntities',
        '@wordpress/data': 'window.wp.data',
        '@woocommerce/blocks-registry': 'window.wc.wcBlocksRegistry',
        '@woocommerce/settings': 'window.wc.wcSettings',
        '@woocommerce/block-data': 'window.wc.wcBlocksData',
        'react': 'React',
        'react-dom': 'ReactDOM',
    },
};

module.exports = [
    {
        ...defaultConfig,
        entry: './assets/js/blocks/credit-card/index.js',
        output: {
            path: path.resolve(__dirname, 'assets/js/blocks/build'),
            filename: 'credit-card-block.js',
        },
    },
    {
        ...defaultConfig,
        entry: './assets/js/blocks/local-payments/index.js',
        output: {
            path: path.resolve(__dirname, 'assets/js/blocks/build'),
            filename: 'local-payments-block.js',
        },
    },
];
