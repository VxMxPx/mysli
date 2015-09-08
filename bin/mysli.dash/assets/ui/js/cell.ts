/// <reference path="container.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Cell
    {
        protected parent: Container;
        protected $element: JQuery;
        protected prop: any;

        public static get SCROLL_Y(): string { return 'scroll-y'; }
        public static get SCROLL_X(): string { return 'scroll-x'; }
        public static get SCROLL_BOTH(): string { return 'scroll-both'; }
        public static get SCROLL_NONE(): string { return 'scroll-none'; }

        public static get ALIGN_LEFT(): string { return 'left'; }
        public static get ALIGN_RIGHT(): string { return 'right'; }

        constructor(parent: Container, $cell: JQuery, options: any = {})
        {
            this.parent = parent;
            this.$element = $cell;

            this.prop = new common.Prop({
                // Weather cell is visible
                visible: true,
                // Cell's padding
                padding: false,
                // Cell's border
                border: false,
                // Weather content should be filled to full width
                fill: false,
                // Where to align cell's content
                align: Cell.ALIGN_LEFT,
                // Weather content can be scrolled
                scroll: Cell.SCROLL_NONE
            }, this);

            this.prop.push(
                options,
                ['visible', 'padding', 'border', 'fill', 'align', 'scroll']);
        }

        // Element
        get element(): JQuery
        {
            return this.$element;
        }

        /**
         * Animate the cell.
         * @param what
         * @param duration
         * @param callback
         */
        animate(what: any, duration: number = 500, callback: any = false): void
        {
            this.element.animate(what, duration, callback);
        }

        // Get/set padded
        get padding(): boolean|any[]
        {
            return this.prop.padding;
        }
        set padding(value: boolean|any[])
        {
            var positions: string[] = ['top', 'right', 'bottom', 'left'];
            var current: number|boolean;

            this.element.css('padding', '');

            // Value is Boolean e.g.: element.padding = false
            if (typeof value === 'boolean')
            {
                value = {top: value, right: value, bottom: value, left: value};
            }

            // Map values
            for (var i=0; i<positions.length; i++)
            {
                if (typeof value[i] !== 'undefined')
                {
                    current = value[i];
                }
                else if (typeof value[positions[i]] !== 'undefined')
                {
                    current = value[positions[i]];
                }
                else
                {
                    current = null;
                }

                if (typeof current === 'number')
                {
                    this.element.css(`padding-${positions[i]}`, current);
                }
                else
                {
                    this.element[current ? 'addClass' : 'removeClass'](`padding-${positions[i]}`);
                }
            }
        }

        // Get/set border
        get border(): boolean|any[]
        {
            return this.prop.border;
        }
        set border(value: boolean|any[])
        {
            var positions: string[] = ['top', 'right', 'bottom', 'left'];
            var current: number|boolean;

            // Value is Boolean e.g.: element.border = false
            if (typeof value === 'boolean')
            {
                value = {top: value, right: value, bottom: value, left: value};
            }

            // Map values
            for (var i=0; i<positions.length; i++)
            {
                if (typeof value[i] !== 'undefined')
                {
                    current = value[i];
                }
                else if (typeof value[positions[i]] !== 'undefined')
                {
                    current = value[positions[i]];
                }
                else
                {
                    current = null;
                }

                this.element[current ? 'addClass' : 'removeClass'](`border-${positions[i]}`);
            }
        }

        // Get/set visibility
        get visible(): boolean
        {
            return this.prop.visible;
        }
        set visible(status: boolean)
        {
            this.prop.visible = status;
            this.element[status ? 'show' : 'hide']();
        }

        // Get/set align
        get align(): string
        {
            return this.prop.align;
        }
        set align(value: string)
        {
            this.prop.align = value;
            if (value === Cell.ALIGN_LEFT)
            {
                this.element.removeClass('align-right');
                this.element.addClass('align-left');
            }
            else
            {
                this.element.addClass('align-right');
                this.element.removeClass('align-left');
            }
        }

        // Get/set fill
        get fill(): boolean
        {
            return this.prop.fill;
        }
        set fill(value: boolean)
        {
            this.prop.fill = value;
            this.element[value ? 'addClass' : 'removeClass']('content-fill');
        }

        // Get/set scroll
        get scroll(): string
        {
            return this.prop.scroll;
        }
        set scroll(value: string)
        {
            switch (value)
            {
                case Cell.SCROLL_X:
                    this.element.addClass('scroll-x');
                    this.element.removeClass('scroll-y');
                    this.prop.scroll = value;
                    break;

                case Cell.SCROLL_Y:
                    this.element.addClass('scroll-y');
                    this.element.removeClass('scroll-x');
                    this.prop.scroll = value;
                    break;

                case Cell.SCROLL_BOTH:
                    this.element.removeClass('scroll-x');
                    this.element.removeClass('scroll-y');
                    this.prop.scroll = value;
                    break;

                case Cell.SCROLL_BOTH:
                    this.element.addClass('scroll-x');
                    this.element.addClass('scroll-y');
                    this.prop.scroll = value;
                    break;

                default:
                    throw new Error("Invalid value required: Cell.(SCROLL_X|SCROLL_Y|SCROLL_BOTH|SCROLL_NONE)");
            }
        }

        /**
         * Remove cell from a collection.
         */
        remove():void
        {
            this.element.remove();
        }
    }
}
