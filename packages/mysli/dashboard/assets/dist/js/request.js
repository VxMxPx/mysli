Mysli.Dashboard
('mysli/zepto', 'mysli/ui')
('mysli/dashboard/request', function ($) {
    console.log('REQUEST', 'zepto:', $);
    return {
        get  : function (data, uri) {},
        post : function (data, uri) {},
        call : function (method, data, uri) {}
    };
});
