define(['remotemedia/view', 'remotemedia/templates/tag'], function(View, Tag)
{
    return View.extend({
        bindKeysScoped : true,

        initialize : function(options)
        {
            _.bindAll(this);
            this.collection = this.model.get('tags');
            this.collection.on('add remove', this.save, this);
            return this;
        },

        events : {
            'change input:text' : 'inputChange',
            'keyup input:text' : 'inputChange',
            'click button.tag' : 'add',
            'click .tags button.close' : 'remove'
        },

        keys : {
            'enter input:text' : 'add'
        },

        // Save any ad
        save : function()
        {
            this.trigger('save');
            this.model.save().success(this.saved);
        },

        saved : function()
        {
            this.trigger('saved');
            this.renderTags();
        },

        remove : function(e) {
            e.preventDefault();
            e.stopPropagation();
            var target = this.$(e.currentTarget);
            var tag = target.data('tag');
            this.collection.remove(this.collection.get(tag));
        },

        add : function(e) {
            e.preventDefault();
            e.stopPropagation();
            var tag = this.$input.val();
            this.collection.add({
                id : tag,
                tag : tag
            });
            this.$input.val('').focus();
        },

        inputChange : function(e) {
            e.preventDefault();
            e.stopPropagation();
            var val = this.$input.val();
            this.$button.attr('disabled', val.length === 0);
            return this;
        },

        render : function(media) {
            this.$list = this.$('.tags');
            this.$input = this.$('input:text');
            this.$button = this.$('button.tag');
            this.renderTags();
            return this;
        },

        // Render tags
        renderTags : function() {
            var html = this.collection.map(function(tag)
            {
                return Tag(tag.toJSON());
            }, this);
            this.$('.tags').html(html);
        }
    });
});
