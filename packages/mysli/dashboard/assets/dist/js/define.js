;(function ($) {

    // moduler required so far
    var modulesRegistry = {
        'mysli/zepto' : $
    };

    function getModule(module) {
        if (module in modulesRegistry) {
            return modulesRegistry[module];
        }
        var xhr = $.ajax({
            type : 'GET',
            url  : '/dashboard/mysli/dashboard/script',
            data : {
                require : module
            },
            dataType : 'script',
            async    : false,

        });
        // console.log(xhr.response);
        // xhr.done(function (data) {
        //     console.log(data);
        // });
        return;
    }

    function registerModule(moduleName, execute) {
        modulesRegistry[moduleName] = execute.apply(execute, this);
    }

    function getModules() {
        var fetchedModules = [];
        for (var i=0; i < arguments.length; i++) {
            fetchedModules.push(getModule(arguments[i]));
        }
        return registerModule.bind(fetchedModules);
    }

    // export require function
    if (typeof window.Mysli === 'undefined') {
        window.Mysli = {};
    }
    window.Mysli.Dashboard = getModules;
}(Zepto));
