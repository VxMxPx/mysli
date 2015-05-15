module mysli.js.common
{
    export class Arr
    {
        stack = {};
        ids:string[] = [];

        /**
         * Push element to the end of an array.
         * @return inserted index
         */
        push(id: string, element: any): number
        {
            this.stack[id] = element;
            this.ids.push(id);
            return this.ids.length - 1;
        }

        /**
         * Push element after particular element.
         * @return inserted index
         */
        push_after(after_id: string|number, id: string, element: any): number
        {
            var index_to:number = typeof after_id === 'string' ? this.get_index(after_id) + 1 : after_id + 1;
            this.stack[id] = element;
            this.ids.splice(index_to, 0, id);
            return index_to;
        }

        /**
         * Remove particular element by id or index.
         */
        remove(id: string|number): void
        {
            var index: number;

            if (typeof id === 'number')
            {
                index = <number> id;
                id = this.ids[id];
            }
            else
            {
                index = this.get_index(<string> id);
            }

            delete this.stack[id];
            this.ids.splice(index, 1);
        }

        /**
         * Get index of particular element by id.
         */
        get_index(id: string): number
        {
            if (typeof this.ids.indexOf !== 'function')
            {
                for (var i = this.ids.length - 1; i >= 0; i--)
                {
                    if (this.ids[i] === id)
                        return i;
                }
            }
            else
            {
                return this.ids.indexOf(id);
            }
        }

        /**
         * Get index n positions from id.
         */
        get_index_from(id: string, step: number): number
        {
            var index: number = this.get_index(id);
        
            if (index > 0)
                return index + step;
            else
                return -1;
        }

        /**
         * Check if element with such ID exists.
         * @param id
         */
        has(id: string|number): boolean
        {
            if (typeof id === 'number')
                return typeof this.ids[id] === 'string';
            else
                return typeof this.stack[id] !== 'undefined';
        }

        /**
         * Get element by id or index.
         */
        get(id: string|number): any
        {
            if (typeof id === 'number')
                id = this.ids[id];

            if (typeof this.stack[id] !== 'undefined')
                return this.stack[id];
            else
                return false;
        }

        /**
         * Get element n positions from id.
         */
        get_from(id: string|number, step: number): any
        {
            var index: number = typeof id === 'string' ? this.get_index_from(id, step) : id + step;
        
            if (index > -1)
                return this.get(this.ids[index]);
            else
                return false;
        }

        /**
         * Number of elements.
         */
        count(): number
        {
            return this.ids.length;
        }

        /**
         * Get last element
         */
        get_last(): any
        {
            return this.stack[this.ids[this.ids.length-1]];
        }

        /**
         * Execute function for each element.
         * @param callback (index, element) will break when anything is returned.
         */
        each(callback: (index?: number, element?: any) => any): any
        {
            var r;

            for (var i = 0; i < this.ids.length; i++)
            {
                r = callback(i, this.stack[this.ids[i]]);

                if (typeof r !== 'undefined')
                    return r;
            }
        }

        /**
         * Execute function for each element, after particular id.
         * @param id
         * @param callback (index, element) will break when anything is returned.
         */
        each_after(id: string, callback: (index?: number, element?: any) => any): any
        {
            var r;

            for (var i = this.get_index(id) + 1; i < this.ids.length; i++)
            {
                r = callback(i, this.stack[this.ids[i]]);

                if (typeof r !== 'undefined')
                    return r;
            }
        }

        /**
         * Execute function for each element, before particular id.
         * @param id
         * @param callback (index, element) will break when anything is returned.
         */
        each_before(id: string, callback: (index?: number, element?: any) => any): any
        {
            var r;

            for (var i = 0; i < this.get_index(id); i++)
            {
                r = callback(i, this.stack[this.ids[i]]);

                if (typeof r !== 'undefined')
                    return r;
            }
        }
    }
}
