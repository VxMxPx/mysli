/// <reference path="common.d.ts" />
declare module mysli.js.common {
    class Prop {
        private _context;
        constructor(properties: any, context: any);
        /**
         * Get/set any property
         */
        get(property: string): any;
        set(property: string, value: any): void;
        /**
         * Add default properties.
         * It will not append those values that are already set.
         * @param properties
         */
        def(properties: any): void;
        /**
         * Push and apply list of properties into the stack!
         * Options had to be predefined with a .def method.
         * If you provide `use` list, those will be set using setter.
         * If any of use values has ! e.g. ['icon!', 'label'], the
         * setter will be used, even if option the same as the
         * one already set (by default for example).
         * @param properties
         * @param use
         */
        push(properties: any, use?: string[]): void;
        /**
         * Query settings, by path, eg: {icon: {name: 'foo'}} ... path='icon.name' => 'foo'
         * @param path
         * @param def
         */
        q(path: string, def?: any): any;
    }
}
