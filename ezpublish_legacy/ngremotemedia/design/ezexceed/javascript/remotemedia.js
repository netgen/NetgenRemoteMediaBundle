define(['shared/datatype', 'remotemedia/views/main', 'remotemedia/config'], function(Base, MainView) {

    return Base.extend({
        initialize: function(options) {
            _.bindAll(this);
            this.init(options);
            _.extend(this, _.pick(options, ['version']));

            this.view = new MainView({
                el: options.el,
                id: options.objectId,
                version: options.version
            }).on('save', this.save, this);
        },

        render: function() {
            this.view.render();
            return this;
        },

        save: function(id, data) {
            var value = {};
            value[id] = data;

            var values = {};
            values[this.version] = {
                attributes: value
            };

            this.model.once('autosave.saved', this.saved, this);
            this.model.manualSave(values);
        },

        saved: function(model, response) {
            if (response && _(response).has('attributes') && _(response.attributes).has(this.attributeId)) {
                this.view.trigger('saved');
            }
        }
    });
});