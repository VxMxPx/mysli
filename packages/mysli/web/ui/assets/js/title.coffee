class mysli.web.ui.title extends mysli.web.ui.widget

    ui = mysli.web.ui
    template = '<span class="ui-widget ui-title" />'

    constructor: (text, options={}) ->
        if typeof text == 'string'
            options.text = text
        else
            options = text

        @options = ui.util.merge_options options,
            text: null,
            level: 1

        super(@options)
        @elements.push $(template)

        ui.util.apply_options @options, this,
            set_level: 'level',
            set_text: 'text'

    ###
    Set title level 1...6
    @param {integer} level
    ###
    set_level: (level) ->
        @options.level = level
        @get_element().html $("<h#{level}/>")
        @set_text @options.text
    ###
    Get current level
    @returns {integer}
    ###
    get_level: -> return @options.for_id

    ###
    Set label's text
    @param {string} text
    ###
    set_text: (text) ->
        @options.text = text
        @get_element().find(':first-child').text text
    ###
    Get label's text
    @returns {string}
    ###
    get_text: -> return @options.text
