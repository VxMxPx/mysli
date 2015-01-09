class mysli.web.ui.panel extends mysli.web.ui.widget

    ui = mysli.web.ui
    template = '<div class="ui-panel ui-widget" />'
    dimensions =
        small: 160
        default: 260
        big: 500
        huge: 800

    constructor: (id, options={}) ->
        if not id
            throw new Error("Panel's ID is required")

        super(options)
        @elements.push $(template)

        # Events
        # On away status change
        # => ( boolean status, integer width, object this )
        @events['away-change'] = {}
        # On size changed
        # => ( object size {width: int, height: int, size: str, diff: int}, boolean element_exists, object this )
        @events['size-change'] = {}
        # On popout status changed
        # => ( boolean value, object this )
        @events['popout-change'] = {}
        # On insensitive status changed
        # => ( boolean value, object this )
        @events['insensitive-change'] = {}
        # On min_size value changed
        # => ( string size, integer size, object this )
        @events['min-size-change'] = {}
        # On focus changed
        # => ( boolean value, object this )
        @events['focus-change'] = {}
        # On expandable status changed
        # => ( boolean value, object this )
        @events['expandable-change'] = {}
        # list of connected panels
        @connected_panels = []
        @properties = ui.util.merge_options options,
            # position in px from left
            position: 0
            # when there's a lot of panels, they start being pushed aside
            # and partly hidden
            offset: 0
            # weather panel is locked
            locked: false
            # weather panel can be expanded to fill the available space
            expandable: false
            # how much panel's width was increased (only if expandable is true)
            expanded_for: 0
            # for how much can panel shrink (if 0 it can't shrink)
            min_size: false
            # panel size by word
            size: 'default'
            # panel's size by px
            width: 0
            # is panel in away mode
            away: false
            # if away on blur, then panel will go away when lose focus
            away_on_blur: false
            # the width (px) of panel when away
            away_width: 10
            # if insensitive, then panel cannot be focused
            insensitive: false
            # if panel is popout
            popout: false
            # Weather panel is in focus
            focused: false
            # Weather panel can be flipped (back side exists!)
            flippable: false

        # when true, some events will be prevented on the panel,
        # like further animations
        @closing = false
        # when panel goes to full screen highest zIndex is set, this is the
        # original zIndex, to be restored, when full screen is turned off
        @old_zindex = 0
        @old_width  = 0

        @set_id id

        ui.util.apply_options @properties, this,
            set_min_size: 'min_size',
            set_size: 'size'

        @connect 'click', @set_focus.bind(this, true)

        # sides
        @front = new ui.panel_side('front')
        @get_element().append(@front.get_element())

        if @properties.flippable
            @back = new ui.panel_side('back')
            @get_element().append(@back.get_element())
        else
            @back = false

    ###
    Animate all the changes made to the element.
    ###
    animate: (callback) ->
        if @closing
            return

        @get_element().stop(true, false).animate
            left: @properties.position + @properties.offset
            width: @properties.width + @properties.expanded_for
            opacity: 1
            400, 'swing', =>
                if typeof callback == 'function'
                    callback.call(this)

    ###
    Set size by word. Please see dimensions for available sizes
    @param {string} size
    ###
    set_size: (size) ->
        size_diff = 0
        if typeof dimensions[size] == 'undefined'
            throw new Error("Invalid value for size: `#{size}`")

        @properties.size = size
        size_diff = -(@properties.width - dimensions[size])
        @properties.width = dimensions[size]

        @trigger 'size-change', [{width: @properties.width, height: 0, size: size, diff: size_diff}]
    ###
    Get panel's size
    @returns {object} {width: int, height: int, size: string}
    ###
    get_size: ->
        return {
            width: @properties.width
            height: 0
            size: @properties.size
        }


    ###
    Set panel's away status.
    @param {boolean} status
    ###
    set_away: (status) ->
        if status
            if @get_focus() || @properties.away
                @properties.away_on_blur = true
                return
            @properties.away = true
            width = -(@properties.width - @properties.away_width)
        else
            if not @properties.away
                @away_on_blur = false
                return
            @properties.away = false
            @properties.away_on_blur = false
            width = @properties.width - @properties.away_width

        @trigger 'away-change', [status, width]
    ###
    Get panel's away status
    @returns {boolean}
    ###
    get_away: ->
        return @properties.away

    ###
    Set panel's popout status
    @param {boolean} value
    ###
    set_popout: (value, size='huge') ->
        # calling it twice with the same value will mess things
        if value == @get_popout
            return

        if value
            @properties.popout = true
            @set_focus true
            @old_zindex = @get_element().css('z-index')
            @old_width = @properties.width
            @get_element().css('z-index', 10005)
            @properties.width = dimensions[size]
            @animate
        else
            @properties.popout = false
            @get_element().css('z-index', @old_zindex)
            @properties.width = @old_width

        @trigger 'popout-change', [value]
    ###
    Get popout status
    @returns {boolean}
    ###
    get_popout: ->
        return @properties.popout

    ###
    Set insensitive status
    @param {boolean} value
    ###
    set_insensitive: (value) ->
        if value
            if @get_focus()
                @set_focus false
            @properties.insensitive = true
        else
            @properties.insensitive = false

        @trigger 'insensitive-change', [value]
    ###
    Get insensitive status
    @returns {boolean}
    ###
    get_insensitive: ->
        return @properties.insensitive

    ###
    Set panel's miz size
    @param {string} size
    ###
    set_min_size: (size) ->
        if size and typeof dimensions[size] == 'undefined'
            throw new Error("Not a valid size value: `#{size}`")

        @properties.min_size = if size then dimensions[size] else false
        @trigger 'min-size-change', [size, dimensions[size]]
    ###
    Get panel's min size
    @returns {integer}
    ###
    get_min_size: ->
        return @properties.min_size

    ###
    Set panel's focus
    @param {boolean} value
    ###
    set_focus: (value) ->
        if value == @properties.focused
            return

        if value
            @properties.focused = true
            @get_element().addClass('focused')
            if @properties.away
                @set_away false
                @properties.away_on_blur = true
        else
            @properties.focused = false
            @get_element().removeClass('focused')
            if @properties.away_on_blur
                @set_away true

        @trigger 'focus-change', [value]
    ###
    Get current focus status
    @returns {boolean}
    ###
    get_focus: ->
        return @properties.focused

    ###
    Set expandable status
    @param {boolean} value
    ###
    set_expandable: (value) ->
        @properties.expandable = value
        @trigger 'expandable-change', [value]
    ###
    Get expandable status
    @returns {boolean}
    ###
    get_expandable: ->
        return @properties.expandable

    ###
    Destroy/remove this panel
    ###
    destroy: ->
        if @locked
            return

        @insensitive = true
        @closing = true

        # TODO: Remove children if implemented!

        @get_element().stop(true, false).animate
            left: (@properties.position + @properties.offset) - (@get_size().width + @properties.expanded_for) - 10,
            opacity: 0
            400, 'swing', =>
                super
