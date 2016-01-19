define(['handlebars'], function(Handlebars) {

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, helper, options, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression, functionType="function";


  buffer += "<form onsubmit=\"javascript: return false;\" class=\"form-search\">\n    <input type=\"text\" class=\"q input-long\" placeholder=\""
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Search for media", options) : helperMissing.call(depth0, "translate", "Search for media", options)))
    + "\">\n    <img style=\"margin: -1px 8px 0 -27px;\" class=\"icon-16 hide loader\" src=\"/extension/ezexceed/design/ezexceed/images/loader.gif\" />\n    <span class=\"upload-container\" id=\"remotemedia-browser-local-file-container-";
  if (helper = helpers.id) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.id); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\">\n        <button type=\"button\" class=\"btn upload\" id=\"remotemedia-browser-local-file-";
  if (helper = helpers.id) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.id); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\">\n            "
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Upload new media", options) : helperMissing.call(depth0, "translate", "Upload new media", options)))
    + "\n        </button>\n    </span>\n</form>\n<div class=\"remotemedia-thumbs\"></div>\n<button class=\"btn btn-large btn-block load-more\" type=\"button\">\n    "
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Load more", options) : helperMissing.call(depth0, "translate", "Load more", options)))
    + "\n    <img class=\"icon-16 hide loader\" src=\"/extension/ezexceed/design/ezexceed/images/loader.gif\" />\n</button>\n";
  return buffer;
  })

});
