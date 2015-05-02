/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class Widget {

        // Events
        private static events_native = [
            // When widget is clicked
            // => ( event: any, widget: Widget )
            'click',
            // When mouse cursor enter (parent) widget
            // => ( event: event, widget: Widget )
            'mouse-enter',
            // When mouse cursor leave (parent) widget
            // => ( event: any, widget: Widget )
            'mouse-leave',
            // When mouse cursor move over widget
            // => ( event: any, widget: Widget )
            'mouse-move',
            // When mouse cursor move out of the widget (even to child)
            // => ( event: any, widget: Widget )
            'mouse-out',
            // Mouse enter (even when enter to child element)
            // => ( event: any, widget: Widget )
            'mouse-over',
            // Mouse up
            // => ( event: any, widget: Widget )
            'mouse-up'
        ];
        private events_count: number = 0;
        private events_count_native: any = {};
        protected events = {
            // When widget is clicked
            // => ( event: any, widget: Widget )
            click: {},
            // When widget is destroyed (destroy method called)
            // => ( widget: Widget )
            destroyed: {}
        };

        // Widget's Unique ID
        private static uid_count: number = 0;

        // Element's template & element
        protected static template: string = '<div class="ui-widget" />';
        protected $element: JQuery;

        // Properties
        protected static allowed_styles: string[] = ['default', 'alt', 'primary', 'confirm', 'attention'];
        protected prop: any;

        constructor(options: any = {}) {

            this.prop = new common.Prop({
                // Weather widget is disabled.
                disabled: false,
                // Widget's style
                style: 'default',
                // Weather widget (style) is flat
                flat: false,
                // Unique identifier of an object
                uid: null
            }, this);

            // Check for uid
            if (typeof options.uid === 'undefined') {
                options.uid = Widget.next_uid();
            } else if (typeof options.uid !== 'string') {
                throw new Error(`UID needs to be a valid string, got: ${uid}`);
            }

            // Create element
            this.$element = $(this['constructor']['template']);
//            this.$element.prop('id', options.uid);

            // Push options finally!
            this.prop.push(options, ['style!', 'flat!', 'disabled']);
        }

        /**
         * Generate a new UID and return it.
         */
        static next_uid(): string {
            return 'mju-id-'+(++Widget.uid_count);
        }

        // Widget handling

        /**
         * Return a main element.
         * @return {JQuery}
         */
        get element(): JQuery {
            return this.$element;
        }

        /**
         * Return element's uid.
         * @return {string}
         */
        get uid(): string {
            return this.prop.uid;
        }

        // Get/set disabled status
        get disabled(): boolean {
            return this.prop.disabled;
        }
        set disabled(status: boolean) {
            this.prop.disabled = status;
            this.element.prop('disabled', status);
        }

        // Get/set widget style to flat.
        get flat(): boolean {
            return this.prop.flat;
        }
        set flat(value: boolean) {
            this.element[value ? 'addClass' : 'removeClass']('style-flat');
        }

        // Get/set widget's style (in general)
        get style(): string {
            return this.prop.style;
        }
        set style(style: string) {
            if (this['constructor']['allowed_styles'].indexOf(style) > -1) {
                this.element.removeClass(`style-${this.prop.style}`);
                this.prop.style = style;
                this.element.addClass(`style-${style}`);
            } else {
                throw new Error(`Invalid style: ${style}, please use one of the following: ${this['constructor']['allowed_styles'].join(', ')}`);
            }
        }

        // Other

        /**
         * Destroy this widget. This will trigger the 'destroyed' event.
         */
        destroy() {
            this.trigger('destroyed');
            this.$element.remove();
            this.prop.uid = -1;
        }

        // Events

        /**
         * Connect callback with an event.
         * @param  {string}   event    event*id (id can be assigned,
         * to disconnect all events with that particular id,
         * by calling: disconnect('*id'))
         * @param  {Function} callback
         * @return {string}
         */
        connect(event: string, callback: (...args) => any): string {
            var _ref: string[] = Widget.event_extract_name(event);
            var id: string;

            event = _ref[0];
            id = _ref[1];

            if (typeof this.events[event] === 'undefined') {
                throw new Error('No such event available: '+event);
            }

            // Create new ID
            id = "" + id + event + "--" + (++this.events_count);
            this.events[event][id] = callback;

            // Handle native events
            if (Widget.events_native.indexOf(event) > -1) {
                this.events_count_native[event] =
                    typeof this.events_count_native[event] === 'undefined' ?
                        1 :
                        this.events_count_native[event]+1;
                // Prevent registering event more than once
                if (this.events_count_native[event] === 1) {
                    this.element.on(event.replace('-', ''), (e) => {
                        this.trigger(event, [e]);
                    });
                }
            }

            return id;
        }

        /**
         * Trigger an event.
         */
        trigger(event: string, params: any[] = []): any[] {
            var call;
            var _results: any[] = [];

            if (typeof this.events[event] === 'undefined') {
                throw new Error("Invalid event: "+event);
            }

            if (typeof params.push !== 'function') {
                throw new Error('Params must be an array!');
            }

            params.push(this);

            for (var id in this.events[event]) {
                if (!this.events[event].hasOwnProperty(id)) {
                    continue;
                }

                call = this.events[event][id];

                if (typeof call === 'function') {
                    _results.push(call.apply(this, params));
                } else {
                    throw new Error("Invalid type of callback: "+id);
                }
            }

            return _results;
        }

        /**
         * Disconnect particular event.
         * @param id full id or specified id (eg *my_id) OR [event, id]
         * @returns {boolean}
         */
        disconnect(id: string|[string, string]): boolean {
            var event: any;
            var eid: string;

            if (typeof id === 'string' && id.substr(0, 1) === '*') {
                id = id + "*";
                for (event in this.events) {
                    if (!this.events.hasOwnProperty(event)) {
                        continue;
                    }
                    for (eid in this.events[event]) {
                        if (!this.events[event].hasOwnProperty(eid)) {
                            continue;
                        }
                        if (eid.substr(0, id.length) === id) {
                            this.event_disconnect_native(event);
                            delete this.events[event][eid];
                        }
                    }
                }
                return true;
            } else {
                if (typeof id === 'string') {
                    event = (<string> id).split('--', 2)[0];
                } else {
                    event = id[0];
                    id = id[1];
                }

                if (typeof this.events[event] !== 'undefined') {
                    this.event_disconnect_native(event);
                    return delete this.events[event][id];
                } else {
                    return false;
                }
            }
        }

        /**
         * Disconnect a native event.
         * @param event
         */
        event_disconnect_native(event: string): void {
            if (typeof Widget.events_native[event] !== 'undefined') {
                this.events_count_native[event] =
                    typeof this.events_count_native[event] === 'undefined' ?
                        0 :
                        this.events_count_native[event]-1;

                if (this.events_count_native[event] === 0) {
                    this.$element.off(event.replace('-', ''));
                }
            }
        }

        /**
         * Process event*special_id and return an array.
         * @param event
         */
        static event_extract_name(event: string): [string, string] {
            var id: string[];
            var idr: string = '';

            if (event.indexOf("*") > 0) {
                id = event.split("*", 2);
                event = id[0];
                idr = "*" + id[1] + "*";
            }

            return [event, idr];
        }
    }
}
