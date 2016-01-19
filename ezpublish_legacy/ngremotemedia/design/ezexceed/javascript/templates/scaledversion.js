define(['handlebars'], function(Handlebars) {

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, helper, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression, self=this, functionType="function";

function program1(depth0,data) {
  
  var buffer = "", helper, options;
  buffer += "\n    <img class=\"white\" src=\"/extension/ezexceed/design/ezexceed/images/kp/24x24/white/Alert.png\"\n        alt=\""
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Image is to small for this version", options) : helperMissing.call(depth0, "translate", "Image is to small for this version", options)))
    + "\"\n        title=\""
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Image is to small for this version", options) : helperMissing.call(depth0, "translate", "Image is to small for this version", options)))
    + "\" />\n    <img class=\"black\" src=\"/extension/ezexceed/design/ezexceed/images/kp/24x24/Alert.png\"\n        alt=\""
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Image is to small for this version", options) : helperMissing.call(depth0, "translate", "Image is to small for this version", options)))
    + "\"\n        title=\""
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Image is to small for this version", options) : helperMissing.call(depth0, "translate", "Image is to small for this version", options)))
    + "\" />\n    ";
  return buffer;
  }

  buffer += "<a>\n    ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.toSmall), {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n    ";
  if (helper = helpers.name) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.name); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "<br />\n    <small>";
  if (helper = helpers.width) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.width); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "x";
  if (helper = helpers.height) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.height); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "</small>\n</a>\n<div class=\"overlay\"></div>\n";
  return buffer;
  })

});