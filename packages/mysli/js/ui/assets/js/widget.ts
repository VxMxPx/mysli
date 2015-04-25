/// <reference path="_util.ts" />
/// <reference path="_jquery.d.ts" />
module mysli.js.ui {
    export class Widget {

        // Events
        private static events_native = [
            // When widget is clicked
            // => ( object event, object this )
            'click',
            // When mouse cursor enter (parent) widget
            // => ( object event, object this )
            'mouse-enter',
            // When mouse cursor leave (parent) widget
            // => ( object event, object this )
            'mouse-leave',
            // When mouse cursor move over widget
            // => ( object event, object this )
            'mouse-move',
            // When mouse cursor move out of the widget (even to child)
            // => ( object event, object this )
            'mouse-out',
            // Mouse enter (even when enter to child element)
            // => ( object event, object this )
            'mouse-over',
            // Mouse up
            // => ( object event, object this )
            'mouse-up'
        ];
        private events_count: number = 0;
        private events_count_native: number[] = [];
        protected events = {
            // When widget is clicked
            // => ( object event, object this )
            click: {},
            // When widget is destroyed (destroy method called)
            // => ( object this )
            destroyed: {}
        };

        // Widget's Unique ID
        private static uid_count:number = 0;
        private static uid_list:string[] = [];

        // Element's template & element
        protected static template:string = '<div class="ui-widget" />';
        private $element: JQuery;

        // Properties
        protected static allowed_styles = ['default', 'alt', 'primary', 'confirm', 'attention'];
        protected prop;

        constructor(options={}) {
            // Extends properties
            this.prop = Util.mix({
                // Weather widget is disabled.
                disabled: false,

                // Widget's style
                style: 'default',

                // Weather widget (style) is flat
                flat: false
            }, options);

            // Check for uid
            if (typeof this.prop.uid === 'undefined') {
                this.prop.uid = 'mju-id-'+(++Widget.uid_count);
            } else {
                if (Widget.uid_list.indexOf(this.prop.uid) > -1) {
                    throw new Error('Moduel with such ID is already added: ' + this.prop.uid);
                } else {
                    Widget.uid_list.push(this.prop.uid);
                }
            }

            // Create element
            this.$element = $(this.constructor['template']);
            this.$element.prop('id', this.prop.uid);

            // Apply default properties
            Util.use(this.prop, this, {
                style: 'style',
                flat: 'flat',
                disabled: 'disabled'
            });
        }

        // Widget handling

        /**
         * Return a main element.
         * @return {JQuery}
         */
        element():JQuery {
            return this.$element;
        }

        /**
         * Return element's uid.
         * @return {string}
         */
        uid():string {
            return this.prop.uid;
        }

        /**
         * Destroy this widget. This will trigger the 'destroyed' event.
         */
        destroy() {
            this.trigger('destroyed');
            this.$element.remove();
        }

        /**
         * Get/get disabled status.
         * @param  {boolean} status
         * @return {boolean}
         */
        disabled(status?:boolean):boolean {
            if (typeof status !== 'undefined') {
                this.prop.disabled = status;
                this.$element.prop('disabled', status);
            }
            return this.prop.disabled;
        }

        /**
         * Get/set widget's style to be flat
         * @param  {boolean} value
         * @return {boolean}
         */
        flat(value?:boolean):boolean {
            if (typeof value !== 'undefined' && value !== this.prop.flat) {
                if (value) {
                    this.$element.addClass('style-flat');
                } else {
                    this.$element.removeClass('style-flat');
                }
                this.prop.flat = value;
            }

            return this.prop.flat;
        }

        /**
         * Get/set widget's style.
         * @param  {string} style
         * @return {string}
         */
        style(style?:string):string {
            var classes:string[];
            var current:string;

            if (typeof style !== 'undefined') {
                if (this.constructor['allowed_styles'].indexOf(style) > -1) {
                    this.prop.style = style;
                    this.$element.removeClass(this.prop.style);
                    this.$element.addClass("style-"+style);
                } else {
                    throw new Error("Invalid style: `"+style+"`, please use one of the following: "+this.constructor['allowed_styles'].join(', '));
                }
            }

            return this.prop.style;
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
        connect(event:string, callback):string {
            var _ref:string[] = Widget.event_extract_name(event);
            var id:string;

            event = _ref[0];
            id = _ref[1];

            if (typeof this.events[event] === 'undefined') {
                throw new Error('No such event available: '+event);
            }

            // Create new ID
            id = "" + id + event + "--" + (++this.events_count);
            this.events[event][id] = callback;

            // Handle native events
            if (typeof Widget.events_native[event] !== 'undefined') {
                this.events_count_native[event] =
                    typeof this.events_count_native[event] === 'undefined' ?
                        0 :
                        this.events_count_native[event]+1;
                // Prevent registering event more than once
                if (this.events_count_native[event] === 1) {
                    this.$element.on(event.replace('-', ''), this.trigger.bind(this, event));
                }
            }

            return id;
        }

        /**
         * Trigger an event.
         * @param  {string} event
         * @param  {array}  params
         * @return {array}
         */
        trigger(event:string, params=[]):any[] {
            var call;
            var _results:any[] = [];

            if (typeof this.events[event] === 'undefined') {
                throw new Error("Invalid event: "+event);
            }

            if (typeof params.push !== 'function') {
                throw new Error("Params needs to be an array!");
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
         * @param  {any} id
         *   string: full id, or specified id (eg *my_id)
         *   array:  [event, id] to disconnect specific event
         * @return {boolean}
         */
        disconnect(id:any):boolean {
            var event:any;
            var eid:string;

            if (typeof id !== 'object' && id.substr(0, 1) === '*') {
                id = id + "*";
                for (event in this.events) {
                    for (eid in this.events[event]) {
                        if (eid.substr(0, id.length) === id) {
                            this.event_disconnect_native(event);
                            delete this.events[event][eid];
                        }
                    }
                }
                return true;
            } else {
                if (typeof id !== 'object') {
                    event = id.split('--', 2)[0];
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
         * Disconnect native event.
         * @param {string} event
         */
        event_disconnect_native(event:string):void {
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
         * @param  {string} event
         * @return {array}        [event, id]
         */
        static event_extract_name(event:string):string[] {
            var id:string[];
            var idr:string = '';

            if (event.indexOf("*") > 0) {
                id = event.split("*", 2);
                event = id[0];
                idr = "*" + id[1] + "*";
            }

            return [event, idr];
        }
    }
}
