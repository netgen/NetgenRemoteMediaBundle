(function() {
  var template = Handlebars.template, templates = window.NgRemoteMedia.JST = window.NgRemoteMedia.JST || {};
templates['alert'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", escapeExpression=this.escapeExpression;


  buffer += "<p>"
    + escapeExpression(helpers.translate.call(depth0, "The image is too small for this crop", {hash:{},data:data}))
    + "</p>\n";
  return buffer;
  });
templates['browser'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, escapeExpression=this.escapeExpression, functionType="function";


  buffer += "<form onsubmit=\"javascript: return false;\" class=\"form-search\">\n\n\n    <div class=\"form-group\">\n        <label for=\"ngremotemedia-remote-media-type-select\">"
    + escapeExpression(helpers.translate.call(depth0, "Media type", {hash:{},data:data}))
    + ":</label>\n        <select class=\"ngremotemedia-remote-media-type-select\" id=\"ngremotemedia-remote-media-type-select\">\n            <option value=\"image\">"
    + escapeExpression(helpers.translate.call(depth0, "Image", {hash:{},data:data}))
    + "</option>\n            <option value=\"video\">"
    + escapeExpression(helpers.translate.call(depth0, "Video", {hash:{},data:data}))
    + "</option>\n        </select>\n    </div>\n\n    <div class=\"form-group\">\n        <label for=\"ngremotemedia-remote-folder-select\">"
    + escapeExpression(helpers.translate.call(depth0, "Folder", {hash:{},data:data}))
    + ":</label>\n        <select class=\"ngremotemedia-remote-folders\" id=\"ngremotemedia-remote-folder-select\">\n            <option class=\"loading\">"
    + escapeExpression(helpers.translate.call(depth0, "Loading...", {hash:{},data:data}))
    + "</option>\n            <option value=\"all\">"
    + escapeExpression(helpers.translate.call(depth0, "All", {hash:{},data:data}))
    + "</option>\n        </select>\n    </div>\n\n    <div class=\"form-group\">\n        <span class=\"upload-container\" id=\"ngremotemedia-browser-local-file-container-"
    + escapeExpression(((stack1 = (depth0 && depth0.id)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\">\n            <button type=\"button\" class=\"btn btn-primary upload upload-from-disk\" id=\"ngremotemedia-browser-local-file-"
    + escapeExpression(((stack1 = (depth0 && depth0.id)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\">\n                "
    + escapeExpression(helpers.translate.call(depth0, "Upload new media", {hash:{},data:data}))
    + "\n            </button>\n        </span>\n\n        <div class=\"with-loader\">\n            <input type=\"text\" class=\"form-control q input-long\" placeholder=\""
    + escapeExpression(helpers.translate.call(depth0, "Search for media", {hash:{},data:data}))
    + "\">\n            <i class=\"ngri-spinner loader\"></i>\n        </div>\n    </div>\n\n</form>\n\n<div class=\"ngremotemedia-thumbs-scroll\">\n  <div class=\"ngremotemedia-thumbs\"></div>\n</div>\n\n<button class=\"btn btn-large btn-block load-more\" type=\"button\">\n    <i class=\"ngri ngri-spinner loader\"></i> "
    + escapeExpression(helpers.translate.call(depth0, "Load more", {hash:{},data:data}))
    + "\n</button>\n";
  return buffer;
  });
templates['item'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, escapeExpression=this.escapeExpression, functionType="function", self=this;

function program1(depth0,data) {
  
  var buffer = "";
  buffer += "\n            <span class=\"share\">"
    + escapeExpression(helpers.translate.call(depth0, "Shared", {hash:{},data:data}))
    + "</span>\n            ";
  return buffer;
  }

  buffer += "<div class=\"item\">\n    <a class=\"pick\" data-id=\""
    + escapeExpression(((stack1 = (depth0 && depth0.id)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\">\n        <img src=\""
    + escapeExpression(((stack1 = (depth0 && depth0.url)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\" />\n        <p class=\"meta\">"
    + escapeExpression(((stack1 = (depth0 && depth0.filename)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "<br />\n            <span class=\"details\">"
    + escapeExpression(((stack1 = (depth0 && depth0.width)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + " x "
    + escapeExpression(((stack1 = (depth0 && depth0.height)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "</span>\n            ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.shared), {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n        </p>\n    </a>\n</div>\n";
  return buffer;
  });
templates['modal'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  


  return "<div class=\"backdrop\"/>\n<div class=\"content\">\n    <a href=\"#\" class=\"js-close close\"></a>\n    <div class=\"in\"></div>\n</div>\n";
  });
templates['nohits'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", escapeExpression=this.escapeExpression;


  buffer += "<div class=\"well well-large\">\n    <h2>"
    + escapeExpression(helpers.translate.call(depth0, "No results", {hash:{},data:data}))
    + "</h2>\n</div>\n";
  return buffer;
  });
templates['scaledversion'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, escapeExpression=this.escapeExpression, self=this, functionType="function";

function program1(depth0,data) {
  
  var buffer = "";
  buffer += "\n    title=\""
    + escapeExpression(helpers.translate.call(depth0, "Image is to small for this version", {hash:{},data:data}))
    + "\"\n";
  return buffer;
  }

function program3(depth0,data) {
  
  
  return "\n    <i class=\"ngri-warning\"></i>\n    ";
  }

  buffer += "<a\n";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.tooSmall), {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n>\n    ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.tooSmall), {hash:{},inverse:self.noop,fn:self.program(3, program3, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n    "
    + escapeExpression(((stack1 = (depth0 && depth0.name)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "<br />\n    <small>"
    + escapeExpression(((stack1 = (depth0 && depth0.possibleWidth)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "x"
    + escapeExpression(((stack1 = (depth0 && depth0.possibleHeight)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "</small>\n</a>\n";
  return buffer;
  });
templates['scaler'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, escapeExpression=this.escapeExpression, functionType="function", self=this;

function program1(depth0,data) {
  
  var buffer = "";
  buffer += "\n        <button class=\"btn btn-primary js-generate\">"
    + escapeExpression(helpers.translate.call(depth0, "Generate", {hash:{},data:data}))
    + "</button>\n    ";
  return buffer;
  }

function program3(depth0,data) {
  
  var buffer = "";
  buffer += "\n        <button class=\"btn btn-primary js-save\">"
    + escapeExpression(helpers.translate.call(depth0, "Save all", {hash:{},data:data}))
    + "</button>\n    ";
  return buffer;
  }

  buffer += "<div class=\"customattributes\"></div>\n\n<section class=\"ngremotemedia-crop\">\n    <ul class=\"nav nav-pills inverted\"></ul>\n</section>\n\n<div class=\"ngremotemedia-crop-container\">\n    <div class=\"image-wrap\">\n        <img src=\""
    + escapeExpression(((stack1 = (depth0 && depth0.media)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\" />\n    </div>\n</div>\n\n\n<div class=\"ngremote-actions\">\n    <button class=\"btn btn-default js-close\">"
    + escapeExpression(helpers.translate.call(depth0, "Cancel", {hash:{},data:data}))
    + "</button>\n    ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.singleVersion), {hash:{},inverse:self.program(3, program3, data),fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n</div>\n";
  return buffer;
  });
templates['scalerattributes'] = template(function (Handlebars,depth0,helpers,partials,data) {
  this.compilerInfo = [4,'>= 1.0.0'];
helpers = this.merge(helpers, Handlebars.helpers); data = data || {};
  var buffer = "", stack1, functionType="function", escapeExpression=this.escapeExpression, self=this;

function program1(depth0,data) {
  
  var buffer = "", stack1;
  buffer += " value=\""
    + escapeExpression(((stack1 = (depth0 && depth0.alttext)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\"";
  return buffer;
  }

function program3(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n    <div class=\"form-group\">\n        <label for=\"ngrm-sa-2\">"
    + escapeExpression(helpers.translate.call(depth0, "Class", {hash:{},data:data}))
    + "</label>\n        <select id=\"ngrm-sa-2\" name=\"cssclass\" class=\"form-control\">\n            <option value=\"\"> - </option>\n            ";
  stack1 = helpers.each.call(depth0, (depth0 && depth0.classes), {hash:{},inverse:self.noop,fn:self.program(4, program4, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n        </select>\n    </div>\n    ";
  return buffer;
  }
function program4(depth0,data) {
  
  var buffer = "", stack1;
  buffer += "\n                <option value=\""
    + escapeExpression(((stack1 = (depth0 && depth0.value)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "\"";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.selected), {hash:{},inverse:self.noop,fn:self.program(5, program5, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += ">"
    + escapeExpression(((stack1 = (depth0 && depth0.name)),typeof stack1 === functionType ? stack1.apply(depth0) : stack1))
    + "</option>\n            ";
  return buffer;
  }
function program5(depth0,data) {
  
  
  return " selected";
  }

  buffer += "<div class=\"well control-group\">\n    <div class=\"form-group\">\n        <label for=\"ngrm-sa-1\">"
    + escapeExpression(helpers.translate.call(depth0, "Alt text", {hash:{},data:data}))
    + "</label>\n        <input id=\"ngrm-sa-1\" name=\"alttext\" type=\"text\" class=\"form-control\" placeholder=\""
    + escapeExpression(helpers.translate.call(depth0, "Alternate text", {hash:{},data:data}))
    + "\" ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.alttext), {hash:{},inverse:self.noop,fn:self.program(1, program1, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += ">\n    </div>\n\n    ";
  stack1 = helpers['if'].call(depth0, (depth0 && depth0.classes), {hash:{},inverse:self.noop,fn:self.program(3, program3, data),data:data});
  if(stack1 || stack1 === 0) { buffer += stack1; }
  buffer += "\n\n</div>\n";
  return buffer;
  });
})();