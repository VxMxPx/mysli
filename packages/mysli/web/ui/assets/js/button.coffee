class mysli.web.ui.button extends mysli.web.ui.widget

    template = '<button class="ui-button ui-widget" />'
    ui = mysli.web.ui

    ###
    @param {mixed}  label   label(string) | options(object)
    @param {object} options if label provided,
                            this parameter can be used for options
    ###
    constructor: (label, options={}) ->

        if typeof label == 'string'
            options.label = label
        else
            options = label

        @options = ui.util.merge_options options,
            label: null,
            icon: null,
            icon_position: ui.const.LEFT,
            icon_spin: false,
            style: ui.const.STYLE_DEFAULT,
            style_flat: false

        super(@options)

        @elements.push $(template)

        ui.util.apply_options @options, this,
            set_label: 'label',
            set_icon: ['icon', 'icon_position', 'icon_spin'],
            set_style: ['style', 'style_flat'],
            set_disabled: 'disabled'

    ###
    Set button's label
    @param {string} label
    ###
    set_label: (label) ->
        label_element = @get_element().find('span.label')

        if not label
            label_element.remove()
            return

        if not label_element.length
            label_element = $('<span class="label" />')
            method = if @options.icon_position == ui.const.RIGHT then 'prepend' else 'append'
            @get_element()[method](label_element)

        label_element.text label

    ###
    Get button's label
    @returns {string}
    ###
    get_label: ->
        return @get_element().find('span.label').text()

    ###
    Set button's icon. Use false to remove icon.
    @param {string}  icon check font-aswesome for available icons
    @param {string}  position ui.const.LEFT ui.const.RIGHT
    @param {boolean} spin weather icon should be animated (spin)
    ###
    set_icon: (icon, position, spin=false) ->
        icons = @get_element().find 'i.fa'
        icons.remove()

        if not icon
            return

        method = if position == ui.const.RIGHT then 'append' else 'prepend'
        spin = if spin then 'fa-spin' else ''

        @get_element()[method] $("<i class=\"fa fa-#{icon} #{spin}\" />")

    ###
    Set button's style
    @param {string}  style default|alt|primary|confirm|attention
    @param {boolean} flat weather this button is flat (no brders, etc...)
    ###
    set_style: (style='default', flat=false) ->
        current_style = "style-#{@get_style()}"
        @get_element().removeClass(current_style)

        if flat
            @get_element().addClass('style-flat')
        else
            @get_element().removeClass('style-flat')

        @get_element().addClass \
        switch style
            when ui.const.STYLE_DEFAULT   then 'style-default'
            when ui.const.STYLE_ALT       then 'style-alt'
            when ui.const.STYLE_PRIMARY   then 'style-primary'
            when ui.const.STYLE_CONFIRM   then 'style-confirm'
            when ui.const.STYLE_ATTENTION then 'style-attention'
            else throw new Error("Invalid style: `#{style}`")

    ###
    Get button's style
    @returns {string}
    ###
    get_style: ->
        classes = @get_element()[0].className.split ' '
        for class_name in classes
            if class_name.substr(0, 6) == 'style-' && class_name != 'style-flat'
                return class_name.substr(6)
