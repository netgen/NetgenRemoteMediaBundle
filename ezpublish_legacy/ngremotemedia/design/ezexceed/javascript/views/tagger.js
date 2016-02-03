define(['remotemedia/view', 'remotemedia/templates/tag'], function(View, Tag) {
    return View.extend({
        bindKeysScoped: true,

        initialize: function() {
            _.bindAll(this);
            console.log('initialize !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!')
            this.collection = this.model.get('tags');
            // this.listenTo(this.collection, 'add remove', this.save);
            return this;
        },

        events: {
            'change input:text': 'inputChange',
            'keyup input:text':   'inputChange',
            'click button.tag': 'add',
            'click .tags button.close': 'remove'
        },

        keys: {
            'enter input:text': 'add'
        },

        saved: function() {
            this.trigger('saved');
            this.renderTags();
        },

        remove: function(e) {
            console.log('remove !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
            e.preventDefault();
            e.stopPropagation();
            this.trigger('save');
            var target = this.$(e.currentTarget);
            var tag_name = target.data('tag');
            // var tag = this.collection.get(tag_name);
            this.collection.remove(tag_name);
            this.model.remove_tag(tag_name).success(this.saved);
        },

        add: function(e) {
            console.log('add !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
            e.preventDefault();
            e.stopPropagation();
            this.trigger('save');
            var tag_name = this.$input.val();
            this.collection.add({
                tag: tag_name
            });
            this.$input.val('').focus();
            this.model.add_tag(tag_name).success(this.saved);
        },

        inputChange: function(e) {
            e.preventDefault();
            e.stopPropagation();
            var val = this.$input.val();
            this.$button.attr('disabled', val.length === 0);
            return this;
        },

        render: function() {
            this.$list = this.$('.tags');
            this.$input = this.$('input:text');
            this.$button = this.$('button.tag');
            this.renderTags();
            return this;
        },

        // Render tags
        renderTags: function() {
            var html = this.collection.map(function(tag) {
                return Tag(tag.toJSON());
            }, this);
            this.$('.tags').html(html);
        }
    });
});