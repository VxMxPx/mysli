var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var common;
        (function (common) {
            var Arr = (function () {
                function Arr() {
                    this.stack = {};
                    this.ids = [];
                }
                /**
                 * Push element to the end of an array.
                 * @return inserted index
                 */
                Arr.prototype.push = function (id, element) {
                    this.stack[id] = element;
                    this.ids.push(id);
                    return this.ids.length - 1;
                };
                /**
                 * Replace particular element by id or index.
                 * @param id
                 */
                Arr.prototype.replace = function (id, element) {
                    if (this.has(id)) {
                        if (typeof id === 'number') {
                            id = this.ids[id];
                        }
                        this.stack[id] = element;
                    }
                    else {
                        throw new Error("Cannot replace element, no such ID: " + id);
                    }
                };
                /**
                 * Push element after particular element.
                 * @return inserted index
                 */
                Arr.prototype.push_after = function (after_id, id, element) {
                    var index_to = typeof after_id === 'string' ? this.get_index(after_id) + 1 : after_id + 1;
                    this.stack[id] = element;
                    this.ids.splice(index_to, 0, id);
                    return index_to;
                };
                /**
                 * Remove particular element by id or index.
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
                 */
                Arr.prototype.get_index = function (id) {
                    if (typeof this.ids.indexOf !== 'function') {
                        for (var i = this.ids.length - 1; i >= 0; i--) {
                            if (this.ids[i] === id)
                                return i;
                        }
                    }
                    else {
                        return this.ids.indexOf(id);
                    }
                };
                /**
                 * Get index n positions from id.
                 */
                Arr.prototype.get_index_from = function (id, step) {
                    var index = this.get_index(id);
                    if (index > 0)
                        return index + step;
                    else
                        return -1;
                };
                /**
                 * Check if element with such ID exists.
                 * @param id
                 */
                Arr.prototype.has = function (id) {
                    if (typeof id === 'number') {
                        return typeof this.ids[id] === 'string';
                    }
                    else {
                        return typeof this.stack[id] !== 'undefined';
                    }
                };
                /**
                 * Get element by id or index.
                 */
                Arr.prototype.get = function (id) {
                    if (typeof id === 'number')
                        id = this.ids[id];
                    if (typeof this.stack[id] !== 'undefined')
                        return this.stack[id];
                    else
                        return false;
                };
                /**
                 * Get element n positions from id.
                 */
                Arr.prototype.get_from = function (id, step) {
                    var index = typeof id === 'string' ? this.get_index_from(id, step) : id + step;
                    if (index > -1)
                        return this.get(this.ids[index]);
                    else
                        return false;
                };
                /**
                 * Number of elements.
                 */
                Arr.prototype.count = function () {
                    return this.ids.length;
                };
                /**
                 * Get last element
                 */
                Arr.prototype.get_last = function () {
                    return this.stack[this.ids[this.ids.length - 1]];
                };
                /**
                 * Execute function for each element.
                 * @param callback (index, element) will break when anything is returned.
                 */
                Arr.prototype.each = function (callback) {
                    var r;
                    for (var i = 0; i < this.ids.length; i++) {
                        r = callback(i, this.stack[this.ids[i]]);
                        if (typeof r !== 'undefined')
                            return r;
                    }
                };
                /**
                 * Execute function for each element, after particular id.
                 * @param id
                 * @param callback (index, element) will break when anything is returned.
                 */
                Arr.prototype.each_after = function (id, callback) {
                    var r;
                    for (var i = this.get_index(id) + 1; i < this.ids.length; i++) {
                        r = callback(i, this.stack[this.ids[i]]);
                        if (typeof r !== 'undefined')
                            return r;
                    }
                };
                /**
                 * Execute function for each element, before particular id.
                 * @param id
                 * @param callback (index, element) will break when anything is returned.
                 */
                Arr.prototype.each_before = function (id, callback) {
                    var r;
                    for (var i = 0; i < this.get_index(id); i++) {
                        r = callback(i, this.stack[this.ids[i]]);
                        if (typeof r !== 'undefined')
                            return r;
                    }
                };
                return Arr;
            })();
            common.Arr = Arr;
        })(common = js.common || (js.common = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
