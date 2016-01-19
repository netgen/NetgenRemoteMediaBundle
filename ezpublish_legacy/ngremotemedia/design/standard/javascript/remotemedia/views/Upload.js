RemoteMedia.views.Upload = Backbone.View.extend({

    browseButton : null,
    browseContainer : null,
    maxSize : '25mb',

    headers : {
        'Accept' : 'application/json, text/javascript, */*; q=0.01'
    },

    initialize : function(options)
    {
        _.bindAll(this);
        if (_(options).has('uploadContainer'))
            this.browseButton = options.uploadContainer;
        this.browseButton = _(options).has('browseButton') ? options.browseButton : 'remotemedia-local-file-' + this.model.id;
        this.browseContainer = _(options).has('browseContainer') ? options.browseContainer : 'remotemedia-local-file-container-' + this.model.id;
        this.options = options;
        this.uploadCallback = options.uploaded;
        return this;
    },

    url : function()
    {
        return this.options.prefix + '/remotemedia::upload';
    },

    uploaded : function(up, file, info)
    {
        if (!('response' in info)) return true;

        try
        {
            var data = $.parseJSON(info.response);
        } catch (e)
        {
            if (this.uploadCallback)
            {
                this.uploadCallback({
                    refresh : true
                });
            }
            return true;
        }

        if ('content' in data && 'media' in data.content)
        {
            var media = data.content.media;
            if (this.uploadCallback)
            {
                this.uploadCallback({
                    id : media.id,
                    host : media.host,
                    type : media.type,
                    ending : media.scalesTo.ending,
                    media : media
                });
            }
        }

        //this.$('.upload-progress').fadeOut();

        return this;
    },

    progress : function(up, file)
    {
        this.$('.progress').css('width', file.percent + '%');
    },

    added : function(up, files)
    {
        up.start();
        // Show loader
        this.$('.loader').removeClass('hide');
        this.$('.pictures-icon, .upload-container').addClass('hide');
        //this.$('.upload-progress').show();
    },

    render : function(response)
    {
        var button = this.$('#' + this.browseButton);
        button.val(button.val() + ' (Max ' + this.maxSize + ')');

        var settings = {
            runtimes : 'html5,flash,html4',
            container : this.browseContainer,
            flash_swf_url : '/extension/remotemedia/design/standard/javascript/libs/plupload/Moxie.swf',
            browse_button : this.browseButton,
            max_file_size : this.maxSize,
            url : this.url(),
            multipart_params : {
                'AttributeID' : this.model.id,
                'ContentObjectVersion' : this.options.version,
                'http_accept' : 'json' //Because of some strange failing when html4 is used
            },
            headers : this.headers
        };

        var $formtoken = $('#ezxform_token_js');
        if ($formtoken.length > 0) {
            // Hack to inject ezformtoken in upload request
            settings.multipart_params.ezxform_token = $formtoken.attr('title');
        }
        this.uploader = new plupload.Uploader(settings);
        this.uploader.init();
        this.uploader.bind('FileUploaded', this.uploaded);
        this.uploader.bind('UploadProgress', this.progress);
        this.uploader.bind('FilesAdded', this.added);
        return this;
    }
});
