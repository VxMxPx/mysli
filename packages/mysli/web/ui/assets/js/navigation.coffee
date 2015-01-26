class mysli.web.ui.navigation extends mysli.web.ui.widget

    template = '<div class="ui-widget ui-navigation" />'
    ui = mysli.web.ui

    constructor: (items={}) ->
        @items = {}
        super
        @elements.push $(template)
        @push_multiple items

        # Events
        # When any navigation item is cliked
        # => ( string item_id, object this )
        @events['action'] = {}

        @get_element().on 'click', 'a.action', (e) =>
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
    @param {mixed}  string: label | object: element
    ###
    push: (id, element) ->
        if typeof element == 'string'
            element = {label: element}
        item = $('<div class="ui-navigation-item" />')
        item.attr('id', "mnip--#{id}")
        item
            .append("<a href=\"#\" class=\"action\" id=\"mnic--#{id}\"><span></span></a>")
            .find('a span')
            .text(element.label)
        if typeof element.options == 'object'
            push_sub id, item, element.options
        @items[id] = item
        @get_element().append item

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


    # --- Private ---

    ###
    Push sub navigation to the parent.
    @param {string} id of the parent
    @param {object} parent
    @param {object} items
    ###
    push_sub= (id, parent, items) ->
        parent.append $('<a href="#" class="ui-navigation-toggle collapsed">&nbsp;</a>')
        options = $('<div class="ui-navigation-options collapsed" />')
        for sid, label of items
            sid = id + '-' + sid
            options.append("<a href=\"#\" class=\"action\" id=\"mnis--#{sid}\"/><span>#{label}</span></a>")
        parent.append options
