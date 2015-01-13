class mysli.web.ui.html extends mysli.web.ui.widget

    ui = mysli.web.ui
    template = '<div class="ui-html ui-widget"></div>'

    constructor: (content=null) ->
        super
        @elements.push $(template)
        if content
            @push(content)

    ###
    Push HTML content to the container
    @param {string} content
    ###
    push: (content) ->
        @get_element().append(content)

