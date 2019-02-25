// Namespace declarations
// Prepares namespace for objects to be placed into later

(function(){

    var translateString = function(key) {
        var t = NgRemoteMediaTranslations[key];
        if(t){return t;}
        //console.warn('Unregistered translation: ', key);
        return key;
    };

    Handlebars.registerHelper('translate', function(value) {
        return translateString(value);
    });



    var NgRemoteMedia = {
        models : {},
        views : {},
        t: translateString,
        template: function(template, context){
            return NgRemoteMedia.JST[template](context);
        }
    };

    // Expose
    window.NgRemoteMedia = NgRemoteMedia;

})();
