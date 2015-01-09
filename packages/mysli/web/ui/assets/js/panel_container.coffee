class mysli.web.ui.panel_container extends mysli.web.ui.container

    util = mysli.web.ui.util
    template = '<div class="ui-widget ui-panel-container" />'

    constructor: ->
        super
        @elements.push $(template)
        @container.master = @container.target = @elements[0]

        # sum of all panels (widths, px)
        @sum_size = 0
        # number of panels which are expandable
        @expandable_count = 0
        # currently selected panel (id)
        @active_id = false
        # weather panels are offseted (overflown)
        @offseted = 0
        # width of container
        @container_width = 0

        @set_parent 'body'
        @set_resize_with_window true
        @set_size_from_dom_element window

    ###
    Update sum size, when panel is added, remove or away
    @param {integer} value  positive or negative
    @param {string}  modify_before_id if provided, panels before this id, will
                                      update position to fit difference.
    ###
    update_sum: (value, modify_before_id=false) ->
        @sum_size = @sum_size + value
        if modify_before_id
            @each_after modify_before_id, (index, panel) ->
                panel.get_element().css('z-index', 10000 - index)
                panel.properties.position = panel.properties.position + value
                panel.animate()
                return

    ###
    Update view when panel is added/removed or window is resized.
    ###
    update_view: ->
        if not @active_id
            return

        overflow = @container_width - @sum_size
        overflow_part = if @expandable_count > 0 then Math.ceil(overflow / @expandable_count) else 0
        active_panel = @get(@active_id)
        screen_left = @container_width - active_panel.get_size().width
        overflow_percent = 100 - util.get_percent(screen_left, @sum_size - active_panel.get_size().width)
        offset_so_far = 0
        panel_calculated = 0

        if overflow_part <= 0
            overflow_part = overflow

        if overflow > 0
            overflow_percent = 0
            @offseted = false
        else
            @offseted = true

        @each (index, panel) ->

            if panel.get_away() && not panel.get_focus()
                panel.properties.expanded_for = 0
                panel.properties.offset = -(panel.get_size().width - panel.properties.away_width + offset_so_far)
                panel.animate()
                offset_so_far = offset_so_far + panel.get_size().width - panel.properties.away_width
                return

            if panel.properties.expandable
                if overflow > 0
                    panel.properties.offset = -(offset_so_far)
                    panel.properties.expanded_for = overflow_part
                    panel.animate()
                    offset_so_far += -(overflow_part)
                    return
                else
                    panel.properties.expanded_for = 0
                    panel.animate()

            if panel.get_focus()
                panel.properties.expanded_for = 0
                panel.properties.offset = -(offset_so_far)
                panel.animate()
                return

            # panelCalculated = Math.ceil(MU.Calc.setPercent(overflowPercent, panel.properties.width))
            panel_calculated = util.set_percent(overflow_percent, panel.get_size().width)

            # is shrinkable and still can be shrinked
            if panel.properties.min_size && panel.get_size().width + panel.properties.expanded_for > panel.properties.min_size
                # can whole offset be shrinked?
                if panel.properties.min_size > panel.get_size().width - panel_calculated
                    diff = panel_calculated - (panel.get_size().width - panel.properties.min_size)
                    panel.properties.expanded_for = -(diff)
                    panel.properties.offset = -(panel_calculated - diff + offset_so_far)
                    panel.animate()
                    offset_so_far += panel_calculated
                    return
                else
                    panel.properties.expanded_for = -(panel_calculated)
                    panel.properties.offset = -(offset_so_far)
                    panel.animate()
                    offset_so_far += panel_calculated
                    return

            panel.properties.expanded_for = 0
            panel.properties.offset = -(panel_calculated + offset_so_far)
            panel.animate()
            offset_so_far += panel_calculated
            return

    ###
    Will push a new panel to the collection.
    @param {object} panel
    ###
    push: (panel) ->
        super(panel, panel.get_id())
        @push_after false, panel

    ###
    Will push  new panel to the collection after particular ID.
    @param {string} after_id
    @param {object} panel
    ###
    push_after: (after_id, panel) ->
        if not panel instanceof mysli.web.ui.panel
            throw new Error('An object must be instance of `mysli.web.ui.panel`')

        if after_id
            size = @get(after_id).get_size().width
            @each_before after_id, (index, ipanel) ->
                ipanel.get_element().css('z-index', 10000 - index)
                size += ipanel.get_size().width
            panel.properties.position = size
        else
            panel.properties.position = @sum_size

        @update_sum panel.get_size().width

        panel.get_element().css({
            opacity: 0
            left: (panel.properties.position + panel.properties.offset) - (panel.get_size().width + panel.properties.expanded_for)
        })

        if after_id
            super(after_id, panel, panel.get_id())
            @get(after_id).get_element().css('z-index', 10000 - @get_index(after_id))

        index = @get_index panel.get_id()
        panel.get_element().css('z-index', 10000 - index)
        panel.connect 'focus-change', @switch_focus.bind(this)
        panel.set_focus(true)

        if panel.get_expandable()
            @expandable_count++

        panel.animate()

    # Remove panel from stack
    remove: (id) ->
        panel = @get(id)
        width = panel.get_size().width

        if panel.get_expandable()
            @expandable_count--

        @update_sum -(width), id

        if id == @active_id
            @active_panel = false
            new_panel = @get_from id, -1
            new_panel.set_focus true
        else
            @update_view()

        super id

    ###
    Element will resize according to window resize
    @param {boolean} status
    @param {integer} timeout
    ###
    set_resize_with_window: (status, timeout=500) ->
        if status
            $(window).on 'resize', =>
                if @resize_timer
                    clearTimeout @resize_timer
                @resize_timer = setTimeout @set_size_from_dom_element.bind(this, window), timeout
        else
            if @resize_timer
                clearTimeout @resize_timer

    ###
    Set element's size to DOM element's size
    @param {string} selector
    ###
    set_size_from_dom_element: (selector) ->
        width = $(selector).outerWidth()
        height = $(selector).outerHeight()
        @set_size
            width: width
            height: height
        @container_width = width
        @update_view()

    ###
    Remove old focus, and set new
    @param {boolean} status
    @param {object}  panel
    ###
    switch_focus: (status, panel) ->
        if status == true
            if @active_id != false
                @get(@active_id).set_focus(false)
            @active_id = panel.get_id()
            @update_view()
