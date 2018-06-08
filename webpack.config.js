const path = require('path');
const glob = require('glob');

// Minify JS
const MinifyPlugin = require('babel-minify-webpack-plugin');

// This plugin extracts the CSS into its own file instead of tying it with the JS.
// It prevents:
//   - not having styles due to a JS error
//   - the flash page without styles during JS loading
const ExtractTextPlugin = require("extract-text-webpack-plugin");

const extractCssDefault = new ExtractTextPlugin({
  filename: "../css/[name].min.css",
  publicPath: 'tpl/default/css/',
});

const extractCssVintage = new ExtractTextPlugin({
  filename: "../css/[name].min.css",
  publicPath: 'tpl/vintage/css/',
});

module.exports = [
  {
    entry: {
      thumbnails: './assets/common/js/thumbnails.js',
      thumbnails_update: './assets/common/js/thumbnails-update.js',
      pluginsadmin: './assets/default/js/plugins-admin.js',
      shaarli: [
        './assets/default/js/base.js',
        './assets/default/scss/shaarli.scss',
      ].concat(glob.sync('./assets/default/img/*')),
    },
    output: {
      filename: '[name].min.js',
      path: path.resolve(__dirname, 'tpl/default/js/')
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: [
                'babel-preset-env',
              ]
            }
          }
        },
        {
          test: /\.scss/,
          use: extractCssDefault.extract({
            use: [{
              loader: "css-loader",
              options: {
                minimize: true,
              }
            }, {
              loader: "sass-loader"
            }],
          })
        },
        {
          test: /\.(gif|png|jpe?g|svg|ico)$/i,
          use: [
            {
              loader: 'file-loader',
              options: {
                name: '../img/[name].[ext]',
                publicPath: 'tpl/default/img/',
              }
            }
          ],
        },
        {
          test: /\.(eot|ttf|woff|woff2)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
          loader: 'file-loader',
          options: {
            name: '../fonts/[name].[ext]',
            // do not add a publicPath here because it's already handled by CSS's publicPath
            publicPath: '',
          }
        },
      ],
    },
    plugins: [
      new MinifyPlugin(),
      extractCssDefault,
    ],
  },
  {
    entry: {
      shaarli: [
        './assets/vintage/js/base.js',
        './assets/vintage/css/reset.css',
        './assets/vintage/css/shaarli.css',
      ].concat(glob.sync('./assets/vintage/img/*')),
      thumbnails: './assets/common/js/thumbnails.js',
      thumbnails_update: './assets/common/js/thumbnails-update.js',
    },
    output: {
      filename: '[name].min.js',
      path: path.resolve(__dirname, 'tpl/vintage/js/')
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: [
                'babel-preset-env',
              ]
            }
          }
        },
        {
          test: /\.css$/,
          use: extractCssVintage.extract({
            use: [{
              loader: "css-loader",
              options: {
                minimize: true,
              }
            }],
          })
        },
        {
          test: /\.(gif|png|jpe?g|svg|ico)$/i,
          use: [
            {
              loader: 'file-loader',
              options: {
                name: '../img/[name].[ext]',
                publicPath: '',
              }
            }
          ],
        },
      ],
    },
    plugins: [
      new MinifyPlugin(),
      extractCssVintage,
    ],
  },
];
