/* eslint-env node */

const { configure } = require('quasar/wrappers');

module.exports = configure(function (/* ctx */) {
  return {
    eslint: {
      warnings: true,
      errors: true
    },

    boot: [
      'axios',
      'auth'
    ],

    css: [
      'app.scss'
    ],

    extras: [
      'roboto-font',
      'material-icons'
    ],

    build: {
      target: {
        browser: ['es2019', 'edge88', 'firefox78', 'chrome87', 'safari13.1'],
        node: 'node16'
      },
      vueRouterMode: 'history',
      env: {
        API_URL: process.env.API_URL || 'http://localhost:8080'
      }
    },

    devServer: {
      open: true
    },

    framework: {
      config: {
        notify: {
          position: 'top-right',
          timeout: 2500
        },
        loading: {
          spinnerColor: 'primary'
        }
      },
      plugins: [
        'Notify',
        'Loading',
        'Dialog',
        'LocalStorage',
        'SessionStorage'
      ]
    },

    animations: 'all',

    ssr: {
      pwa: false,
      prodPort: 3000,
      middlewares: [
        'render'
      ]
    },

    pwa: {
      workboxMode: 'generateSW',
      injectPwaMetaTags: true,
      swFilename: 'sw.js',
      manifestFilename: 'manifest.json',
      useCredentialsForManifestTag: false
    },

    capacitor: {
      hideSplashscreen: true
    },

    electron: {
      inspectPort: 5858,
      bundler: 'packager',
      builder: {
        appId: 'optic-modern'
      }
    },

    bex: {
      contentScripts: [
        'my-content-script'
      ]
    }
  }
});
