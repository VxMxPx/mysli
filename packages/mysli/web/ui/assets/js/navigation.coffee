class mysli.web.ui.navigation extends mysli.web.ui.widget

    template = '<div class="ui-widget ui-navigation" />'
    ui = mysli.web.ui

    constructor: ->
        super
        @elements.push $(template)
