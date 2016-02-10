require.config({
    map: {
        jcrop: {
            'jquery': 'jquery-safe'
        }
    },
    shim: {
        jcrop: {
            exports: 'jQuery.fn.Jcrop'
        }
    },
    paths: {
        'remotemedia': '/extension/ngremotemedia/design/ezexceed/javascript',
        'jcrop': '/extension/ngremotemedia/design/standard/javascript/libs/jquery.jcrop.min'
    }
});