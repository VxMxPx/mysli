module mysli.js.ui {
    export class Arr {
        stack = {};
        ids:string[] = [];

        /**
         * Push element to the end of an array.
         * @param  {string} id
         * @param  {mixed}  element
         * @return {number} inserted index
         */
        push(id:string, element:any):number {
            this.stack[id] = element;
            this.ids.push(id);
            return this.ids.length - 1;
        }

        /**
         * Push element after particular element.
         * @param  {string|number} after_id
         * @param  {string}        id
         * @param  {mixed}         element
         * @return {number}        inserted index
         */
        push_after(after_id:string|number, id:string, element:any):number {
            var index_to:number = typeof after_id === 'string' ? this.get_index(after_id) + 1 : after_id + 1;
            this.stack[id] = element;
            this.ids.splice(index_to, 0, id);
            return index_to;
        }

        /**
         * Remove particular element by id or index.
         * @param {string} id
         */
        remove(id:string|number):void {
            var index:number;

            if (typeof id === 'number') {
                index = id;
                id = this.ids[id];
            } else {
                index = this.get_index(id);
            }

            delete this.stack[id];
            this.ids.splice(index, 1);
        }

        /**
         * Get index of particular element by id.
         * @param  {string} id
         * @return {number}
         */
        get_index(id:string):number {
            if (typeof this.ids.indexOf !== 'function') {
                for (var i = this.ids.length - 1; i >= 0; i--) {
                    if (this.ids[i] === id) {
                        return i;
                    }
                }
            } else {
                return this.ids.indexOf(id);
            }
        }

        /**
         * Get index n positions from id.
         * @param  {string} id
         * @param  {number} step
         * @return {number}
         */
        get_index_from(id:string, step:number):number {
            var index:number = this.get_index(id);
            if (index > 0) {
                return index + step;
            } else {
                return -1;
            }
        }

        /**
         * Get element by id or index.
         * @param  {string|number} id
         * @return {mixed}
         */
        get(id:string|number):any {
            if (typeof id === 'number') {
                id = this.ids[id];
            }
            if (typeof this.stack[id] !== 'undefined') {
                return this.stack[id];
            } else {
                return false;
            }
        }

        /**
         * Get element n positions from id.
         * @param  {string|number} id
         * @param  {number}        step
         * @return {mixed}
         */
        get_from(id:string|number, step:number):any {
            var index:number = typeof id === 'string' ? this.get_index_from(id, step) : id + step;
            if (index > -1) {
                return this.get(this.ids[id]);
            } else {
                return false;
            }
        }

        /**
         * Number of elements.
         * @return {number}
         */
        count():number {
            return this.ids.length;
        }

        /**
         * Get last element
         * @return {mixed}
         */
        get_last():any {
            return this.stack[this.ids[this.ids.length-1]];
        }

        /**
         * Execute function for each element.
         * @param   {Function} callback (index, element) ->
         *   Break when anything is returned.
         * @returns {mixed}
         */
        each(callback:any):any {
            var r;
            for (var i = 0; i < this.ids.length; i++) {
                r = callback(i, this.stack[this.ids[i]]);
                if (typeof r !== 'undefined') {
                    return r;
                }
            }
        }

        /**
         * Execute function for each element, after particular id.
         * @param  {string}   id
         * @param   {Function} callback (index, element) ->
         *   Break when anything is returned.
         * @return {mixed}
         */
        each_after(id:string, callback:any):any {
            var r;
            for (var i = this.get_index(id) + 1; i < this.ids.length; i++) {
                r = callback(i, this.stack[this.ids[i]]);
                if (typeof r !== 'undefined') {
                    return r;
                }
            }
        }

        /**
         * Execute function for each element, before particular id.
         * @param  {string}   id
         * @param   {Function} callback (index, element) ->
         *                              Break when anything is returned.
         * @return {mixed}
         */
        each_before(id:string, callback:any):any {
            var r;
            for (var i = 0; i < this.get_index(id); i++) {
                r = callback(i, this.stack[this.ids[i]]);
                if (typeof r !== 'undefined') {
                    return r;
                }
            }
        }
    }
}
