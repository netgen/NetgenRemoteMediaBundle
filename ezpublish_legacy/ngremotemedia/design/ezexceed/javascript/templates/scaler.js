define(['handlebars'], function(Handlebars) {

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, helper, functionType="function", escapeExpression=this.escapeExpression;


  buffer += "<div class=\"customattributes\"></div>\n\n<section class=\"remotemedia-crop\">\n    <ul class=\"nav nav-pills inverted\"></ul>\n</section>\n\n<div class=\"remotemedia-crop-container\">\n    <div class=\"image-wrap\">\n        <img src=\"";
  if (helper = helpers.media) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.media); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\" />\n    </div>\n</div>\n";
  return buffer;
  })

});
