class mysli.web.ui.navigation extends mysli.web.ui.container

    template = '<div class="ui-widget ui-navigation" />'
    ui = mysli.web.ui

    ###
    @param {object} items
    ###
    constructor: (items={}) ->
        super
        @elements.push $(template)
        @container.master = @container.target = @elements[0]

        @push_multiple items

        # Events
        # When any navigation item is cliked
        # => ( string item_id, object this )
        @events['action'] = {}

        @get_element().on 'click', '.ui-navigation-item > a.action', (e) =>
            e.stopPropagation()
            id = e.currentTarget.id.substr(6)
            @trigger 'action', id


    ###
    Push multiple items to the collection.
    @param {object} items
    ###
    push_multiple: (items) ->
        for id, item of items
            @push id, item

    ###
    Push one item to the collection.
    @param {string} id
    @param {mixed}  string: label | object: element (additional options)
    ###
    push: (id, element) ->
        if typeof element == 'string'
            element = {label: element}

        item = new ui.widget();
        item.elements.push($('<div class="ui-navigation-item" />'))
        item.set_id("mnip--#{id}")
        item.get_element()
            .append("<a href=\"#\" class=\"action\" id=\"mnic--#{id}\"><span></span></a>")
            .find('a span')
            .text(element.label)
        super(item, id)

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
