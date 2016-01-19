define(['remotemedia/view', 'jquery-safe', 'plupload/plupload'],
    function(View, $, plupload)
{
    return View.extend({
        browseButton : null,
        browseContainer : null,
        maxSize : '25mb',

        headers : {
            'Accept' : 'application/json, text/javascript, */*; q=0.01'
        },

        initialize : function(options)
        {
            _.bindAll(this);
            if (_(options).has('uploadContainer')) {
                this.uploadContainer = options.uploadContainer;
            }
            this.browseButton = _(options).has('browseButton') ? options.browseButton : 'remotemedia-local-file-' + this.model.id;
            this.browseContainer = _(options).has('browseContainer') ? options.browseContainer : 'remotemedia-local-file-container-' + this.model.id;
            this.options = options;
            this.uploadCallback = options.uploaded;
            return this;
        },

        uploaded : function(up, file, info)
        {
            if (!info || !info.response) {
                return true;
            }

            var data;
            try {
                data = $.parseJSON(info.response);
            }
            catch (e) {
                if (this.uploadCallback) {
                    this.uploadCallback({
                        refresh : true
                    });
                }
                return true;
            }

            if (data && data.content && data.content.media) {
                var media = data.content.media;
                if (this.uploadCallback) {
                    this.uploadCallback({
                        id : media.id,
                        host : media.host,
                        type : media.type,
                        ending : media.scalesTo.ending,
                        media : media
                    });
                }
            }

            this.$('.upload-progress').fadeOut();

            return this;
        },

        progress : function(up, file) {
            this.$('.progress').css('width', file.percent + '%');
        },

        added : function(up, files) {
            up.start();
            this.trigger('uploading');
            this.$('.upload-progress').show();
        },

        render : function(response) {
            var button = this.$('#' + this.browseButton);
            var id = this.model.id !== "ezoe" ? this.model.id : this.model.get('attributeId');
            var settings = {
                runtimes : 'html5,flash,html4',
                container : this.browseContainer,
                flash_swf_url : eZExceed.config.plupload.flash_swf_url,
                browse_button : this.browseButton,
                max_file_size : this.maxSize,
                url : this.model.urlRoot + '/remotemedia::upload',
                multipart_params : {
                    'AttributeID' : id,
                    'ContentObjectVersion' : this.options.version,
                    'http_accept' : 'json' //Because of some strange failing when html4 is used
                },
                headers : this.headers
            };

            if ($('#ezxform_token_js').length) {
                // Ugly hack to go with ezformtoken
                settings.multipart_params.ezxform_token = $('#ezxform_token_js').attr('title');
            }
            this.uploader = new plupload.Uploader(settings);
            this.uploader.init();
            this.uploader.bind('FileUploaded', this.uploaded);
            this.uploader.bind('UploadProgress', this.progress);
            this.uploader.bind('FilesAdded', this.added);
            return this;
        }
    });
});
