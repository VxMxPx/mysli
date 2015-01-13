class mysli.web.ui.label extends mysli.web.ui.widget

    ui = mysli.web.ui
    template = '<label class="ui-widget ui-label" />'

    constructor: (text, options={}) ->
        if typeof text == 'string'
            options.text = text
        else
            options = text

        @options = ui.util.merge_options options,
            text: null,
            for_id: null

        super
        @elements.push $(template)

        ui.util.apply_options @options, this,
            set_for_id: 'for_id',
            set_text: 'text'

    ###
    Set label for particular form element
    @param {string} id
    ###
    set_for_id: (id) ->
        @options.for_id = id
        @get_element().attr('for', id)
    ###
    Get ID of form element, for which label is set
    @returns {string}
    ###
    get_for_id: -> return @options.for_id

    ###
    Set label's text
    @param {string} text
    ###
    set_text: (text) ->
        @options.text = text
        @get_element().text text
    ###
    Get label's text
    @returns {string}
    ###
    get_text: -> return @options.text
