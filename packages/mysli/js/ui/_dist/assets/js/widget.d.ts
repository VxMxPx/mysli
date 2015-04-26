/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Widget {
        private static events_native;
        private events_count;
        private events_count_native;
        protected events: {
            click: {};
            destroyed: {};
        };
        private static uid_count;
        private static uid_list;
        protected static template: string;
        private $element;
        protected static allowed_styles: string[];
        protected prop: any;
        constructor(options?: any);
        /**
         * Generate a new UID and return it.
         */
        static next_uid(): string;
        /**
         * Return a main element.
         * @return {JQuery}
         */
        element: JQuery;
        /**
         * Return element's uid.
         * @return {string}
         */
        uid: string;
        /**
         * Destroy this widget. This will trigger the 'destroyed' event.
         */
        destroy(): void;
        /**
         * Get/get disabled status.
         * @param  {boolean} status
         * @return {boolean}
         */
        disabled(status?: boolean): boolean;
        /**
         * Get/set widget's style to be flat
         * @param  {boolean} value
         * @return {boolean}
         */
        flat(value?: boolean): boolean;
        /**
         * Get/set widget's style.
         * @param  {string} style
         * @return {string}
         */
        style(style?: string): string;
        /**
         * Connect callback with an event.
         * @param  {string}   event    event*id (id can be assigned,
         * to disconnect all events with that particular id,
         * by calling: disconnect('*id'))
         * @param  {Function} callback
         * @return {string}
         */
        connect(event: string, callback: (...args) => any): string;
        /**
         * Trigger an event.
         * @param  {string} event
         * @param  {array}  params
         * @return {array}
         */
        trigger(event: string, params?: any[]): any[];
        /**
         * Disconnect particular event.
         * @param  {any} id
         *   string: full id, or specified id (eg *my_id)
         *   array:  [event, id] to disconnect specific event
         * @return {boolean}
         */
        disconnect(id: string | string[]): boolean;
        /**
         * Disconnect native event.
         * @param {string} event
         */
        event_disconnect_native(event: string): void;
        /**
         * Process event*special_id and return an array.
         * @param  {string} event
         * @return {array}        [event, id]
         */
        static event_extract_name(event: string): string[];
    }
}
