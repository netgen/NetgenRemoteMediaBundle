define(['handlebars'], function(Handlebars) {

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, options, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression;


  buffer += "<p>";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "The image is too small for this crop", options) : helperMissing.call(depth0, "translate", "The image is too small for this crop", options)))
    + "</p>\n";
  return buffer;
  })

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, stack2, options, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression, functionType="function";


  buffer += "<form onsubmit=\"javascript: return false;\" class=\"form-search\">\n    <input type=\"text\" class=\"q input-long\" placeholder=\"";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "Search for media", options) : helperMissing.call(depth0, "translate", "Search for media", options)))
    + "\">\n    <img style=\"margin: -1px 8px 0 -27px;\" class=\"icon-16 hide loader\" src=\"/extension/ezexceed/design/ezexceed/images/loader.gif\" />\n    <span class=\"upload-container\" id=\"remotemedia-browser-local-file-container-";
  if (stack2 = helpers.id) { stack2 = stack2.call(depth0, {hash:{},data:data}); }
  else { stack2 = depth0.id; stack2 = typeof stack2 === functionType ? stack2.apply(depth0) : stack2; }
  buffer += escapeExpression(stack2)
    + "\">\n        <button type=\"button\" class=\"btn upload\" id=\"remotemedia-browser-local-file-";
  if (stack2 = helpers.id) { stack2 = stack2.call(depth0, {hash:{},data:data}); }
  else { stack2 = depth0.id; stack2 = typeof stack2 === functionType ? stack2.apply(depth0) : stack2; }
  buffer += escapeExpression(stack2)
    + "\">\n            ";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "Upload new media", options) : helperMissing.call(depth0, "translate", "Upload new media", options)))
    + "\n        </button>\n    </span>\n</form>\n<div class=\"remotemedia-thumbs\"></div>\n<button class=\"btn btn-large btn-block load-more\" type=\"button\">\n    ";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "Load more", options) : helperMissing.call(depth0, "translate", "Load more", options)))
    + "\n    <img class=\"icon-16 hide loader\" src=\"/extension/ezexceed/design/ezexceed/images/loader.gif\" />\n</button>\n";
  return buffer;
  })

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, stack2, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression, functionType="function", self=this;

function program1(depth0,data) {

  var buffer = "", stack1, options;
  buffer += "\n            <span class=\"share\">";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "Shared", options) : helperMissing.call(depth0, "translate", "Shared", options)))
    + "</span>\n            ";
  return buffer;
  }

  buffer += "<div class=\"item\">\n    <a class=\"pick\" data-id=\"";
  if (stack1 = helpers.id) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.id; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\">\n        <img src=\""
    + escapeExpression(((stack1 = ((stack1 = depth0.thumb),stack1 == null || stack1 === false ? stack1 : stack1.url)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\" />\n        <p class=\"meta\">";
  if (stack2 = helpers.filename) { stack2 = stack2.call(depth0, {hash:{},data:data}); }
  else { stack2 = depth0.filename; stack2 = typeof stack2 === functionType ? stack2.apply(depth0) : stack2; }
  buffer += escapeExpression(stack2)
    + "<br />\n            <span class=\"details\">";
  if (stack2 = helpers.width) { stack2 = stack2.call(depth0, {hash:{},data:data}); }
  else { stack2 = depth0.width; stack2 = typeof stack2 === functionType ? stack2.apply(depth0) : stack2; }
  buffer += escapeExpression(stack2)
    + " x ";
  if (stack2 = helpers.height) { stack2 = stack2.call(depth0, {hash:{},data:data}); }
  else { stack2 = depth0.height; stack2 = typeof stack2 === functionType ? stack2.apply(depth0) : stack2; }
  buffer += escapeExpression(stack2)
    + "</span>\n            ";
  stack2 = helpers['if'].call(depth0, depth0.shared, {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n        </p>\n    </a>\n</div>\n";
  return buffer;
  })

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, options, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression;


  buffer += "<div class=\"well well-large\">\n    <h2>";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "No results", options) : helperMissing.call(depth0, "translate", "No results", options)))
    + "</h2>\n</div>\n";
  return buffer;
  })

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression, self=this, functionType="function";

function program1(depth0,data) {

  var buffer = "", stack1, options;
  buffer += "\n    <img class=\"white\" src=\"/extension/ezexceed/design/ezexceed/images/kp/24x24/white/Alert.png\"\n        alt=\"";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "Image is to small for this version", options) : helperMissing.call(depth0, "translate", "Image is to small for this version", options)))
    + "\"\n        title=\"";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "Image is to small for this version", options) : helperMissing.call(depth0, "translate", "Image is to small for this version", options)))
    + "\" />\n    <img class=\"black\" src=\"/extension/ezexceed/design/ezexceed/images/kp/24x24/Alert.png\"\n        alt=\"";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "Image is to small for this version", options) : helperMissing.call(depth0, "translate", "Image is to small for this version", options)))
    + "\"\n        title=\"";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "Image is to small for this version", options) : helperMissing.call(depth0, "translate", "Image is to small for this version", options)))
    + "\" />\n    ";
  return buffer;
  }

  buffer += "<a>\n    ";
  stack1 = helpers['if'].call(depth0, depth0.toSmall, {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n    ";
  if (stack1 = helpers.name) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.name; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "<br />\n    <small>";
  if (stack1 = helpers.width) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.width; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "x";
  if (stack1 = helpers.height) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.height; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</small>\n</a>\n<div class=\"overlay\"></div>\n";
  return buffer;
  })

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, functionType="function", escapeExpression=this.escapeExpression;


  buffer += "<div class=\"customattributes\"></div>\n\n<section class=\"remotemedia-crop\">\n    <ul class=\"nav nav-pills inverted\"></ul>\n</section>\n\n<div class=\"remotemedia-crop-container\">\n    <div class=\"image-wrap\">\n        <img src=\"";
  if (stack1 = helpers.media) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.media; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\" />\n    </div>\n</div>\n";
  return buffer;
  })

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, stack2, options, functionType="function", escapeExpression=this.escapeExpression, self=this, helperMissing=helpers.helperMissing;

function program1(depth0,data) {

  var buffer = "", stack1;
  buffer += " value=\"";
  if (stack1 = helpers.alttext) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.alttext; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\"";
  return buffer;
  }

function program3(depth0,data) {

  var buffer = "", stack1, stack2, options;
  buffer += "\n    <label for=\"cssclass\">";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "Class", options) : helperMissing.call(depth0, "translate", "Class", options)))
    + "</label>\n    <select name=\"cssclass\" id=\"cssclass\">\n        <option value=\"\"> - </option>\n        ";
  stack2 = helpers.each.call(depth0, depth0.classes, {hash:{},inverse:self.noop,fn:self.program(4, program4, data),data:data});
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n    </select>\n    ";
  return buffer;
  }
function program4(depth0,data) {

  var buffer = "", stack1;
  buffer += "\n            <option value=\"";
  if (stack1 = helpers.value) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.value; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\"";
  stack1 = helpers['if'].call(depth0, depth0.selected, {hash:{},inverse:self.noop,fn:self.program(5, program5, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += ">";
  if (stack1 = helpers.name) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.name; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</option>\n        ";
  return buffer;
  }
function program5(depth0,data) {


  return " selected";
  }

function program7(depth0,data) {

  var buffer = "", stack1, stack2, options;
  buffer += "\n    <label for=\"viewmode\">";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "View", options) : helperMissing.call(depth0, "translate", "View", options)))
    + "</label>\n    <select name=\"viewmode\" id=\"viewmode\">\n        <option value=\"\"> - </option>\n        ";
  stack2 = helpers.each.call(depth0, depth0.viewmodes, {hash:{},inverse:self.noop,fn:self.program(8, program8, data),data:data});
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n    </select>\n    ";
  return buffer;
  }
function program8(depth0,data) {

  var buffer = "", stack1;
  buffer += "\n        <option value=\"";
  if (stack1 = helpers.value) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.value; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\"\n        ";
  stack1 = helpers['if'].call(depth0, depth0.selected, {hash:{},inverse:self.noop,fn:self.program(5, program5, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += ">";
  if (stack1 = helpers.name) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.name; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "</option>\n        ";
  return buffer;
  }

  buffer += "<div class=\"well control-group\">\n    <input type=\"text\" name=\"alttext\"\n        placeholder=\"";
  options = {hash:{},data:data};
  buffer += escapeExpression(((stack1 = helpers.translate),stack1 ? stack1.call(depth0, "Alternate text", options) : helperMissing.call(depth0, "translate", "Alternate text", options)))
    + "\"\n        ";
  stack2 = helpers['if'].call(depth0, depth0.alttext, {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += ">\n\n    ";
  stack2 = helpers['if'].call(depth0, depth0.classes, {hash:{},inverse:self.noop,fn:self.program(3, program3, data),data:data});
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n\n    ";
  stack2 = helpers['if'].call(depth0, depth0.viewmodes, {hash:{},inverse:self.noop,fn:self.program(7, program7, data),data:data});
  if(stack2 || stack2 === 0) { buffer += stack2; }
  buffer += "\n</div>\n";
  return buffer;
  })

return Handlebars.template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [2,'>= 1.0.0-rc.3'];
helpers = helpers || Handlebars.helpers; data = data || {};
  var buffer = "", stack1, functionType="function", escapeExpression=this.escapeExpression;


  buffer += "<span class=\"label\">";
  if (stack1 = helpers.tag) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.tag; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + " <button class=\"close\" data-tag=\"";
  if (stack1 = helpers.tag) { stack1 = stack1.call(depth0, {hash:{},data:data}); }
  else { stack1 = depth0.tag; stack1 = typeof stack1 === functionType ? stack1.apply(depth0) : stack1; }
  buffer += escapeExpression(stack1)
    + "\">×</button></span>\n";
  return buffer;
  })

});
