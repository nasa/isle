({
    appDir: 'src',
    baseUrl: "lib",
    paths: {
        //jquery: "empty:", //can't use a cdn with shim.
        jquery: "jquery-1.7.2",
        app: '../app'
    },
    dir: '../scripts',
    removeCombined: true,
    modules: [{
      //module names are relative to baseUrl
      //name: 'app/NodeManager',
      name: '../main'
    }]
})