define(['handlebars'], function(Handlebars) {

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", helper, options, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression;


  buffer += "<div class=\"well well-large\">\n    <h2>"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "No results", options) : helperMissing.call(depth0, "translate", "No results", options)))
    + "</h2>\n</div>\n";
  return buffer;
  })

});