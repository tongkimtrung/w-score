const {defineConfig} = require('@vue/cli-service')
const path = require('path');
module.exports = defineConfig({
    css: {
        loaderOptions: {
            sass: {
                additionalData:
                    `@import "./src/assets/_variables.scss";`,
            }
        }
    },
    transpileDependencies: true,
    configureWebpack: {
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'src/'),
                '@public': path.resolve(__dirname, 'public/'),
            }
        }
    },
    devServer: {
        port: 9091 // Change this to the port you want to use
    }
})
