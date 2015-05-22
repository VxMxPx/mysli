/// <reference path="container.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Stack extends Container
    {
        // VARIABLES
        private current: string;

        // CONSTANTS
        static get ANI_SLIDE_UP(): string { return 'animate-slide-up'; }
        static get ANI_SLIDE_DOWN(): string { return 'animate-slide-down'; }
        static get ANI_SLIDE_LEFT(): string { return 'animate-slide-left'; }
        static get ANI_SLIDE_RIGHT(): string { return 'animate-slide-right'; }
        static get ANI_FADE(): string { return 'animate-fade'; }

        // METHODS
        constructor(options: any = {})
        {
            super(options);
            this.element.addClass('ui-stack');

            this.prop.def({
                // Which animation to use, when switching between tabs...
                animation: Stack.ANI_SLIDE_LEFT
            });
        }

        // Get/set animation
        get animation(): string
        {
            return this.prop.animation;
        }
        set animation(value: string)
        {
            this.element.removeClass(this.prop.animation);
            this.element.addClass(value);
            this.prop.animation = value;
        }

        /**
         * Go to particular view.
         * @param id
         */
        to(id: string): void
        {
            if (this.current !== id)
            {
                if (this.current)
                {
                    this.animate((<Cell>this.get(this.current, true)), 'hide');
                }
                this.current = id;
                this.animate((<Cell>this.get(id, true)), 'show');
            }
        }

        /**
         * Animate cell(s)
         * @param cell
         * @param type
         */
        private animate(cell: Cell, type: string): void
        {
            cell.element.css('position', 'absolute');
            cell.element['fade'+(type === 'show' ? 'In' : 'Out')]({
                always: function () {
                    cell.element.css('position', 'relative');
                }
            });
        }
    }
}
