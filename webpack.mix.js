const mix = require("laravel-mix");
const ForkTsCheckerNotifierWebpackPlugin = require('fork-ts-checker-notifier-webpack-plugin');
const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const webpack = require('webpack');

mix.version();

mix.ts('resources/assets/js/app.ts', 'public/js').react(); // User React App

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
        }),
        // If you need to check memory usage:
        {
            apply: (compiler) => {
                compiler.hooks.beforeRun.tap('MemoryUsagePlugin', (compilation) => {
                    const used = process.memoryUsage().heapUsed / 1024 / 1024;
                    console.log(`Memory used before compilation: ${Math.round(used * 100) / 100} MB`);
                });

                compiler.hooks.done.tap('MemoryUsagePlugin', (stats) => {
                    const used = process.memoryUsage().heapUsed / 1024 / 1024;
                    console.log(`Memory used after compilation: ${Math.round(used * 100) / 100} MB`);
                });
            }
        },
        new webpack.DefinePlugin(envKeys),
    ],
})

mix.options({
    terser: {
        extractComments: false,
    },
    processCssUrls: false,
});

