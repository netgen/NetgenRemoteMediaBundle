/* eslint-disable prefer-arrow-callback */
(function (tinymce) {
    function insertMediaCallback(data, caption, cssClass) {
        var imageUrl = null;
        if (data.type === 'image') {
            if (data.variation_url !== null) {
                imageUrl = data.variation_url;
            } else if (data.url !== null) {
                imageUrl = data.url;
            }
        } else if (data.type === 'video' && data.thumbnail_url !== null) {
            imageUrl = data.thumbnail_url;
        }

        let html = '<img type="custom" src="'+imageUrl !== null ? imageUrl : '/extension/ezoe/design/standard/images/tango/image-x-generic22.png'+'"'
            + 'data-mce-src="'+imageUrl !== null ? imageUrl : '/extension/ezoe/design/standard/images/tango/image-x-generic22.png'+'"'
            + 'customattributes=\'caption|'+caption+'attribute_separationcssclass|'+cssClass+'attribute_separationcoords|'+JSON.stringify(data.image_variations)
            + 'attribute_separationresourceId|'+data.resourceId+'attribute_separationresourceType|'+data.type+'attribute_separationimage_url|'+imageUrl
            + 'attribute_separationvariation|'+data.selected_variation+'\'"'+'class="ezoeItemCustomTag ngremotemedia" style="">';

        tinymce.execCommand('mceInsertContent', false, html);
    }

    tinymce.PluginManager.add("ngremotemedia", function (editor) {
        const fieldId = editor.settings.ez_attribute_id;

        window[`remoteMedia` + fieldId].setEditorInsertCallback(insertMediaCallback);

        // Add a button that opens a modal
        editor.addButton("ngremotemedia", {
            title: "Insert remote media",
            onclick() {
                let attributeType = tinymce.activeEditor.selection.getNode().getAttribute('type');
                let attributeString = tinymce.activeEditor.selection.getNode().getAttribute('customattributes');
                let hasNgrmClass = tinymce.activeEditor.selection.getNode().classList.contains('ngremotemedia');

                var data = {};
                if (attributeType === 'custom' && hasNgrmClass === true && attributeString) {
                    let attributes = attributeString.split('attribute_separation');

                    attributes.forEach(function (attribute) {
                        let attributeKey = attribute.split('|')[0];
                        let attributeValue = attribute.split('|')[1];

                        if (attributeKey === 'coords' || attributeKey === 'image_variations') {
                            try {
                                data['image_variations'] = JSON.parse(attributeValue);
                            } catch(e) {
                                data['image_variations'] = {};
                            }

                            return;
                        }

                        data[attributeKey] = attributeValue;
                    });
                }

                window[`remoteMedia` + fieldId].openEditorInsertModal(data);
            },
        });

        return {
            getMetadata() {
                return {
                    name: "Netgen remote media",
                    url: "https://github.com/netgen/NetgenRemoteMediaBundle",
                };
            },
        };
    });
})(tinymce);
