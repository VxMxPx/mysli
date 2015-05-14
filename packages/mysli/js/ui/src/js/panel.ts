/// <reference path="widget.ts" />
/// <reference path="panel_side.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Panel extends Widget
    {
        // Predefined panel sizes
        public static get SIZE_TINY(): number { return 160; }
        public static get SIZE_SMALL(): number { return 260; }
        public static get SIZE_NORMAL(): number { return 340; }
        public static get SIZE_BIG(): number { return 500; }
        public static get SIZE_HUGE(): number { return 800; }

        private static valid_sides: string[] = ['front', 'back'];
        public static get SIDE_FRONT(): string { return 'front'; }
        public static get SIDE_BACK(): string { return 'back'; }

        // List of connected panels
        // private connected: common.Arr = new common.Arr();

        // when true, some events will be prevented on the panel, like further animations
        private closing: boolean = false;

        // when panel goes to full screen highest zIndex is set, this is the
        // original zIndex, to be restored, when full screen is turned off
        private old_zindex: number = 0;
        private old_width: number = 0;

        // Front and back side
        public front: PanelSide;
        public back: PanelSide;

        constructor(options: any = {})
        {
            super(options);

            this.element.addClass('ui-panel');
            this.element.append('<div class="ui-panel-sides" />');

            // Add supported events
            this.events = common.mix({
                // When panel `close` method is called, just before panel's
                // `destroy` method is invoked.
                // => ( panel: Panel )
                'close': {},
                // On away status change
                // => ( status: boolean, width: number, panel: Panel )
                'set-away': {},
                // On size changed
                // => ( width: number, diff: number, panel: Panel )
                'set-width': {},
                // On popout status changed
                // => ( value: boolean, panel: Panel )
                'set-popout': {},
                // On insensitive status changed
                // => ( value: boolean, panel: Panel )
                'set-insensitive': {},
                // On min_size value changed
                // => ( min_size: number, panel: Panel )
                'set-min-size': {},
                // On focus changed
                // => ( value: boolean, panel: Panel )
                'set-focus': {},
                // On expandable status changed
                // => ( value: boolean, panel: Panel )
                'set-expandable': {}
            }, this.events);

            this.prop.def({
                // position in px from left
                position: 0,
                // when there's a lot of panels, they start being pushed aside
                // and partly hidden
                offset: 0,
                // weather panel is locked
                locked: false,
                // weather panel can be expanded to fill the available space
                expandable: false,
                // how much panel's width was increased (only if expandable is true)
                expanded_for: 0,
                // for how much can panel shrink (if 0 it can't shrink)
                min_size: 0,
                // panel's size by px
                width: Panel.SIZE_NORMAL,
                // is panel in away mode
                away: false,
                // if away on blur, then panel will go away when lose focus
                away_on_blur: false,
                // the width (px) of panel when away
                away_width: 10,
                // if insensitive, then panel cannot be focused
                insensitive: false,
                // if panel is popout
                popout: false,
                // Size of the panel when popout
                popout_size: Panel.SIZE_HUGE,
                // Weather panel is in focus
                focus: false,
                // Weather panel can be flipped (back side exists!)
                flippable: false,
                // Which side is visible
                side: Panel.SIDE_FRONT
            });
            this.prop.push(options);

            this.element.width(this.prop.width);

            // Proxy the click event to focus
            this.element.on('click', () => {
                if (!this.prop.closing && !this.locked)
                    this.focus = true;
            });

            // Add Sides
            this.front = new PanelSide();
            this.element.find('.ui-panel-sides').append(this.front.element);

            if (this.prop.flippable)
            {
                // Add multi-panel class
                this.element.addClass('multi');
                // Add actual panel
                this.back = new ui.PanelSide({style: 'alt'});
                this.element.find('.ui-panel-sides').append(this.back.element);
                // Set desired side
                this.side = this.prop.side;
            }
        }


        /**
         * Animate all the changes made to the element.
         */
        animate(callback?: () => any): void
        {
            if (this.prop.closing)
                return;

            this.element.stop(true, false).animate({
                left: this.position + this.offset,
                width: this.width + this.expand,
                opacity: 1
            }, {
                duration: 400,
                queue: false,
                always: function () {
                    if (callback) {
                        callback.call(this);
                    }
                }
            }).css({overflow: 'visible'});
        }

        /**
         * Get/set panel's visible side
         */
        get side(): string
        {
            return this.prop.side;
        }
        set side(value: string)
        {
            if (Panel.valid_sides.indexOf(value) === -1)
                throw new Error(`Trying to set invalid side: ${value}`);

            if (!this.prop.flippable)
                throw new Error(`Trying to flip a panel which is not flippable.`);

            // Right now this is hard coded, there are only two sides.
            // It's possible that in future more sides will be added?
            this.element[value === Panel.SIDE_BACK ? 'addClass' : 'removeClass']('flipped');
        }

        /**
         * Get/set panel's width
         */
        get width(): number
        {
            return this.prop.width;
        }
        set width(value: number)
        {
            var diff: number;

            if (value === this.width)
                return;

            diff = -(this.width - value);
            this.prop.width = value;

            this.trigger('set-width', [value, diff]);
        }

        /**
         * Get/set away status for panel.
         */
        get away(): boolean
        {
            return this.prop.away;
        }
        set away(status: boolean)
        {
            var width: number;

            if (status === this.away)
                return;

            if (status)
            {
                if (this.focus || this.away)
                {
                    this.prop.away_on_blur = true;
                    return;
                }
                this.prop.away = true;
                width = -(this.width - this.away_width);
            }
            else
            {
                if (!this.away)
                {
                    this.prop.away_on_blur = false;
                    return;
                }
                this.prop.away = false;
                this.prop.away_on_blur = false;
                width = this.width - this.away_width;
            }

            this.trigger('set-away', [status, width]);
        }

        /**
         * Get/set panel's popout status.
         */
        get popout(): boolean
        {
            return this.prop.popout;
        }
        set popout(status: boolean)
        {
            if (status === this.popout)
                return;

            if (status)
            {
                this.prop.popout = true;
                this.focus = true;
                this.old_zindex = +this.element.css('z-index');
                this.old_width = this.width;
                this.element.css('z-index', 10005);
                this.width = this.prop.popout_size;
            }
            else
            {
                this.prop.popout = false;
                this.element.css('z-index', this.old_zindex);
                this.width = this.old_width;
            }

            this.trigger('set-popout', [status]);
        }

        /**
         * Get/get insensitive status.
         */
        get insensitive(): boolean
        {
            return this.prop.insensitive;
        }
        set insensitive(value: boolean)
        {
            if (value === this.insensitive)
                return;

            if (value)
            {
                if (this.focus)
                    this.focus = false;

                this.prop.insensitive = true;
            }
            else
            {
                this.prop.insensitive = false;
            }

            this.trigger('set-insensitive', [value]);
        }

        /**
         * Get/set panel's min size.
         */
        get min_size(): number
        {
            return this.prop.min_size;
        }
        set min_size(size: number)
        {
            if (this.min_size === size)
                return;

            this.prop.min_size = size;
            this.trigger('set-min-size', [size]);
        }

        /**
         * Get/set focus.
         */
        get focus(): boolean
        {
            return this.prop.focus;
        }
        set focus(value: boolean)
        {
            if (value === this.focus)
                return;

            if (value)
            {
                this.prop.focus = true;
                this.element.addClass('focused');
                if (this.away)
                {
                    this.away = false;
                    this.prop.away_on_blur = true;
                }
            }
            else
            {
                this.prop.focus = false;
                this.element.removeClass('focused');
                if (this.prop.away_on_blur)
                    this.away = true;
            }

            this.trigger('set-focus', [value]);
        }

        /**
         * Get/set expandable status.
         */
        get expandable(): boolean
        {
            return this.prop.expandable;
        }
        set expandable(value: boolean)
        {
            if (value !== this.expandable)
            {
                this.prop.expandable = value;
                this.trigger('set-expandable', [value]);
            }
        }

        /**
         * Get/set panel's position
         */
        get position(): number
        {
            return this.prop.position;
        }
        set position(value: number)
        {
            this.prop.position = value;
        }

        /**
         * Get/set panel's offset
         */
        get offset(): number
        {
            return this.prop.offset;
        }
        set offset(value: number)
        {
            this.prop.offset = value;
        }

        /**
         * Get/set panel's locked state
         */
        get locked(): boolean
        {
            return this.prop.locked;
        }
        set locked(value: boolean)
        {
            this.prop.locked = value;
        }

        /**
         * Get/set panel's locked state
         */
        get expand(): number
        {
            return this.prop.expanded_for;
        }
        set expand(value: number)
        {
            this.prop.expanded_for = value;
        }

        /**
         * Get away width
         */
        get away_width(): number
        {
            return this.prop.away_width;
        }

        /**
         * Close the panel.
         */
        close(): void
        {
            if (this.locked)
                return;

            this.insensitive = true;
            this.prop.closing = true;

            this.trigger('close');

            this.element.stop(true, false).animate({
                left: (this.position + this.offset) - (this.width + this.expand) - 10,
                opacity: 0
            }, {
               done: () => {
                   this.destroy();
               }
            });
        }
    }
}
