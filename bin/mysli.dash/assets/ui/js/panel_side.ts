/// <reference path="container.ts" />

module mysli.js.ui
{
    export class PanelSide extends Container
    {
        constructor(options: any = {})
        {
            super(options);
            this.element.addClass('ui-panel-side');
        }
    }
}
