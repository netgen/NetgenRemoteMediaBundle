RemoteMedia.views.Scalebox = Backbone.View.extend({
    outerBounds : null,

    initialize : function(options)
    {
        this.outerBounds = options.outer;
        return this;
    },

    render : function()
    {
        var w = 26, h = 26, min = 8;
        var size = this.boxSize(this.model, w, h, min);

        var css = {
            width : size[0],
            height : size[1],
            'margin-left' : parseInt(((w - size[0]) / 2), 10),
            'margin-top' : parseInt(((h - size[1]) / 2), 10)
        };

        this.$el.css(css);
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
        if (min.w * scale < minIconWidthHeight)
        {
            addMinimum = minIconWidthHeight;
            scale = scale * ((maxIconWidth-minIconWidthHeight)/maxIconWidth);
            addToWidth = true;

            // if the shortest downscale still is too small, recalculate
            if (min.h * scale < minIconWidthHeight)
            {
                scale = (scale /((maxIconWidth-minIconWidthHeight)/maxIconWidth)) * ((maxIconHeight-minIconWidthHeight)/maxIconHeight);
                addToWidth = false;
            }
        }
        else if (min.h * scale < minIconWidthHeight)
        {
            // if the shortest downscale gets smaller than given minimum icon size, recalculate
            addToWidth = false;
            addMinimum = minIconWidthHeight;
            scale = scale * ((maxIconHeight-minIconWidthHeight)/maxIconHeight);
        }

        // calc icon sizes
        var w = size[0], h = size[1];

        if (addToWidth)
        {
            width = (w * scale) + addMinimum;
            height = Math.round(width * (h/w));
            width = Math.round(width);

            if (height>maxIconHeight)
            {
                height = maxIconHeight;
                width = Math.round(height * (w/h));
            }
        }
        else
        {
            height = (h*scale)+addMinimum;
            width = Math.round(height * (w/h));
            height = Math.round(height);

            if (width>maxIconWidth)
            {
                width = maxIconWidth;
                height = Math.round(width * (h/w));
            }
        }

        return [width, height];
    }

});
