define(['remotemedia/view', 'remotemedia/templates/tag'], function(View, Tag) {
    return View.extend({
        bindKeysScoped: true,

        initialize: function() {
            _.bindAll(this);
            console.log('initialize !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!')
            this.collection = this.model.get('tags');

            this.listenTo(this.collection, 'add', function(model){
                this.model.add_tag(model.get('tag')).success(this.saved);
            });

            this.listenTo(this.collection, 'remove', function(model){
                this.model.remove_tag(model.get('tag')).success(this.saved);
            });            
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
            e.preventDefault();
            this.trigger('save');
            var tag_name = $(e.currentTarget).data('tag');
            this.collection.remove(tag_name);
        },

        add: function(e) {
            e.preventDefault();
            this.trigger('save');
            var tag_name = this.$input.val();
            this.collection.add({
                id: tag_name,
                tag: tag_name
            });
            this.$input.val('').focus();    
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