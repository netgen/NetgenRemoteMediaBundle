require.config({
    map: {
        jcrop: {
            'jquery': 'jquery-safe'
        }
    },
    shim: {
        jcrop: {
            exports: 'jQuery.fn.Jcrop'
        },
        brigthcove: {
            exports: 'brightcove'
        }
    },
    paths: {
        'remotemedia': '/extension/ngremotemedia/design/ezexceed/javascript',
        'brightcove': '/extension/ngremotemedia/design/standard/javascript/libs/BrightcoveExperiences',
        'jcrop': '/extension/ngremotemedia/design/standard/javascript/libs/jquery.jcrop.min'
    }
});