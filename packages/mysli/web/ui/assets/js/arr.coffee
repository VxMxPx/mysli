class mysli.web.ui.arr

    constructor: ->
        @stack = {}
        @ids   = []

    ###
    Push an element to the end of array
    @param {string} id
    @param {mixed} element
    @returns {integer} inserted index
    ###
    push: (id, element) ->
        @stack[id] = element
        @ids.push id
        return @ids.length - 1

    ###
    Pust an element after particular element
    @param {string} after_id
    @param {string} id
    @param {mixed}  element
    @returns {integer} inserted index
    ###
    push_after: (after_id, id, element) ->
        index_to = @get_index(after_id) + 1
        @stack[id] = element
        @ids.splice(index_to, 0, id)
        return index_to

    ###
    Remove particular element by id
    @param {string} id
    ###
    remove: (id) ->
        delete @stack[id]
        @ids.splice(@get_index(id), 1)

    ###
    Get index of particular element by id
    @param {string} id
    @returns {integer}
    ###
    get_index: (id) ->
        if typeof @ids.indexOf != 'function'
            for ide in @ids
                if ide == id
                    return ide
        else
            return @ids.indexOf(id)

    ###
    Get index n positions from id
    @param {string} id
    @param {integer} step
    @returns {integer}
    ###
    get_index_from: (id, step) ->
        id = @get_index(id)
        if id != false && id > 0
            return id + step

    ###
    Get element by id
    @param {string} id
    @returns {mixed}
    ###
    get: (id) ->
        if typeof @stack[id] != 'undefined'
            return @stack[id]
        else
            return false

    ###
    Get element n positions from id
    @param {string} id
    @param {integer} step
    @returns {mixed}
    ###
    get_from: (id, step) ->
        id = @get_index_from(id, step)
        if id != false
            return @get(@ids[id])
        else
            return false

    ###
    Number of elements
    @returns {integer}
    ###
    count: -> @ids.length

    ###
    Get last element
    @returns {mixed}
    ###
    get_last: ->
        @stack[@ids[@ids.length - 1]]

    ###
    Execute function for each element.
    function (index, element)
    break if function return any value (+ return that value)
    @param {function} callback
    @returns {mixed}
    ###
    each: (callback) ->
        for id in @ids
            r = callback(id, @stack[id])
            if r != undefined
                return r

    ###
    Execute function for each element, after particular id
    function (index, element)
    break if function return any value (+ return that value)
    @param {string} id
    @param {function} callback
    @returns {mixed}
    ###
    each_after: (id, callback) ->
        i = @get_index(id)
        while i < @ids.length
            r = callback(i, @stack[@ids[i]])
            if r != undefined
                return r
            else
                i++

    ###
    Execute function for each element, before particular id
    function (index, element)
    break if function return any value (+ return that value)
    @param {string} id
    @param {function} callback
    @returns {mixed}
    ###
    each_before: (id, callback) ->
        i = 0
        while i < @get_index(id)
            r = callback(i, @stack[@ids[i]])
            if r != undefined
                return r
            else
                i++
