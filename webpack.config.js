const path = require("path");
const Encore = require("@symfony/webpack-encore");
const scriptRoot = "./src/main/resources/assets/script";

const entryPoints = [
    "public",
    "internal"
];

Encore
    .configureManifestPlugin((options) => {
        options.fileName = path.resolve(__dirname, "webpack.assets.json");
    })
    .setOutputPath("httpdocs/assets")
    .setPublicPath("/assets");

entryPoints.forEach((entryPoint) => {
    Encore.addEntry(entryPoint, `${scriptRoot}/index-${entryPoint}.js`);
});

Encore
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableSassLoader()
    .enableTypeScriptLoader()
    .enableIntegrityHashes(Encore.isProduction());

module.exports = Encore.getWebpackConfig();