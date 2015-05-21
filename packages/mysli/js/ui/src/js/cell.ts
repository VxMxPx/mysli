/// <reference path="container.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Cell
    {
        protected parent: Container;
        protected $cell: JQuery;
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
            this.$cell = $cell;

            this.prop = new common.Prop({
                // Weather cell is visible
                visible: true,
                // Cell's padding
                padding: false,
                // Weather content should be filled to full width
                fill: false,
                // Where to align cell's content
                align: Cell.ALIGN_LEFT,
                // Weather content can be scrolled
                scroll: Cell.SCROLL_NONE
            }, this);
            this.prop.push(options, ['visible', 'padding', 'fill', 'align', 'scroll']);
        }

        /**
         * Animate the cell.
         * @param what
         * @param duration
         * @param callback
         */
        animate(what: any, duration: number = 500, callback: any = false): void
        {
            this.$cell.animate(what, duration, callback);
        }

        // Get/set padded
        get padding(): boolean|any[]
        {
            return this.prop.padding;
        }
        set padding(value: boolean|any[])
        {
            var positions: string[] = ['top', 'right', 'bottom', 'left'];

            this.$cell.css('padding', '');

            if (typeof value === 'boolean')
                value = [value, value, value, value];

            for (var i=0; i<positions.length; i++)
            {
                if (typeof value[i] === 'number')
                    this.$cell.css(`padding-${positions[i]}`, value[i]);
                else
                    this.$cell[value[i] ? 'addClass' : 'removeClass'](`pad${positions[i]}`);
            }
        }

        // Get/set visibility
        get visible(): boolean
        {
            return this.prop.visible;
        }
        set visible(status: boolean)
        {
            if (status === this.prop.visible)
                return;

            this.prop.visible = status;
            this.$cell[status ? 'show' : 'hide']();
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
                this.$cell.removeClass('align-right');
                this.$cell.addClass('align-left');
            }
            else
            {
                this.$cell.addClass('align-right');
                this.$cell.removeClass('align-left');
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
            this.$cell[value ? 'addClass' : 'removeClass']('content-fill');
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
                    this.$cell.addClass('scroll-x');
                    this.$cell.removeClass('scroll-y');
                    this.prop.scroll = value;
                    break;

                case Cell.SCROLL_Y:
                    this.$cell.addClass('scroll-y');
                    this.$cell.removeClass('scroll-x');
                    this.prop.scroll = value;
                    break;

                case Cell.SCROLL_BOTH:
                    this.$cell.removeClass('scroll-x');
                    this.$cell.removeClass('scroll-y');
                    this.prop.scroll = value;
                    break;

                case Cell.SCROLL_BOTH:
                    this.$cell.addClass('scroll-x');
                    this.$cell.addClass('scroll-y');
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
            this.$cell.remove();
        }
    }
}
