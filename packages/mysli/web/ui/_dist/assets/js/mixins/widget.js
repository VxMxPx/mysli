mysli.web.ui.mixins.widget = (function () {

    var ui = mysli.web.ui,
        ids_pool = {};

    /// Set busy state for the button
    /// @param {boolean} state
    function set_busy(state) {
        if (state === true) {
            if (this.busy) {
                return;
            }
            this.trigger('busy-change', [state]);
            this.busy = new ui.overlay();
            this.busy.set_position(this.get_position());
            this.busy.set_dimension(this.get_dimension());

            this.connect(
                'destroy*widget.overlay',
                this.busy.destroy.bind(this.busy));
            this.connect(
                'position-change*widget.overlay',
                function (__, position) {
                    this.busy.set_position(position);
                }
            );
            this.connect(
                'size-change*widget.overlay',
                function (__, size) {
                    this.busy.set_size(size);
                }
            );

            this.busy.show();
        } else {
            this.disconnect('*widget.overlay');
            this.trigger('busy-change', [state]);
            this.busy = this.busy.destroy();
        }
    }
    /// Get busy state for the button
    /// @returns {mixed} ui.overlay|false
    function get_busy() {
        return this.busy;
    }
    /// Set main element's position (offset).
    /// @param {object} {top:int, left:int}
    function set_position(position) {

        var element = this.elements[0];
        this.trigger('position-change', [position, !!element]);

        if (element) {
            element.offset(position);
        }
    }
    /// Get main element's positin (offset).
    /// @returns {object} {top:int, left:int}|false
    function get_position() {
        if (typeof this.elements[0] === 'object') {
            return this.elements[0].offset();
        }
    }
    /// Set main element's size.
    /// @param {object} size {width: int, height: int}
    function set_size(size) {

        var element = this.elements[0];

        if (element) {
            if (size.width) {
                element.css('width', size.width);
            }
            if (size.height) {
                element.css('height', size.height);
            }
        }

        this.trigger('size-change', [size, !!element]);
    }
    /// Get main element's size.
    /// @returns {object} {width: int, height: int}
    function get_size() {
        var element = this.elements[0];

        if (element) {
            return {
                width  : element.outerWidth(),
                height : element.outerHeight()
            };
        }
    }
    /// You can set an ID only once for particular object
    /// ID must be unique amoung all object.
    /// @param {string} id
    function set_id(id) {
        if (this.id) {
            throw new Error("You cannot change ID once it was set: `"+id+"`");
        }
        if (typeof ids_pool[id] !== 'undefined') {
            throw new Error("Item with such ID already exists: `"+id+"`");
        }
        this.elements[0].attr('id', id);
        this.id = id;
        ids_pool[id] = this;
    }
    /// Get ID for this object
    /// @returns {string} | false if not set
    function get_id() {
        return this.id;
    }
    /// Get primary DOM element for this widget.
    /// @returns {object}
    function get_element() {
        return this.elements[0];
    }
    /// Destroy this widget, please note: this will destroy all elements
    /// in DOM, trigger 'destroy', and clear connected events.
    /// You still need to manually delete(ref) afer that.
    function destroy() {
        this.trigger('destroy');

        if (this.id) {
            delete ids_pool[this.id];
        }

        for (var event in this.events) {
            this.events[event] = {};
        }

        for (var i = this.elements.length - 1; i >= 0; i--) {
            this.elements[i].remove();
        }

        return false;
    }

    return function () {

        mysli.web.ui.mixins.event.call(this);

        this.events = {
            // On busy changed
            // => ( boolean state, object this )
            'busy-change'     : {},
            // On position changed
            // => ( object position, boolean elementExists, object this )
            'position-change' : {},
            // On size changed
            // => ( object size, boolean elementExists, object this )
            'size-change'     : {},
            // When this widget is added to a container
            // => ( object container, object this )
            'added'           : {},
            // When this widget is removed from a container
            // => ( object container, object this )
            'removed'         : {},
            // On destroy called
            // => ( object this )
            'destroy'         : {}
        };

        this.elements = [];
        this.busy = false;
        this.id = false;
        this.parent = false;

        // Export functions
        this.set_busy     = set_busy;
        this.get_busy     = get_busy;
        this.set_position = set_position;
        this.get_position = get_position;
        this.set_size     = set_size;
        this.get_size     = get_size;
        this.set_id       = set_id;
        this.get_id       = get_id;
        this.get_element  = get_element;
        this.destroy      = destroy;

        return this;
    };

}());
