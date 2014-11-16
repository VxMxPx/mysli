mysli.web.ui.mixins.container = (function () {

    var ui = mysli.web.ui;

    /// Add a new element to the container
    /// @param   {object} element
    /// @returns {integer} id
    function add(element) {

        element.parent = this;

        element.trigger('added', [this]);
        this.trigger('add', [element]);

        var id = this.container.elements.push(element)-1;

        if (element.get_id()) {
            this.container.ids[element.get_id()] = id;
        }
        element.connect('destroy*container.add', function (id) {
            this.remove(id);
        }.bind(this, id));

        element.elements[0].addClass('contained-element-n-'+id);
        this.elements[0].append(element.elements[0]);
    }
    /// Remove an element from a container
    /// @param {mixed} id string (element's internal id)|integer
    function remove(id) {

        idn = get_internal_id(id);
        var element = this.get(idn);

        this.trigger('remove', [element]);
        element.trigger('removed', [this]);
        element.disconnect('*container.add');
        element.parent = false;

        delete this.container.elements[id];
        if (idn !== id) {
            delete this.container.ids[id];
        }

        this.elements[0].find('.contained-element-n-'+idn).remove();
    }
    /// Get element by id.
    /// @param   {mixed} id string (element's internal id)|integer
    /// @returns {mixed} object|false
    function get(id) {
        return this.container.elements[get_internal_id(id)];
    }

    /// Get internal element's ID
    /// @param   {mixed} id string (element's internal id)|integer
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
        ui.mixins.widget.call(this);

        // Container append an element
        // => ( object element, object this )
        this.events.add    = {};
        // Container removed an element
        // => ( object element, object this )
        this.events.remove = {};

        // Contained elements
        this.container = {
            elements : [],
            ids      : {}
        };

        this.add = add;
        this.remove = remove;
        this.get = get;

        return this;
    };

}());
