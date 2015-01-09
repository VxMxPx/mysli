class mysli.web.ui.titlebar extends mysli.web.ui.container

    template = '<div class="ui-widget ui-panel-container ui-titlebar" />'

    constructor: ->
        super
        @elements.push $(template)
        @container.master = @container.target = @elements[0]
