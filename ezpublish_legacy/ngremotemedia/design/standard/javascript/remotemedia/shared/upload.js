window.RemoteMediaShared || (window.RemoteMediaShared = {});

window.RemoteMediaShared.upload = function($, plupload){
  return {
        maxSize: '25mb',

        headers: {
            'Accept': 'application/json, text/javascript, */*; q=0.01'
        },

        initialize: function(options) {
            _.bindAll(this);
            this.options = options;

            this.browseButton = $('.upload-from-disk');
            this.uploadCallback = options.uploaded;

            return this;
        },

        uploaded: function(up, file, info) {
            if (!info || !info.response || !this.uploadCallback) { return this; }

            var model_attributes;

            try {
                model_attributes = $.parseJSON(info.response);
            } catch (e) {
                alert("Error while uploading file.");
                this.uploadCallback(false);
                return this;
            }

            
            this.uploadCallback({
                model_attributes: model_attributes,
                new_image_selected: true
            });

            return this;
        },

        progress: function(up, file) {
            this.$('.progress').css('width', file.percent + '%');
        },

        added: function(up /*, files*/) {
            up.start();
            this.trigger('uploading');
        },

        render: function(/*response*/) {
            var id = this.model.id !== "ezoe" ? this.model.id : this.model.get('attributeId');
            var settings = {
                runtimes: 'html5,flash,html4',
                browse_button: this.browseButton.get(0),
                flash_swf_url: RemoteMediaShared.config().plupload_swf,
                max_file_size: this.maxSize,
                url: '/ezexceed/ngremotemedia/upload/' +  RemoteMediaShared.config().currentObjectId,
                multipart_params: {
                    legacy: RemoteMediaShared.config().is_admin,
                    user_id: RemoteMediaShared.config().user_id,
                    AttributeID: id,
                    ContentObjectVersion: this.options.version,
                    http_accept: 'json' //Because of some strange failing when html4 is used
                },
                headers: this.headers
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
    

  };
};