declare module mysli.js.common {
    class Arr {
        stack: {};
        ids: string[];
        /**
         * Push element to the end of an array.
         * @return inserted index
         */
        push(id: string, element: any): number;
        /**
         * Push element after particular element.
         * @return inserted index
         */
        push_after(after_id: string | number, id: string, element: any): number;
        /**
         * Remove particular element by id or index.
         */
        remove(id: string | number): void;
        /**
         * Get index of particular element by id.
         */
        get_index(id: string): number;
        /**
         * Get index n positions from id.
         */
        get_index_from(id: string, step: number): number;
        /**
         * Check if element with such ID exists.
         * @param id
         */
        has(id: string | number): boolean;
        /**
         * Get element by id or index.
         */
        get(id: string | number): any;
        /**
         * Get element n positions from id.
         */
        get_from(id: string | number, step: number): any;
        /**
         * Number of elements.
         */
        count(): number;
        /**
         * Get last element
         */
        get_last(): any;
        /**
         * Execute function for each element.
         * @param callback (index, element) will break when anything is returned.
         */
        each(callback: (index?: number, element?: any) => any): any;
        /**
         * Execute function for each element, after particular id.
         * @param id
         * @param callback (index, element) will break when anything is returned.
         */
        each_after(id: string, callback: (index?: number, element?: any) => any): any;
        /**
         * Execute function for each element, before particular id.
         * @param id
         * @param callback (index, element) will break when anything is returned.
         */
        each_before(id: string, callback: (index?: number, element?: any) => any): any;
    }
}
