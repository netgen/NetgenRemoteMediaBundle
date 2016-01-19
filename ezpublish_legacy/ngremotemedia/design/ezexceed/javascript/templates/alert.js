define(['handlebars'], function(Handlebars) {

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", helper, options, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression;


  buffer += "<p>"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "The image is too small for this crop", options) : helperMissing.call(depth0, "translate", "The image is too small for this crop", options)))
    + "</p>\n";
  return buffer;
  })

});