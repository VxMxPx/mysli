class mysli.web.ui.overlay extends mysli.web.ui.widget

    ui = mysli.web.ui
    template = '<div class="ui-overlay ui-widget"><div class="ui-overlay-busy"><i class="fa fa-cog fa-spin"></i></div></div>'

    constructor: (caller) ->
        super
        @elements.push $(template)
        if 'parent' of caller
            parent = caller.parent()
            parent.prepend @get_element()

    set_busy: (state) ->
        if state
            @get_element().addClass 'state-busy'
        else
            @get_element().removeClass 'state-busy'

    get_busy: ->
        @get_element().hasClass 'state-busy'

    show: ->
        @get_element().fadeIn()

    hide: ->
        @get_element().fadeOut 400

    destroy: ->
        @get_element().fadeOut 400, =>
            super
