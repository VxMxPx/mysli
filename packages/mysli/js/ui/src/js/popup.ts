/// <reference path="container.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Popup extends Container
    {
        static get CURSOR(): string { return 'cursor'; }
        
        constructor(options: any = {})
        {
            super(options);
            this.element.addClass('ui-popup');
            
            this.prop.def({
                pointer: false,
                position: Popup.CURSOR
            });
            this.prop.push(options, ['pointer!']);
        }
        
        // Get/set pointer
        get pointer(): boolean
        {
            return this.prop.pointer;
        }
        set pointer(value: boolean)
        {
            this.prop.pointer = value;
        }
        
        /**
         * Show the popup.
         */
        show(): void
        {
            
        }
        
        /**
         * Hide the popup.
         */
        hide(): void
        {
            
        }
    }
}