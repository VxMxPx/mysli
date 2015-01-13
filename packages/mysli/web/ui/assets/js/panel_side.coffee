class mysli.web.ui.panel_side extends mysli.web.ui.container

    template = '<div class="ui-widget ui-panel-container ui-panel-side" />'
    ui = mysli.web.ui

    constructor: (id='front', options) ->

        @options = ui.util.merge_options options,
            style: ui.const.STYLE_DEFAULT

        super(@options)

        @elements.push $(template)
        @container.master = @container.target = @elements[0]
        @elements[0].addClass("ui-panel-side-type-#{id}")

        ui.util.apply_options @options, this,
            set_style: ['style']

    ###
    Set panels side's style
    @param {string}  style default|alt
    ###
    set_style: (style='default') ->
        current_style = "style-#{@get_style()}"
        @get_element().removeClass(current_style)

        @get_element().addClass \
        switch style
            when ui.const.STYLE_DEFAULT   then 'style-default'
            when ui.const.STYLE_ALT       then 'style-alt'
            else throw new Error("Invalid style: `#{style}`")

    ###
    Get button's style
    @returns {string}
    ###
    get_style: ->
        classes = @get_element()[0].className.split ' '
        for class_name in classes
            if class_name.substr(0, 6) == 'style-'
                return class_name.substr(6)
