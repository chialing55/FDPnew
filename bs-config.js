module.exports = {
  proxy: "http://nginx:80",
  host: "0.0.0.0",      // ⭐ 強制對外綁定
  listen: "0.0.0.0",    // ⭐ 有些版本需要
  port: 3000,
  ui: {
    port: 3001,
  },
  files: [
    "public/css/**/*.css",
    "resources/views/**/*.blade.php",
    "public/js/**/*.js"
  ],
  open: false,
};
