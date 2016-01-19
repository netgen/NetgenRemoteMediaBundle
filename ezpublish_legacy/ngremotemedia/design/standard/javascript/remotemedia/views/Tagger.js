RemoteMedia.views.Tagger = Backbone.View.extend({

    // underscore templates, is compile in initialize
    templates : {
        tag : '<li><%-tag%><span class="remove"><a data-tag="<%-tag%>">x</a></span></li>'
    },

    initialize : function(options)
    {
        _.bindAll(this, 'render', 'tag', 'update');
        _(this.templates).each(function(template, name) {
            if (_.isString(template))
                this.templates[name] = _.template(template);
        }, this);
        this.model.bind('change', this.update);
        return this;
    },

    // Listen to model changes and update tag list when model changes
    update : function(model) {
        var tags = this.model.get('tags'), html = '';
        _(tags).each(function(tag) {
            html += this.templates.tag({tag: tag});
        }, this);
        this.$('ul').html(html);
    },

    events : {
        'change .tagedit' : 'inputChange',
        'keyup .tagedit' : 'inputChange',
        'click .tagit' : 'tag',
        'submit .tagedit' : 'tag',
        'click .remove a' : 'remove'
    },

    remove : function(e) {
        var clicked = $(e.currentTarget), tag = clicked.data('tag');
        this.model.removeTag(tag).saveAttr();
    },

    inputChange : function(e) {
        var val = this.input.val();
        if (val)
            this.button.removeClass('disabled').attr('disabled', null);
        else
            this.button.addClass('disabled').attr('disabled', true);
        return this;
    },

    render : function(media) {
        this.list = this.$('ul');
        this.input = this.$('.tagedit');
        this.button = this.$('button');
        this.update(this.model);
        this.inputChange();
        return this;
    },

    tag : function(e) {
        e.preventDefault();
        var tag = this.input.val();
        this.model.addTag(tag).saveAttr();
        this.input.val('');
    }
});
