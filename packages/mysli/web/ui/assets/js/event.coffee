class mysli.web.ui.event

    constructor: ->
        @events_counter = 0
        @events = {}

    ###
    Trigger an event
    @param {string} event
    @param {array}  params
    ###
    trigger: (event, params=[]) ->

        if typeof @events[event] == 'undefined'
            throw new Error("Invalid event id: `#{event}`")

        if typeof params.push != 'function'
            params = [params]

        params.push this

        for id of @events[event]
            if not @events[event].hasOwnProperty(id)
                continue

            call = @events[event][id]

            if typeof call == 'function'
                call.apply this, params
            else
                throw new Error("Invalid type of callback: `#{id}`")

    ###
    Connect callback with an event
    @param   {string}   event [event*id]
                        id can be assigned, to disconnect all events
                        with that id, by calling: disconnect('*id')
    @param   {function} callback
    @returns {string}   id
    ###
    connect: (event, callback) ->

        [event, id] = @extract_event_name event

        if typeof @events[event] == 'undefined'
            throw new Error("No such event available: `#{event}`")

        @events_counter++
        id = "#{id}#{event}--#{@events_counter}"

        @events[event][id] = callback
        return id

    ###
    Disconnect particular event
    @param   {string} id full id, or specified unique id (eg *my_id)
             {array}  [event, id] to disconnect specific event
    @returns {boolean}
    ###
    disconnect: (id) ->
        if typeof id != 'object' && id.substr(0, 1) == '*'
            id = id + "*"
            for event of @events
                for eid of @events[event]
                    if eid.substr(0, id.length) == id
                        delete @events[event][eid]
            return true
        else
            if typeof id != 'object'
                event = id.split('--', 2)[0]
            else
                event = id[0]
                id = id[1]

            if typeof @events[event] != 'undefined'
                return delete @events[event][id]
            else
                return false

    ###
    Process event*special_id and return an array
    @param   {string} event
    @returns {array}  [event, id]
    ###
    extract_event_name: (event) ->
        if event.indexOf("*") > 0
            id = event.split("*", 2)
            event = id[0]
            id = "*#{id[1]}*"
        else
            id = ''

        return [event, id]
