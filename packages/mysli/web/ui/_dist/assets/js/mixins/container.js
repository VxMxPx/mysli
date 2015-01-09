mysli.web.ui.mixins.container = (function () {

    var ui = mysli.web.ui;

    /// Add a new widget to the container
    /// @param   {object} widget
    /// @returns {integer} id
    function add(widget) {

        widget.parent = this;

        widget.trigger('added', [this]);
        this.trigger('add', [widget]);

        var id = this.container.elements.push(widget)-1;

        if (widget.get_id()) {
            this.container.ids[widget.get_id()] = id;
        }
        widget.connect('destroy*container.add', function (id) {
            this.remove(id);
        }.bind(this, id));

        widget.get_element().addClass('contained-widget-n-'+id);
        this.target.append(widget.get_element());

        return id;
    }
    /// Remove an widget from a container
    /// @param {mixed} id string (widget's internal id)|integer
    function remove(id) {

        idn = get_internal_id(id);
        var widget = this.get(idn);

        this.trigger('remove', [widget]);
        widget.trigger('removed', [this]);
        widget.disconnect('*container.add');
        widget.parent = false;

        delete this.container.elements[id];
        if (idn !== id) {
            delete this.container.ids[id];
        }

        this.get_element().find('.contained-widget-n-'+idn).remove();
    }
    /// Get widget by id.
    /// @param   {mixed} id string (widget's internal id)|integer
    /// @returns {mixed} object|false
    function get(id) {
        return this.container.elements[get_internal_id(id)];
    }

    /// Get internal widget's ID
    /// @param   {mixed} id string (widget's internal id)|integer
    /// @returns {integer}
    function get_internal_id(id) {
        if (typeof id === 'number') {
            return id;
        }
        if (typeof this.container.ids[id] !== 'undefined') {
            return this.container.ids[id];
        }
    }

    return function () {
        // Extend...
        ui.mixins.widget.call(this);

        // Container append an element
        // => ( object element, object this )
        this.events.add    = {};
        // Container removed an element
        // => ( object element, object this )
        this.events.remove = {};

        // Contained elements
        this.container = {
            master   : null,
            target   : null,
            elements : [],
            ids      : {}
        };
        this.container.master = this.get_element();
        this.container.target = this.get_element();

        // Public methods
        this.add = add;
        this.remove = remove;
        this.get = get;

        return this;
    };

}());
