class mysli.web.ui.box extends mysli.web.ui.container

    ui = mysli.web.ui
    template = '<div class="ui-box ui-widget"></div>'

    constructor: (options) ->
        super

        @elements.push $(template)

        @orientation = options.orientation || ui.const.HORIZONTAL

        if @orientation == ui.const.VERTICAL
            row = $('<div class="row" />')
            @get_element().append row
            @container.master = @container.target = row
        else
            @container.master = @container.target = @get_element()

    ###
    Add widget to the box
    @param {object}  widget
    @param {boolean} expand weather cell should expand to max width / height
    @returns {integer} id
    ###
    push: (widget, expand=false) ->

        expanded = if expand then 'expanded' else 'collapsed'

        if @orientation == ui.const.HORIZONTAL
            wrapper = $("<div class=\"row #{expanded}\"><div class=\"cell\" /></div>")
            target = wrapper.find('.cell')
        else
            wrapper = $("<div class=\"cell #{expanded}\" />")
            target = wrapper

        # method = if position == ui.const.START then 'prepend' else 'append'
        method = 'append'

        @container.master[method](wrapper)
        @container.target = target

        return super(widget)
