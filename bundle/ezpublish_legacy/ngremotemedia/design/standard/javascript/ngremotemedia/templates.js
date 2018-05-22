(function() {
  var template = Handlebars.template, templates = window.NgRemoteMedia.JST = window.NgRemoteMedia.JST || {};
templates['alert'] = template({"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    return "<p>"
    + container.escapeExpression(helpers.translate.call(depth0 != null ? depth0 : (container.nullContext || {}),"The image is too small for this crop",{"name":"translate","hash":{},"data":data}))
    + "</p>\n";
},"useData":true});
templates['browser'] = template({"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    var alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.escapeExpression, alias3=container.lambda;

  return "<form onsubmit=\"javascript: return false;\" class=\"form-search\">\n\n\n    <div class=\"form-group\">\n        <label for=\"ngremotemedia-remote-media-type-select\">"
    + alias2(helpers.translate.call(alias1,"Media type",{"name":"translate","hash":{},"data":data}))
    + ":</label>\n        <select class=\"ngremotemedia-remote-media-type-select\" id=\"ngremotemedia-remote-media-type-select\">\n            <option value=\"image\">"
    + alias2(helpers.translate.call(alias1,"Image",{"name":"translate","hash":{},"data":data}))
    + "</option>\n            <option value=\"video\">"
    + alias2(helpers.translate.call(alias1,"Video",{"name":"translate","hash":{},"data":data}))
    + "</option>\n        </select>\n    </div>\n\n    <div class=\"form-group\">\n        <label for=\"ngremotemedia-remote-folder-select\">"
    + alias2(helpers.translate.call(alias1,"Folder",{"name":"translate","hash":{},"data":data}))
    + ":</label>\n        <select class=\"ngremotemedia-remote-folders\" id=\"ngremotemedia-remote-folder-select\">\n            <option class=\"loading\">"
    + alias2(helpers.translate.call(alias1,"Loading...",{"name":"translate","hash":{},"data":data}))
    + "</option>\n            <option value=\"all\">"
    + alias2(helpers.translate.call(alias1,"All",{"name":"translate","hash":{},"data":data}))
    + "</option>\n        </select>\n    </div>\n\n    <div class=\"form-group\">\n        <span class=\"upload-container\" id=\"ngremotemedia-browser-local-file-container-"
    + alias2(alias3((depth0 != null ? depth0.id : depth0), depth0))
    + "\">\n            <button type=\"button\" class=\"btn btn-primary upload upload-from-disk\" id=\"ngremotemedia-browser-local-file-"
    + alias2(alias3((depth0 != null ? depth0.id : depth0), depth0))
    + "\">\n                "
    + alias2(helpers.translate.call(alias1,"Upload new media",{"name":"translate","hash":{},"data":data}))
    + "\n            </button>\n        </span>\n\n        <div class=\"with-loader\">\n            <input type=\"text\" class=\"form-control q input-long\" placeholder=\""
    + alias2(helpers.translate.call(alias1,"Search for media",{"name":"translate","hash":{},"data":data}))
    + "\">\n            <i class=\"ngri-spinner loader\"></i>\n        </div>\n        <span class=\"ngrm-by\">\n            "
    + alias2(helpers.translate.call(alias1,"by",{"name":"translate","hash":{},"data":data}))
    + "\n            <label><input type=\"radio\" value=\"name\" name=\"search_type\" checked=\"checked\" /> "
    + alias2(helpers.translate.call(alias1,"name",{"name":"translate","hash":{},"data":data}))
    + " </label>\n            <label><input type=\"radio\" value=\"tag\" name=\"search_type\" /> "
    + alias2(helpers.translate.call(alias1,"tag",{"name":"translate","hash":{},"data":data}))
    + " </label>\n        </span>\n    </div>\n\n</form>\n\n<div class=\"ngremotemedia-thumbs-scroll\">\n    <div class=\"ngremotemedia-thumbs\"></div>\n\n\n    <button class=\"btn btn-default btn-large btn-block load-more\" type=\"button\" style=\"display:none;\">\n        <i class=\"ngri ngri-spinner loader\"></i> "
    + alias2(helpers.translate.call(alias1,"Load more",{"name":"translate","hash":{},"data":data}))
    + "\n    </button>\n</div>\n";
},"useData":true});
templates['item'] = template({"1":function(container,depth0,helpers,partials,data) {
    return "            <img src=\""
    + container.escapeExpression(container.lambda((depth0 != null ? depth0.url : depth0), depth0))
    + "\" />\n";
},"3":function(container,depth0,helpers,partials,data) {
    return "            <i class=\"ngri-video ngri-big\"></i>\n";
},"5":function(container,depth0,helpers,partials,data) {
    return "            <i class=\"ngri-book ngri-big\"></i>\n";
},"7":function(container,depth0,helpers,partials,data) {
    return "            <span class=\"share\">"
    + container.escapeExpression(helpers.translate.call(depth0 != null ? depth0 : (container.nullContext || {}),"Shared",{"name":"translate","hash":{},"data":data}))
    + "</span>\n";
},"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1, alias1=container.lambda, alias2=container.escapeExpression, alias3=depth0 != null ? depth0 : (container.nullContext || {});

  return "<div class=\"item\">\n    <a class=\"pick\" data-id=\""
    + alias2(alias1((depth0 != null ? depth0.id : depth0), depth0))
    + "\">\n"
    + ((stack1 = helpers["if"].call(alias3,(depth0 != null ? depth0.is_image : depth0),{"name":"if","hash":{},"fn":container.program(1, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + ((stack1 = helpers["if"].call(alias3,(depth0 != null ? depth0.is_video : depth0),{"name":"if","hash":{},"fn":container.program(3, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + "\n"
    + ((stack1 = helpers["if"].call(alias3,(depth0 != null ? depth0.is_other : depth0),{"name":"if","hash":{},"fn":container.program(5, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + "        <p class=\"meta\">"
    + alias2(alias1((depth0 != null ? depth0.filename : depth0), depth0))
    + "<br />\n            <span class=\"details\">"
    + alias2(alias1((depth0 != null ? depth0.width : depth0), depth0))
    + " x "
    + alias2(alias1((depth0 != null ? depth0.height : depth0), depth0))
    + "</span>\n"
    + ((stack1 = helpers["if"].call(alias3,(depth0 != null ? depth0.shared : depth0),{"name":"if","hash":{},"fn":container.program(7, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + "        </p>\n    </a>\n</div>\n";
},"useData":true});
templates['modal'] = template({"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    return "<div class=\"backdrop\"/>\n<div class=\"content\">\n    <a href=\"#\" class=\"js-close close\"></a>\n    <div class=\"in\"></div>\n</div>\n";
},"useData":true});
templates['nohits'] = template({"1":function(container,depth0,helpers,partials,data) {
    return "    <h2 class=\"ngrm-title ngrm-big\"><i class=\"ngri-spinner ngri-spin\"></i> "
    + container.escapeExpression(helpers.translate.call(depth0 != null ? depth0 : (container.nullContext || {}),"Loading...",{"name":"translate","hash":{},"data":data}))
    + "</h2>\n";
},"3":function(container,depth0,helpers,partials,data) {
    return "    <h2 class=\"ngrm-title ngrm-big\">"
    + container.escapeExpression(helpers.translate.call(depth0 != null ? depth0 : (container.nullContext || {}),"No results",{"name":"translate","hash":{},"data":data}))
    + "</h2>\n";
},"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1;

  return "\n"
    + ((stack1 = helpers["if"].call(depth0 != null ? depth0 : (container.nullContext || {}),(depth0 != null ? depth0.loading : depth0),{"name":"if","hash":{},"fn":container.program(1, data, 0),"inverse":container.program(3, data, 0),"data":data})) != null ? stack1 : "");
},"useData":true});
templates['scaledversion'] = template({"1":function(container,depth0,helpers,partials,data) {
    return "    title=\""
    + container.escapeExpression(helpers.translate.call(depth0 != null ? depth0 : (container.nullContext || {}),"Image is to small for this version",{"name":"translate","hash":{},"data":data}))
    + "\"\n";
},"3":function(container,depth0,helpers,partials,data) {
    return "    <i class=\"ngri-warning\"></i>\n";
},"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.lambda, alias3=container.escapeExpression;

  return "<a\n"
    + ((stack1 = helpers["if"].call(alias1,(depth0 != null ? depth0.tooSmall : depth0),{"name":"if","hash":{},"fn":container.program(1, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + ">\n"
    + ((stack1 = helpers["if"].call(alias1,(depth0 != null ? depth0.tooSmall : depth0),{"name":"if","hash":{},"fn":container.program(3, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + "    "
    + alias3(alias2((depth0 != null ? depth0.name : depth0), depth0))
    + "<br />\n    <small>"
    + alias3(alias2((depth0 != null ? depth0.possibleWidth : depth0), depth0))
    + "x"
    + alias3(alias2((depth0 != null ? depth0.possibleHeight : depth0), depth0))
    + "</small>\n</a>\n";
},"useData":true});
templates['scaler'] = template({"1":function(container,depth0,helpers,partials,data) {
    return "        <button class=\"btn btn-primary js-generate\">"
    + container.escapeExpression(helpers.translate.call(depth0 != null ? depth0 : (container.nullContext || {}),"Generate",{"name":"translate","hash":{},"data":data}))
    + "</button>\n";
},"3":function(container,depth0,helpers,partials,data) {
    return "        <button class=\"btn btn-primary js-save\">"
    + container.escapeExpression(helpers.translate.call(depth0 != null ? depth0 : (container.nullContext || {}),"Save all",{"name":"translate","hash":{},"data":data}))
    + "</button>\n";
},"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1, alias1=depth0 != null ? depth0 : (container.nullContext || {});

  return "<div class=\"customattributes\"></div>\n\n<section class=\"ngremotemedia-crop\">\n    <ul class=\"nav nav-pills inverted\"></ul>\n</section>\n\n<div class=\"ngremotemedia-crop-container\">\n    <div class=\"image-wrap\"></div>\n</div>\n\n\n<div class=\"ngremote-actions\">\n    <button class=\"btn btn-default js-close\">"
    + container.escapeExpression(helpers.translate.call(alias1,"Cancel",{"name":"translate","hash":{},"data":data}))
    + "</button>\n"
    + ((stack1 = helpers["if"].call(alias1,(depth0 != null ? depth0.singleVersion : depth0),{"name":"if","hash":{},"fn":container.program(1, data, 0),"inverse":container.program(3, data, 0),"data":data})) != null ? stack1 : "")
    + "</div>\n";
},"useData":true});
templates['scalerattributes'] = template({"1":function(container,depth0,helpers,partials,data) {
    return " value=\""
    + container.escapeExpression(container.lambda((depth0 != null ? depth0.alttext : depth0), depth0))
    + "\"";
},"3":function(container,depth0,helpers,partials,data) {
    return " value=\""
    + container.escapeExpression(container.lambda((depth0 != null ? depth0.caption : depth0), depth0))
    + "\"";
},"5":function(container,depth0,helpers,partials,data) {
    var stack1, alias1=depth0 != null ? depth0 : (container.nullContext || {});

  return "    <div class=\"form-group\">\n        <label for=\"ngrm-sa-3\">"
    + container.escapeExpression(helpers.translate.call(alias1,"Class",{"name":"translate","hash":{},"data":data}))
    + "</label>\n        <select id=\"ngrm-sa-3\" name=\"cssclass\" class=\"form-control\">\n            <option value=\"\"> - </option>\n"
    + ((stack1 = helpers.each.call(alias1,(depth0 != null ? depth0.classes : depth0),{"name":"each","hash":{},"fn":container.program(6, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + "        </select>\n    </div>\n";
},"6":function(container,depth0,helpers,partials,data) {
    var stack1, alias1=container.lambda, alias2=container.escapeExpression;

  return "                <option value=\""
    + alias2(alias1((depth0 != null ? depth0.value : depth0), depth0))
    + "\""
    + ((stack1 = helpers["if"].call(depth0 != null ? depth0 : (container.nullContext || {}),(depth0 != null ? depth0.selected : depth0),{"name":"if","hash":{},"fn":container.program(7, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + ">"
    + alias2(alias1((depth0 != null ? depth0.name : depth0), depth0))
    + "</option>\n";
},"7":function(container,depth0,helpers,partials,data) {
    return " selected";
},"compiler":[7,">= 4.0.0"],"main":function(container,depth0,helpers,partials,data) {
    var stack1, alias1=depth0 != null ? depth0 : (container.nullContext || {}), alias2=container.escapeExpression;

  return "<div class=\"well control-group\">\n    <div class=\"form-group\">\n        <label for=\"ngrm-sa-1\">"
    + alias2(helpers.translate.call(alias1,"Alternate text",{"name":"translate","hash":{},"data":data}))
    + "</label>\n        <input id=\"ngrm-sa-1\" name=\"alttext\" type=\"text\" class=\"form-control\" "
    + ((stack1 = helpers["if"].call(alias1,(depth0 != null ? depth0.alttext : depth0),{"name":"if","hash":{},"fn":container.program(1, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + ">\n    </div>\n\n    <div class=\"form-group\">\n        <label for=\"ngrm-sa-2\">"
    + alias2(helpers.translate.call(alias1,"Caption",{"name":"translate","hash":{},"data":data}))
    + "</label>\n        <input id=\"ngrm-sa-2\" name=\"caption\" type=\"text\" class=\"form-control\" "
    + ((stack1 = helpers["if"].call(alias1,(depth0 != null ? depth0.caption : depth0),{"name":"if","hash":{},"fn":container.program(3, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + ">\n    </div>\n\n"
    + ((stack1 = helpers["if"].call(alias1,(depth0 != null ? depth0.classes : depth0),{"name":"if","hash":{},"fn":container.program(5, data, 0),"inverse":container.noop,"data":data})) != null ? stack1 : "")
    + "\n</div>\n";
},"useData":true});
})();