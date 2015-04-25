var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Arr = (function () {
                function Arr() {
                    this.stack = {};
                    this.ids = [];
                }
                /**
                 * Push element to the end of an array.
                 * @param  {string} id
                 * @param  {mixed}  element
                 * @return {number} inserted index
                 */
                Arr.prototype.push = function (id, element) {
                    this.stack[id] = element;
                    this.ids.push(id);
                    return this.ids.length - 1;
                };
                /**
                 * Push element after particular element.
                 * @param  {string|number} after_id
                 * @param  {string}        id
                 * @param  {mixed}         element
                 * @return {number}        inserted index
                 */
                Arr.prototype.push_after = function (after_id, id, element) {
                    var index_to = typeof after_id === 'string' ? this.get_index(after_id) + 1 : after_id + 1;
                    this.stack[id] = element;
                    this.ids.splice(index_to, 0, id);
                    return index_to;
                };
                /**
                 * Remove particular element by id or index.
                 * @param {string} id
                 */
                Arr.prototype.remove = function (id) {
                    var index;
                    if (typeof id === 'number') {
                        index = id;
                        id = this.ids[id];
                    }
                    else {
                        index = this.get_index(id);
                    }
                    delete this.stack[id];
                    this.ids.splice(index, 1);
                };
                /**
                 * Get index of particular element by id.
                 * @param  {string} id
                 * @return {number}
                 */
                Arr.prototype.get_index = function (id) {
                    if (typeof this.ids.indexOf !== 'function') {
                        for (var i = this.ids.length - 1; i >= 0; i--) {
                            if (this.ids[i] === id) {
                                return i;
                            }
                        }
                    }
                    else {
                        return this.ids.indexOf(id);
                    }
                };
                /**
                 * Get index n positions from id.
                 * @param  {string} id
                 * @param  {number} step
                 * @return {number}
                 */
                Arr.prototype.get_index_from = function (id, step) {
                    var index = this.get_index(id);
                    if (index > 0) {
                        return index + step;
                    }
                    else {
                        return -1;
                    }
                };
                /**
                 * Get element by id or index.
                 * @param  {string|number} id
                 * @return {mixed}
                 */
                Arr.prototype.get = function (id) {
                    if (typeof id === 'number') {
                        id = this.ids[id];
                    }
                    if (typeof this.stack[id] !== 'undefined') {
                        return this.stack[id];
                    }
                    else {
                        return false;
                    }
                };
                /**
                 * Get element n positions from id.
                 * @param  {string|number} id
                 * @param  {number}        step
                 * @return {mixed}
                 */
                Arr.prototype.get_from = function (id, step) {
                    var index = typeof id === 'string' ? this.get_index_from(id, step) : id + step;
                    if (index > -1) {
                        return this.get(this.ids[id]);
                    }
                    else {
                        return false;
                    }
                };
                /**
                 * Number of elements.
                 * @return {number}
                 */
                Arr.prototype.count = function () {
                    return this.ids.length;
                };
                /**
                 * Get last element
                 * @return {mixed}
                 */
                Arr.prototype.get_last = function () {
                    return this.stack[this.ids[this.ids.length - 1]];
                };
                /**
                 * Execute function for each element.
                 * @param   {Function} callback (index, element) ->
                 *   Break when anything is returned.
                 * @returns {mixed}
                 */
                Arr.prototype.each = function (callback) {
                    var r;
                    for (var i = 0; i < this.ids.length; i++) {
                        r = callback(i, this.stack[this.ids[i]]);
                        if (typeof r !== 'undefined') {
                            return r;
                        }
                    }
                };
                /**
                 * Execute function for each element, after particular id.
                 * @param  {string}   id
                 * @param   {Function} callback (index, element) ->
                 *   Break when anything is returned.
                 * @return {mixed}
                 */
                Arr.prototype.each_after = function (id, callback) {
                    var r;
                    for (var i = this.get_index(id) + 1; i < this.ids.length; i++) {
                        r = callback(i, this.stack[this.ids[i]]);
                        if (typeof r !== 'undefined') {
                            return r;
                        }
                    }
                };
                /**
                 * Execute function for each element, before particular id.
                 * @param  {string}   id
                 * @param   {Function} callback (index, element) ->
                 *                              Break when anything is returned.
                 * @return {mixed}
                 */
                Arr.prototype.each_before = function (id, callback) {
                    var r;
                    for (var i = 0; i < this.get_index(id); i++) {
                        r = callback(i, this.stack[this.ids[i]]);
                        if (typeof r !== 'undefined') {
                            return r;
                        }
                    }
                };
                return Arr;
            })();
            ui.Arr = Arr;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
