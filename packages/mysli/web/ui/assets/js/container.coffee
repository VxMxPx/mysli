class mysli.web.ui.container extends mysli.web.ui.widget

    ui = mysli.web.ui

    constructor: ->
        super
        # Container append an element
        # => ( object element, object this )
        @events.add = {}
        # Container removed an element
        # => ( object element, object this )
        @events.remove = {}

        # Contained elements
        @container =
            icounter: 0
            master: null
            target: null
            stack: {}
            ids: []

    ###
    Push a new widget to the end of container
    @param   {object} widget
    @param   {string} id to access element later, if not provided, it will be
    automatically generated.
    @returns {object} widget
    ###
    push: (widget, id=null) ->
        widget.parent = this

        widget.trigger 'added', [this]
        @trigger   'add', [widget]

        id = @get_new_id() if not id

        widget.connect 'destroyed*container.add', =>
            @remove id

        widget.get_element().addClass "contained-widget-#{id}"

        if @container.target
            @container.target.append widget.get_element()
        else
            throw new Error("Target is undefined!")

        @container.stack[id] = widget
        @container.ids.push id

        return widget

    ###
    Pust a widget after particular id
    @param   {string} after_id
    @param   {object} widget
    @param   {string} id
    @returns {object} widget
    ###
    push_after: (after_id, widget, id=null) ->
        id = @get_new_id() if not id
        index_to = @get_index(after_id) + 1

        widget.parent = this

        widget.trigger 'added', [this]
        @trigger   'add', [widget]

        widget.connect 'destroyed*container.add', =>
            @remove id

        widget.get_element().addClass "contained-widget-#{id}"

        if @container.target
            @container.target.append widget.get_element()
        else
            throw new Error("Target is undefined!")

        @container.stack[id] = widget
        @container.ids.splice(index_to, 0, id)
        return widget

    ###
    Remove an widget from a container
    @param {string} id string
    ###
    remove: (id) ->
        widget = @get id

        @trigger   'remove', [widget]

        if not widget.destroyed
            widget.trigger 'removed', [this]
            widget.disconnect '*container.add'

        widget.parent = false

        delete @container.stack[id]
        @container.ids.splice(@get_index(id), 1)

        @get_element().find(".contained-widget-#{id}").remove()

    ###
    Get widget by id.
    @param   {string} id
    @returns {mixed} object|false
    ###
    get: (id) ->
        if typeof @container.stack[id] != 'undefined'
            return @container.stack[id]
        else
            return false

    ###
    Get index of particular widget by id
    @param   {string} id
    @returns {integer}
    ###
    get_index: (id) ->
        if typeof @container.ids.indexOf != 'function'
            for ide in @container.ids
                if ide == id
                    return ide
        else
            return @container.ids.indexOf(id)

    ###
    Get index n positions from id
    @param {string} id
    @param {integer} step
    @returns {integer}
    ###
    get_index_from: (id, step) ->
        id = @get_index(id)
        if id != false && id > 0
            return id + step

    ###
    Get widget n positions from id
    @param   {string} id
    @param   {integer} step
    @returns {mixed}
    ###
    get_from: (id, step) ->
        id = @get_index_from(id, step)
        if id != false
            return @get(@container.ids[id])
        else
            return false

    ###
    Number of widgets
    @returns {integer}
    ###
    count: -> @container.ids.length

    ###
    Get last widget
    @returns {mixed}
    ###
    get_last: -> @container.stack[@container.ids[@container.ids.length - 1]]

    ###
    Execute function for each widget.
    function (index, widget)
    break if function return any value (+ return that value)
    @param   {function} callback
    @returns {mixed}
    ###
    each: (callback) ->
        for id in @container.ids
            r = callback(id, @container.stack[id])
            if r != undefined && r != null
                return r

    ###
    Execute function for each widget, after particular id
    function (index, widget)
    break if function return any value (+ return that value)
    @param   {string} id
    @param   {function} callback
    @returns {mixed}
    ###
    each_after: (id, callback) ->
        i = @get_index_from(id, 1)
        while i < @container.ids.length
            r = callback(i, @container.stack[@container.ids[i]])
            if r != undefined && r != null
                return r
            else
                i++

    ###
    Execute function for each element, before particular id
    function (index, element)
    break if function return any value (+ return that value)
    @param   {string} id
    @param   {function} callback
    @returns {mixed}
    ###
    each_before: (id, callback) ->
        i = 0
        while i < @get_index(id)
            r = callback(i, @container.stack[@container.ids[i]])
            if r != undefined && r != null
                return r
            else
                i++

    ###
    Generate new unique ID for this container's element.
    @returns {string}
    ###
    get_new_id: ->
        @container.icounter++
        return "aid-#{@container.icounter}"
