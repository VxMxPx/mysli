class mysli.web.ui.titlebar extends mysli.web.ui.box

    ui = mysli.web.ui

    constructor: (options={}) ->
        options.orientation = ui.const.VERTICAL

        @options = ui.util.merge_options options,
            style: ui.const.STYLE_DEFAULT,

        super(options)
        @get_element().addClass('ui-titlebar')

        ui.util.apply_options @options, this,
            set_style: ['style']

    ###
    Set titlebar style
    @param {string}  style default|flat
    ###
    set_style: (style) ->
        if style == 'flat'
            @get_element().addClass('style-flat')
        else
            @get_element().removeClass('style-flat')

    ###
    Get button's style
    @returns {string}
    ###
    get_style: ->
        classes = @get_element()[0].className.split ' '
        for class_name in classes
            if class_name.substr(0, 6) == 'style-'
                return class_name.substr(6)
