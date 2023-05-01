const Encore = require("@symfony/webpack-encore");
const scriptRoot = "./assets/script";

const entryPoints = [
    "public",
    "internal"
];

Encore
    .setOutputPath("public/assets")
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