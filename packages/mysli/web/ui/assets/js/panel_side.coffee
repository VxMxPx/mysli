class mysli.web.ui.panel_side extends mysli.web.ui.container

    template = '<div class="ui-widget ui-panel-container ui-panel-side" />'

    constructor: (id='front') ->
        super
        @elements.push $(template)
        @container.master = @container.target = @elements[0]
        @elements[0].addClass("ui-panel-side-type-#{id}")
