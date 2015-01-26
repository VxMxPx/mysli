class mysli.web.ui.widget extends mysli.web.ui.event

    ui = mysli.web.ui
    ids_pool = {}

    constructor: (options) ->
        super

        @native_events =
            'click': 0
            'mouse-enter': 0
            'mouse-leave': 0
            'mouse-move': 0
            'mouse-out': 0
            'mouse-over': 0
            'mouse-up': 0

        @events =
            # When widget is clicked
            # => ( object event, object this )
            'click': {}
            # When mouse cursor enter (parent) widget
            # => ( object event, object this )
            'mouse-enter': {}
            # When mouse cursor leave (parent) widget
            # => ( object event, object this )
            'mouse-leave': {}
            # When mouse cursor move over widget
            # => ( object event, object this )
            'mouse-move': {}
            # When mouse cursor move out of the widget (even to child)
            # => ( object event, object this )
            'mouse-out': {}
            # Mouse enter (even when enter to child element)
            # => ( object event, object this )
            'mouse-over': {}
            # Mouse up
            # => ( object event, object this )
            'mouse-up': {}
            # On busy changed
            # => ( boolean state, object this )
            'busy-change': {}
            # On disable state change
            # => ( boolean state, object this )
            'disable-change': {}
            # On position changed
            # => ( object position, boolean element_exists, object this )
            'position-change': {}
            # On size changed
            # => ( object size, boolean element_exists, object this )
            'size-change': {}
            # When this widget is added to a container
            # => ( object container, object this )
            'added': {}
            # When this widget is removed from a container
            # => ( object container, object this )
            'removed': {}
            # On destroy called
            # => ( object this )
            'destroyed': {}

        @destroyed = false
        @elements = []
        @busy = false
        @id = false
        @parent = false

        @options = ui.util.merge_options options,
            disabled: false

    ###
    Overload event connect, to better handle native events (like mouse events)
    @param  {string}   event name
    @param  {function} callback
    @return {string}   id
    ###
    connect: (event, callback) ->
        id = super(event, callback)

        [event, _] = @extract_event_name(event)

        if typeof @native_events[event] != 'undefined'
            @native_events[event]++
            # If more than one, this event was already set, and we need only one
            if @native_events[event] == 1
                dom_event  = event.replace '-', ''
                @get_element().on dom_event, @trigger.bind(this, event)

        return id

    ###
    Overload event disconnect, to better handle native events
    @param   {string} id full id, or specified unique id (eg *my_id)
    @returns {boolean}
    ###
    disconnect: (id) ->
        if typeof id != 'object' && id.substr(0, 1) == '*'
            id = id + "*"
            for event of @events
                for eid of @events[event]
                    if eid.substr(0, id.length) == id
                        disconnect_native event, this
                        super event, id
            return true
        else
            if typeof id != 'object'
                event = id.split('--', 2)[0]
            else
                event = id[0]
                id = id[1]

            if typeof @events[event] != 'undefined'
                disconnect_native event, this
                return super event, id

    ###
    Set busy state for the button
    @param {boolean} state
    ###
    set_busy: (state) ->
        if state == true
            if @busy
                return

            @trigger 'busy-change', [state]

            @busy = new ui.overlay(this.get_element())

            @connect 'destroyed*widget.overlay', @busy.destroy.bind(@busy)
            @connect 'position-change*widget.overlay', (__, position) ->
                @busy.set_position position
            @connect 'size-change*widget.overlay', (__, size) ->
                @busy.set_size size
            @busy.set_busy true
            @busy.show()
        else
            @disconnect '*widget.overlay'
            @trigger 'busy-change', [state]
            @busy = @busy.destroy()
    ###
    Get busy state for the button
    @returns {mixed} ui.overlay|false
    ###
    get_busy: ->
        return @busy

    ###
    Set disable state
    @param {boolean} state
    ###
    set_disabled: (state) ->
        @trigger 'disable-change', [state]
        @get_element().prop 'disabled', state
    ###
    Get disabled state
    @returns {boolean}
    ###
    get_disabled: ->
        return @get_element().prop 'disabled'

    ###
    Set main element's position (offset).
    @param {object} {top:int, left:int}
    ###
    set_position: (position) ->
        element = @get_element()
        @trigger 'position-change', [position, !!element]

        if element
            element.offset position
    ###
    Get main element's positin (offset).
    @returns {object} {top:int, left:int}|false
    ###
    get_position: ->
        if typeof @get_element() == 'object'
            return @get_element().offset()

    ###
    Set main element's size.
    @param {object} size {width: int, height: int}
    ###
    set_size: (size) ->
        element = @get_element()

        if element
            if size.width
                element.css 'width', size.width
            if size.height
                element.css 'height', size.height

        @trigger 'size-change', [size, !!element]
    ###
    Get main element's size.
    @returns {object} {width: int, height: int}
    ###
    get_size: ->
        element = @get_element()
        if element
            width: element.outerWidth()
            height: element.outerHeight()

    ###
    You can set an ID only once for particular object
    ID must be unique amoung all object.
    @param {string} id
    ###
    set_id: (id) ->
        if @id
            throw new Error("You cannot change ID once it was set: `#{id}`")
        if typeof ids_pool[id] != 'undefined'
            throw new Error("Item with such ID already exists: `#{id}`")

        @get_element().attr 'id', id
        @id = id
        ids_pool[id] = this
    ###
    Get ID for this object
    @returns {string} | false if not set
    ###
    get_id: ->
        return @id

    ###
    Get primary DOM element for this widget.
    @returns {object}
    ###
    get_element: ->
        return @elements[0]

    ###
    Set parent
    @param {mixed} parent
    ###
    set_parent: (parent) ->
        if typeof parent == 'string'
            @parent = $(parent)
        else
            @parent = parent
    ###
    Get parent element
    @returns {object}
    ###
    get_parent: ->
        return @parent

    ###
    Show (append) an element (and all containing elements) to the parent
    For this to function, parent needs to be DOM element
    ###
    show: ->
        if typeof @parent.append != 'function'
            throw new Error('Parent has no function `append`')
        @parent.append @get_element()

    ###
    Destroy this widget, please note: this will destroy all elements in DOM,
    trigger 'destroy', and clear connected events.
    You still need to manually delete(ref) afer that.
    ###
    destroy: ->
        @trigger 'destroyed'

        for element in @elements
            element.remove()

        if @id
            delete ids_pool[@id]

        for event of @events
            @events[event] = {}

        @destroyed = true
        return false

    # --- Private ---

    ###
    Disconnect native event.
    @param {string} event
    ###
    disconnect_native= (event, context) ->
        if typeof context.native_events[event] != 'undefined'
            context.native_events[event]--
            if context.native_events[event] == 0
                dom_event  = event.replace '-', ''
                context.get_element().off dom_event
