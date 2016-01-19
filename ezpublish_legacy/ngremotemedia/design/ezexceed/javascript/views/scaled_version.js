define(['remotemedia/view', 'remotemedia/templates/scaledversion'], function(View, ScaledVersion)
{
    return View.extend({
        MAX_SIZE : 26,
        MIN_SIZE : 8,

        outerBounds : null,
        tagName : 'li',
        media : null,

        initialize : function(options)
        {
            options = (options || {});
            _.bindAll(this);
            _.extend(this, _.pick(options, ['outerBounds', 'media']));
        },

        render : function()
        {
            var modelSize = (this.model.size || [this.MAX_SIZE, this.MAX_SIZE]);
            var size = this.boxSize(modelSize, this.MAX_SIZE, this.MAX_SIZE, this.MIN_SIZE);

            var data = _.extend(this.model);

            data.width = _(data).has('size') ? data.size[0] : 0;
            data.height = _(data).has('size') ? data.size[1] : 0;

            /**
             * Check if media is large enough to fit the scaling
             */
            var file = this.media.get('file');
            data.toSmall = !(file.width >= data.width && file.height >= data.height);
            this.$el.html(ScaledVersion(data))
                .attr("id", "eze-remotemedia-scale-version-" + data.name.toLowerCase())
                .data('scale', this.model);

            this.$('.box').css({
                width : size[0],
                height : size[1],
                'margin-left' : parseInt(((this.MAX_SIZE - size[0]) / 2), 10),
                'margin-top' : parseInt(((this.MAX_SIZE - size[1]) / 2), 10)
            });

            return this;
        },

        boxSize : function (size, maxIconWidth, maxIconHeight, minIconWidthHeight)
        {
            var max = this.outerBounds.max, min = this.outerBounds.min;

            var minHeight, minWidth, maxHeight, maxWidth;
            var addMinimum = 0, addToWidth = false;

            // find the scale multiplier to fit the widest downscale into the max icon width
            var scale = maxIconWidth / max.w;

            // check if the tallest downscale fits into the max icon height width multiplier
            // recalculate multiplier to use height instead of width, to fit the tall downscale
            if (max.h * scale > maxIconHeight)
                scale = maxIconHeight / max.h;

            // if the narrowest downscale gets smaller than given minimum icon size, recalculate the scale so that all boxes is at least as wide as mimimum
            if (min.w * scale < minIconWidthHeight) {
                addMinimum = minIconWidthHeight;
                scale = scale * ((maxIconWidth-minIconWidthHeight)/maxIconWidth);
                addToWidth = true;

                // if the shortest downscale still is too small, recalculate
                if (min.h * scale < minIconWidthHeight) {
                    scale = (scale /((maxIconWidth-minIconWidthHeight)/maxIconWidth)) * ((maxIconHeight-minIconWidthHeight)/maxIconHeight);
                    addToWidth = false;
                }
            }
            else if (min.h * scale < minIconWidthHeight) {
                // if the shortest downscale gets smaller than given minimum icon size, recalculate
                addToWidth = false;
                addMinimum = minIconWidthHeight;
                scale = scale * ((maxIconHeight-minIconWidthHeight)/maxIconHeight);
            }

            // calc icon sizes
            var w = size[0], h = size[1];

            if (addToWidth) {
                width = (w * scale) + addMinimum;
                height = Math.round(width * (h/w));
                width = Math.round(width);

                if (height > maxIconHeight) {
                    height = maxIconHeight;
                    width = Math.round(height * (w/h));
                }
            }
            else {
                height = (h*scale)+addMinimum;
                width = Math.round(height * (w/h));
                height = Math.round(height);

                if (width > maxIconWidth) {
                    width = maxIconWidth;
                    height = Math.round(width * (h/w));
                }
            }

            return [width, height];
        }

    });
});
