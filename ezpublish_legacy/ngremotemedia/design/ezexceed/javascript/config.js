require.config({
    map : {
        jcrop : {
            'jquery' : 'jquery-safe'
        }
    },
    shim : {
        jcrop : {
            exports : 'jQuery.fn.Jcrop'
        },
        brigthcove : {
            exports : 'brightcove'
        }
    },
    paths : {
        'remotemedia' : '/extension/remotemedia/design/ezexceed/javascript',
        'brightcove' : '/extension/remotemedia/design/standard/javascript/libs/BrightcoveExperiences',
        'jcrop' : '/extension/remotemedia/design/standard/javascript/libs/jquery.jcrop.min'
    }
});
