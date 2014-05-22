;(function (MU) {

    'use strict';

    var Aarray = function () {
        this.stack = {};
        this.idsStack = [];
    };

    Aarray.prototype = {

        construct : Aarray,

        push : function (id, panel) {
            this.stack[id] = panel;
            this.idsStack.push(id);

            return this.idsStack.length - 1;
        },

        pushAfter : function (afterId, id, panel) {
            var indexTo = this.getIndex(afterId) + 1;

            this.stack[id] = panel;
            this.idsStack.splice(indexTo, 0, id);

            return indexTo;
        },

        remove : function (id) {
            delete this.stack[id];
            this.idsStack.splice(this.getIndex(id), 1);
        },

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

        getIndexFrom : function (id, step) {
            id = this.getIndex(id);
            if (id !== false && id > 0) {
                return id + step;
            }
        },

        get : function (id) {
            if (typeof this.stack[id] !== undefined) {
                return this.stack[id];
            } else {
                return false;
            }
        },

        getFrom : function (id, step) {
            id = this.getIndexFrom(id, step);
            if (id !== false) {
                return this.get(this.idsStack[id]);
            } else {
                return false;
            }
        },

        count : function () {
            return this.idsStack.length;
        },

        getLast : function () {
            return this.stack[this.idsStack[this.idsStack.length-1]];
        },

        each : function (callback) {
            for (var i = 0; i < this.idsStack.length; i++) {
                callback(i, this.stack[this.idsStack[i]]);
            }
        },

        eachAfter : function (id, callback) {
            for (var i = this.getIndex(id) + 1; i < this.idsStack.length; i++) {
                callback(i, this.stack[this.idsStack[i]]);
            }
        },

        eachBefore : function (id, callback) {
            for (var i = 0; i < this.getIndex(id); i++) {
                callback(i, this.stack[this.idsStack[i]]);
            }
        }
    };

    MU.Aarray = Aarray;

}(MU));
