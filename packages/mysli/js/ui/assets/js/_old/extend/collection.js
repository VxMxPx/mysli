mysli.js.ui.extend.collection = function(target, options) {

    var common = mysli.js.common;

    // Properties
    options = common.merge({
        target: '$element',
        wrapper: '<div class="ui-widget ui-cell collection-target" />',
        push: false,
        cellapi: false
    }, options);

    // Cell

    var cell = function (container, uid, $cell) {
        this.uid = uid;
        this.container = container;
        this.$cell = $cell;

        // Properties
        this._.toggle_animation = 'slideUp';
        this._.visibility = true;
    };

    cell.prototype = {

        constructor: cell,

        /**
         * Change cell visibility.
         * @param  {boolean} status
         * @return {boolean}
         */
        visible: function(status) {
            if (typeof status !== 'undefined') {
                if (this.visibility !== status) {
                    this._.visibility = !!status;
                    if (this._.visibility) {
                        this.$cell.show();
                    } else {
                        this.$cell[this._.toggle_animation]();
                    }
                }
            } else {
                return this._.visibility;
            }
        },

        /**
         * Remove the cell from the collection.
         */
        remove: function () {
            var element = this.container.collection.get(this.uid);
            delete element.collection_uid;
            if (!options.wrapper) {
                element.$element.removeClass('coll-uid-'+uid);
            }
            this.container.collection.remove(this.uid);
            this.$cell.remove();
        }
    };

    if (options.cellapi) {
        for (var key in options.cellapi) {
            if (common.has_own_property(options.cellapi, key)) {
                cell.prototype[key] = options.cellapi[key];
            }
        }
    }

    // Define Methods

    /**
     * Push element to the collection.
     * If `uid` is not provided, element.uid will be used.
     * @param  {object} element
     * @param  {string} uid
     * @return {object} _element_
     */
    function push(element, uid) {
        return this.insert(element, -1, uid);
    }

    /**
     * Insert element to the collection, before `at`.
     * If `at` is not provided, element will be inserted
     * at the begining of the collection.
     * If `uid` is not provided, element.uid will be used.
     * @param  {object} element
     * @param  {mixed}  at either string (uid) or integer (index)
     * @param  {string} uid
     * @return {object} _element_
     */
    function insert(element, at, uid) {

        var r = true,
            wrapper = options.wrapper,
            pushable, at_index, class_id;

        // If no UID is provided, the element's uid will be used
        if (typeof uid === 'undefined') {
            uid = element.uid;
        }

        // Set collection uid (which might be different from uid itself)
        element.collection_uid = uid;

        // Either push after another element or at the end of the list
        if (at > -1) {
            at_index = this.collection.push_after(at, uid, element);
        } else {
            at_index = this.collection.push(uid, element);
        }

        // Costume push function
        if (typeof options.push === 'function') {
            r = options.push.call(this, element, at, uid);
            if (!r) {
                return element;
            } else if (typeof r === 'string') {
                wrapper = r;
            }
        }

        // If costume allows us to continue
        if (r) {
            class_id = 'coll-uid-'+uid;
            // Create wrapper, append at the end of the list
            if (wrapper) {
                pushable = $(wrapper);
                pushable.addClass(class_id);
                pushable.find('.collection-target').append(element.$element);
            } else {
                element.$element.addClass(class_id);
                pushable = element.$element;
            }
            // Either inster after particular element or just at the end
            if (at > -1) {
                this[options.target]
                    .find('.coll-uid-'+this.collection.get_from(at_index, -1).collection_uid)
                        .after(pushable);
            } else {
                this[options.target].append(pushable);
            }
        }

        return element;
    }

    /**
     * Get elements from the collection. If `cell` is provided, get cell itself.
     * @param  {mixed}   uid  either string (uid) or number (index)
     * @param  {boolean} cell weather to get cell itself rather than containing element.
     * @return {object}
     */
    function get(uid, cell) {
        if (cell && options.wrapper) {
            if (typeof uid === 'number') {
                uid = this.collection.get(uid).collection_uid;
            }
            return new cell(this, uid, this[options.target].find('.coll-uid-'+uid));
        } else {
            return this.collection.get(uid);
        }
    }

    // Append Methods

    if (target.prototype.hasOwnProperty('push')) {
        target.prototype.collection_push = push;
    } else {
        target.prototype.push = push;
    }

    if (target.prototype.hasOwnProperty('insert')) {
        target.prototype.collection_insert = insert;
    } else {
        target.prototype.insert = insert;
    }

    if (target.prototype.hasOwnProperty('get')) {
        target.prototype.collection_get = get;
    } else {
        target.prototype.get = get;
    }
};
