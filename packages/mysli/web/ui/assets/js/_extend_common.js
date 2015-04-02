/**
 * Style mixin.
 * @param  {Array} allowed list of allowrd styles.
 * @return {Function}
 */
mysli.web.ui._.extend.style = function (allowed, call) {

    if (typeof call === 'undefined') {
        call = 'style';
    }

    /**
     * Get/Set Style.
     * @param  {String} style
     * @return {String}
     */
    return function (style) {
        var classes, i, current;
        if (typeof style !== 'undefined') {
            this.$element.removeClass(this[call]());
            if (style in allowed) {
                this.$element.addClass("style-"+style);
            } else {
                throw new Error("Invalid style: `"+style+"`, please use one of the following: "+allowed.join(', '));
            }
        } else {
            classes = this.$element.className.split(' ');
            for (i = classes.length - 1; i >= 0; i--) {
                if (classes[i].substr(0, 6) === 'style-') {
                    current = classes[i].substr(6);
                    if (current in allowed) {
                        return current;
                    }
                }
            }
        }
    };
};

/**
 * Get/Set disabled state of an element.
 * @param  {Boolean} state
 * @return {Boolean}
 */
mysli.web.ui._.extend.disable = function (state) {
    if (typeof state !== 'undefined') {
        this.$element.prop('disabled', !!state);
    } else {
        return this.$element.prop('disabled');
    }
};

/**
 * Get/set to be flat state.
 * @param  {Boolean} value
 * @return {Boolean}
 */
mysli.web.ui._.extend.flat = function (value) {
    if (typeof value !== 'undefined') {
        if (value) {
            this.$element.addClass('style-flat');
        } else {
            this.$element.removeClass('style-flat');
        }
    } else {
        return this.$element.hasClass('style-flat');
    }
};
