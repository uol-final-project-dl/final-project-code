const mix = require("laravel-mix");
const ForkTsCheckerNotifierWebpackPlugin = require('fork-ts-checker-notifier-webpack-plugin');
const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

mix.version();

mix.ts('resources/assets/js/app.ts', 'public/js').react(); // User React App
mix.sass("resources/assets/sass/app.scss", "public/css");

mix.webpackConfig({
    cache: {
        type: 'filesystem',
    },
    watchOptions: {
        ignored: /node_modules/
    },
    module: {
        rules: [
            {
                test: /.([cm]?ts|tsx)$/,
                loader: 'ts-loader',
                options: {
                    transpileOnly: true
                }
            },
        ],
    },
    optimization: {
        realContentHash: false,
        mangleWasmImports: true,
        splitChunks: {
            chunks: 'all',
        },
        minimize: mix.inProduction(),
        minimizer: [new TerserPlugin({
            terserOptions: {
                sourceMap: false,
                compress: {
                    passes: 1,
                },
            },
            parallel: 2
        })]
    },
    plugins: [
        new ForkTsCheckerWebpackPlugin(),
        new ForkTsCheckerNotifierWebpackPlugin({
            title: 'TypeScript',
            excludeWarnings: false,
        })
    ],
})

mix.options({
    terser: {
        extractComments: false,
    },
    processCssUrls: false,
});

