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
        protected static template: string;
        protected $element: JQuery;
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
        disabled: boolean;
        flat: boolean;
        style: string;
        /**
         * Destroy this widget. This will trigger the 'destroyed' event.
         */
        destroy(): void;
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
         */
        trigger(event: string, params?: any[]): any[];
        /**
         * Disconnect particular event.
         * @param id full id or specified id (eg *my_id) OR [event, id]
         * @returns {boolean}
         */
        disconnect(id: string | [string, string]): boolean;
        /**
         * Disconnect a native event.
         * @param event
         */
        event_disconnect_native(event: string): void;
        /**
         * Process event*special_id and return an array.
         * @param event
         */
        static event_extract_name(event: string): [string, string];
    }
}
