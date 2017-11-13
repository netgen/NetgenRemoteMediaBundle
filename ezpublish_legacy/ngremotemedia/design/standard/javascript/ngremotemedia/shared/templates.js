(function() {
  var template = Handlebars.template, templates = window.JST = window.JST || {};
templates['alert'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", helper, options, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression;


  buffer += "<p>"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "The image is too small for this crop", options) : helperMissing.call(depth0, "translate", "The image is too small for this crop", options)))
    + "</p>\n";
  return buffer;
  });
templates['browser'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, helper, options, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression, functionType="function";


  buffer += "<form onsubmit=\"javascript: return false;\" class=\"form-search\">\n\n\n    <div class=\"form-group\">\n        <label for=\"ngremotemedia-remote-media-type-select\">"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Media type", options) : helperMissing.call(depth0, "translate", "Media type", options)))
    + ":</label>\n        <select class=\"ngremotemedia-remote-media-type-select\" id=\"ngremotemedia-remote-media-type-select\">\n            <option value=\"image\">"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Image", options) : helperMissing.call(depth0, "translate", "Image", options)))
    + "</option>\n            <option value=\"video\">"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Video", options) : helperMissing.call(depth0, "translate", "Video", options)))
    + "</option>\n        </select>\n    </div>\n\n    <div class=\"form-group\">\n        <label for=\"ngremotemedia-remote-folder-select\">"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Folder", options) : helperMissing.call(depth0, "translate", "Folder", options)))
    + ":</label>\n        <select class=\"ngremotemedia-remote-folders\" id=\"ngremotemedia-remote-folder-select\">\n            <option class=\"loading\">"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Loading...", options) : helperMissing.call(depth0, "translate", "Loading...", options)))
    + "</option>\n            <option value=\"root\">"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Root folder", options) : helperMissing.call(depth0, "translate", "Root folder", options)))
    + "</option>\n        </select>\n    </div>\n\n    <div class=\"form-group\">\n        <span class=\"upload-container\" id=\"ngremotemedia-browser-local-file-container-";
  if (helper = helpers.id) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.id); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\">\n            <button type=\"button\" class=\"btn btn-primary upload upload-from-disk\" id=\"ngremotemedia-browser-local-file-";
  if (helper = helpers.id) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.id); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\">\n                "
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Upload new media", options) : helperMissing.call(depth0, "translate", "Upload new media", options)))
    + "\n            </button>\n        </span>\n\n        <div class=\"with-loader\">\n            <input type=\"text\" class=\"form-control q input-long\" placeholder=\""
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Search for media", options) : helperMissing.call(depth0, "translate", "Search for media", options)))
    + "\">\n            <i class=\"ngri-spinner loader\"></i>\n        </div>\n    </div>\n\n</form>\n\n<div class=\"ngremotemedia-thumbs-scroll\">\n  <div class=\"ngremotemedia-thumbs\"></div>\n</div>\n\n<button class=\"btn btn-large btn-block load-more\" type=\"button\">\n    <i class=\"ngri ngri-spinner loader\"></i> "
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Load more", options) : helperMissing.call(depth0, "translate", "Load more", options)))
    + "\n</button>\n";
  return buffer;
  });
templates['item'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, helper, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression, functionType="function", self=this;

function program1(depth0,data) {
  
  var buffer = "", helper, options;
  buffer += "\n            <span class=\"share\">"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Shared", options) : helperMissing.call(depth0, "translate", "Shared", options)))
    + "</span>\n            ";
  return buffer;
  }

  buffer += "<div class=\"item\">\n    <a class=\"pick\" data-id=\"";
  if (helper = helpers.id) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.id); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\">\n        <img src=\"";
  if (helper = helpers.url) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.url); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\" />\n        <p class=\"meta\">";
  if (helper = helpers.filename) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.filename); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "<br />\n            <span class=\"details\">";
  if (helper = helpers.width) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.width); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + " x ";
  if (helper = helpers.height) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.height); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "</span>\n            ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.shared), {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n        </p>\n    </a>\n</div>\n";
  return buffer;
  });
templates['nohits'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", helper, options, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression;


  buffer += "<div class=\"well well-large\">\n    <h2>"
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "No results", options) : helperMissing.call(depth0, "translate", "No results", options)))
    + "</h2>\n</div>\n";
  return buffer;
  });
templates['scaledversion'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, helper, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression, self=this, functionType="function";

function program1(depth0,data) {
  
  var buffer = "", helper, options;
  buffer += "\n    title=\""
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Image is to small for this version", options) : helperMissing.call(depth0, "translate", "Image is to small for this version", options)))
    + "\"\n";
  return buffer;
  }

function program3(depth0,data) {
  
  
  return "\n    <i class=\"ngri-warning\"></i>\n    ";
  }

  buffer += "<a\n";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.toSmall), {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n>\n    ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.toSmall), {hash:{},inverse:self.noop,fn:self.program(3, program3, data),data:data});
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
    + "</small>\n</a>\n";
  return buffer;
  });
templates['scaler'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, helper, options, helperMissing=helpers.helperMissing, escapeExpression=this.escapeExpression, functionType="function";


  buffer += "<button class=\"btn btn-primary js-close\">\n    "
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Apply", options) : helperMissing.call(depth0, "translate", "Apply", options)))
    + "\n</button>\n\n<div class=\"customattributes\"></div>\n\n<section class=\"ngremotemedia-crop\">\n    <ul class=\"nav nav-pills inverted\"></ul>\n</section>\n\n<div class=\"ngremotemedia-crop-container\">\n    <div class=\"image-wrap\">\n        <img src=\"";
  if (helper = helpers.media) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.media); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\" />\n    </div>\n</div>\n";
  return buffer;
  });
templates['scalerattributes'] = template(function (Handlebars,depth0,helpers,partials,data) {
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

  buffer += "<div class=\"well control-group\">\n    <input type=\"text\" name=\"alttext\"\n        placeholder=\""
    + escapeExpression((helper = helpers.translate || (depth0 && depth0.translate),options={hash:{},data:data},helper ? helper.call(depth0, "Alternate text", options) : helperMissing.call(depth0, "translate", "Alternate text", options)))
    + "\"\n        ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.alttext), {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += ">\n\n    ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.classes), {hash:{},inverse:self.noop,fn:self.program(3, program3, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n\n</div>\n";
  return buffer;
  });
templates['tag'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, helper, functionType="function", escapeExpression=this.escapeExpression;


  buffer += "<span class=\"label\">";
  if (helper = helpers.tag) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.tag); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + " <button class=\"close\" data-tag=\"";
  if (helper = helpers.tag) { stack1 = helper.call(depth0, {hash:{},data:data}); }
  else { helper = (depth0 && depth0.tag); stack1 = typeof helper === functionType ? helper.call(depth0, {hash:{},data:data}) : helper; }
  buffer += escapeExpression(stack1)
    + "\">×</button></span>\n";
  return buffer;
  });
})();