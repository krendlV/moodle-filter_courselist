define([], function () {
  window.requirejs.config({
      paths: {
        "slick": M.cfg.wwwroot + '/filter/courselist/lib/slick/slick.min.js',
      },
      shim: {
        'slick': {exports: 'slick'},
      }
  });
});