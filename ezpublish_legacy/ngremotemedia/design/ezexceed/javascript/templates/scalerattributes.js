define(['handlebars'], function(Handlebars) {

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, helper, options, functionType="function", escapeExpression=this.escapeExpression, self=this, helperMissing=helpers.helperMissing;

function program1(depth0,data) {
  
  var buffer = "", stack1, helper;
  buffer += " value=\"";
  if (helper = helpers.alttext) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.alttext); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\"";
  return buffer;
  }

function program3(depth0,data) {
  
  var buffer = "", stack1, helper, options;
  buffer += "\n    <label for=\"cssclass\">"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Class", options) : helperMissing.call(depth0, "translate", "Class", options)))
    + "</label>\n    <select name=\"cssclass\" id=\"cssclass\">\n        <option value=\"\"> - </option>\n        ";
  stack1 = helpers.each.call(depth0, (depth0 && depth0.classes), {hash:{},inverse:self.noop,fn:self.program(4, program4, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n    </select>\n    ";
  return buffer;
  }
function program4(depth0,data) {
  
  var buffer = "", stack1, helper;
  buffer += "\n            <option value=\"";
  if (helper = helpers.value) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.value); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\"";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.selected), {hash:{},inverse:self.noop,fn:self.program(5, program5, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += ">";
  if (helper = helpers.name) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.name); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "</option>\n        ";
  return buffer;
  }
function program5(depth0,data) {
  
  
  return " selected";
  }

function program7(depth0,data) {
  
  var buffer = "", stack1, helper, options;
  buffer += "\n    <label for=\"viewmode\">"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "View", options) : helperMissing.call(depth0, "translate", "View", options)))
    + "</label>\n    <select name=\"viewmode\" id=\"viewmode\">\n        <option value=\"\"> - </option>\n        ";
  stack1 = helpers.each.call(depth0, (depth0 && depth0.viewmodes), {hash:{},inverse:self.noop,fn:self.program(8, program8, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n    </select>\n    ";
  return buffer;
  }
function program8(depth0,data) {
  
  var buffer = "", stack1, helper;
  buffer += "\n        <option value=\"";
  if (helper = helpers.value) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.value); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\"\n        ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.selected), {hash:{},inverse:self.noop,fn:self.program(5, program5, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += ">";
  if (helper = helpers.name) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.name); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "</option>\n        ";
  return buffer;
  }

  buffer += "<div class=\"well control-group\">\n    <input type=\"text\" name=\"alttext\"\n        placeholder=\""
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Alternate text", options) : helperMissing.call(depth0, "translate", "Alternate text", options)))
    + "\"\n        ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.alttext), {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += ">\n\n    ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.classes), {hash:{},inverse:self.noop,fn:self.program(3, program3, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n\n    ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.viewmodes), {hash:{},inverse:self.noop,fn:self.program(7, program7, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n</div>\n";
  return buffer;
  })

});