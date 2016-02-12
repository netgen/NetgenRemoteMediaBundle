// Namespace declarations
// Prepares namespace for objects to be placed into later

RemoteMedia = {
    models : {},
    views : {}
};

var translateString = function(key) {
    var t = RemoteMediaTranslations[key];
    if(t){return t;}
    console.warn('Unregistered translation: ', key);
    return key;
};


Handlebars.registerHelper('translate', function(value) {
    var translateEntity = function(value) {
        /** Simple string that should be translated. */
        if (_(value).isString()){
            return translateString(value);
        }

        /** Single object. Return original text. */
        if (_(value).isObject()) {
            if (value.hasOwnProperty('quotes') && value.quotes){
                return '«' + value.text + '»';
            }else{
                return value.text;
            }
        }
    };

    if (_(value).isArray()){
        return _(value).map(translateEntity).join(' ');
    }else{
        return translateEntity(value);
    }
});