;(function (MU) {

    'use strict';

    // associative array
    var Aarray = function () {
        this.stack = {};
        this.idsStack = [];
    };

    Aarray.prototype = {

        construct : Aarray,

        // push element to the end of array
        // id      : string
        // element : mixed
        // return  : integer  inserted index
        push : function (id, element) {
            this.stack[id] = element;
            this.idsStack.push(id);

            return this.idsStack.length - 1;
        },

        // pust element after particular element
        // afterId : string
        // id      : string
        // element : mixed
        // return  : integer  inserted index
        pushAfter : function (afterId, id, element) {
            var indexTo = this.getIndex(afterId) + 1;

            this.stack[id] = element;
            this.idsStack.splice(indexTo, 0, id);

            return indexTo;
        },

        // remove particular element by id
        // id : string
        remove : function (id) {
            delete this.stack[id];
            this.idsStack.splice(this.getIndex(id), 1);
        },

        // get index of particular element by id
        // id : string
        getIndex : function (id) {
            if (typeof this.idsStack.indexOf !== 'function') {
                for (var i = this.idsStack.length - 1; i >= 0; i--) {
                    if (this.idsStack[i] === id) {
                        return i;
                    }
                }
            } else {
                return this.idsStack.indexOf(id);
            }
        },

        // get index n positions from id
        // id   : string
        // step : integer
        getIndexFrom : function (id, step) {
            id = this.getIndex(id);
            if (id !== false && id > 0) {
                return id + step;
            }
        },

        // get element by id
        // id : string
        get : function (id) {
            if (typeof this.stack[id] !== undefined) {
                return this.stack[id];
            } else {
                return false;
            }
        },

        // get element n positions from id
        // id   : string
        // step : integer
        getFrom : function (id, step) {
            id = this.getIndexFrom(id, step);
            if (id !== false) {
                return this.get(this.idsStack[id]);
            } else {
                return false;
            }
        },

        // amount of elements
        // return : integer
        count : function () {
            return this.idsStack.length;
        },

        // get last element
        getLast : function () {
            return this.stack[this.idsStack[this.idsStack.length-1]];
        },

        // execute function for each element
        // function (index, element)
        // break if function return any value (+ return that value)
        // callback : function
        each : function (callback) {
            var r;
            for (var i = 0; i < this.idsStack.length; i++) {
                r = callback(i, this.stack[this.idsStack[i]]);
                if (r !== undefined) { return r; }
            }
        },

        // execute function for each element, after particular id
        // function (index, element)
        // break if function return any value (+ return that value)
        // id       : string
        // callback : function
        eachAfter : function (id, callback) {
            var r;
            for (var i = this.getIndex(id) + 1; i < this.idsStack.length; i++) {
                r = callback(i, this.stack[this.idsStack[i]]);
                if (r !== undefined) { return r; }
            }
        },

        // execute function for each element, before particular id
        // function (index, element)
        // break if function return any value (+ return that value)
        // id       : string
        // callback : function
        eachBefore : function (id, callback) {
            var r;
            for (var i = 0; i < this.getIndex(id); i++) {
                r = callback(i, this.stack[this.idsStack[i]]);
                if (r !== undefined) { return r; }
            }
        }
    };

    // export
    MU.Aarray = Aarray;

}(MU));
