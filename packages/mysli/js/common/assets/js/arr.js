mysli.js.common.arr = (function () {

    'use strict';

    // Array
    var arr = function () {
        this.stack = {};
        this.ids = [];
    };

    arr.prototype = {

        construct: arr,

        /**
         * Push element to the end of an array.
         * @param  {string}  id
         * @param  {mixed}   element
         * @return {integer} inserted index
         */
        push: function (id, element) {
            this.stack[id] = element;
            this.ids.push(id);
            return this.ids.length - 1;
        },

        /**
         * Push element after particular element.
         * @param  {string}  after_id
         * @param  {string}  id
         * @param  {mixed}   element
         * @return {integer} inserted index
         */
        push_after: function (after_id, id, element) {
            var index_to = this.get_index(after_id) + 1;
            this.stack[id] = element;
            this.ids.splice(index_to, 0, id);
            return index_to;
        },

        /**
         * Remove particular element by id.
         * @param  {string} id
         */
        remove: function (id) {
            delete this.stack[id];
            this.ids.splice(this.get_index(id), 1);
        },

        /**
         * Get index of particular element by id.
         * @param  {string} id
         * @return {integer}
         */
        get_index: function (id) {
            if (typeof this.ids.indexOf !== 'function') {
                for (var i = this.ids.length - 1; i >= 0; i--) {
                    if (this.ids[i] === id) {
                        return i;
                    }
                }
            } else {
                return this.ids.indexOf(id);
            }
        },

        /**
         * Get index n positions from id.
         * @param  {tring}   id
         * @param  {integer} step
         * @return {integer}
         */
        get_index_from: function (id, step) {
            id = this.get_index(id);
            if (id !== false && id > 0) {
                return id + step;
            }
        },

        /**
         * Get element by id.
         * @param  {string} id
         * @return {mixed}
         */
        get: function (id) {
            if (typeof this.stack[id] !== 'undefined') {
                return this.stack[id];
            } else {
                return false;
            }
        },

        /**
         * Get element n positions from id.
         * @param  {string}  id
         * @param  {integer} step
         * @return {mixed}
         */
        get_from: function (id, step) {
            id = this.get_index_from(id, step);
            if (id !== false) {
                return this.get(this.ids[id]);
            } else {
                return false;
            }
        },

        /**
         * Number of elements.
         * @return {integer}
         */
        count: function () {
            return this.ids.length;
        },

        /**
         * Get last element
         * @return {mixed}
         */
        get_last: function () {
            return this.stack[this.ids[this.ids.length-1]];
        },

        /**
         * Execute function for each element.
         * @param   {Function} callback (index, element) ->
         *                              Break when anything is returned.
         * @returns {mixed}
         */
        each: function (callback) {
            var r;
            for (var i = 0; i < this.ids.length; i++) {
                r = callback(i, this.stack[this.ids[i]]);
                if (typeof r !== 'undefined') {
                    return r;
                }
            }
        },

        /**
         * Execute function for each element, after particular id.
         * @param  {string}   id
         * @param   {Function} callback (index, element) ->
         *                              Break when anything is returned.
         * @return {mixed}
         */
        each_after: function (id, callback) {
            var r;
            for (var i = this.get_index(id) + 1; i < this.ids.length; i++) {
                r = callback(i, this.stack[this.ids[i]]);
                if (typeof r !== 'undefined') {
                    return r;
                }
            }
        },

        /**
         * Execute function for each element, before particular id.
         * @param  {string}   id
         * @param   {Function} callback (index, element) ->
         *                              Break when anything is returned.
         * @return {mixed}
         */
        each_before: function (id, callback) {
            var r;
            for (var i = 0; i < this.get_index(id); i++) {
                r = callback(i, this.stack[this.ids[i]]);
                if (typeof r !== 'undefined') {
                    return r;
                }
            }
        }
    };

    return arr;
}());
