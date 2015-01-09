mysli.web.ui.util=
    apply_options: (options, context, methods={}) ->
        for call, expect of methods
            params = []
            if typeof expect == 'string'
                params.push options[expect]
            else
                for arg in expect
                    params.push options[arg]
            context[call].apply context, params
        return

    merge_options: (options, defaults) ->
        return $.extend {}, defaults, options

    number_format: (number, decimals, dec_point='.', thousands_sep=',') ->
        # Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
        n = if isFinite(+number) then +number else 0
        prec = if isFinite(+decimals) then Math.abs(decimals) else 0
        s = ''
        to_fixed_fix = (n, prec) ->
            k = Math.pow(10, prec)
            return '' + Math.round(n * k) / k

        # Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = if prec then to_fixed_fix(n, prec) else '' + Math.round(n)
        s = s.split('.')

        if s[0].length > 3
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, thousands_sep)

        if (s[1] || '').length < prec
            s[1] = s[1] || ''
            s[1] += new Array(prec - s[1].length + 1).join('0')

        return s.join(dec_point)

    get_percent: (amount, total, percision) ->
        if typeof amount == 'number' && typeof total == 'number'
            percision = percision || 2
            if not amount || not total
                return amount
            count = amount / total
            count = count * 100
            count = parseFloat(@number_format(count, percision))
            return count
        return false

    set_percent: (percent, total, percision) ->
        if typeof percent == 'number' && typeof total == 'number'
            percision = percision || 2
            if not percent || not total
                return 0
            result = parseFloat(@number_format((total / 100) * percent, percision))
            return result
        return false
