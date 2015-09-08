/// <reference path="widget.ts" />

module mysli.js.ui
{
    export class Overlay extends Widget
    {
        protected static template: string = '<div class="ui-overlay ui-widget"><div class="ui-overlay-busy"><i class="fa fa-cog fa-spin"></i></div></div>';

        constructor(options: any = {})
        {
            super(options);

            this.prop.def({
                busy: false,
                visible: true
            });
            this.prop.push(options, ['busy', 'visible']);
        }

        // Get/set busy state.
        get busy(): boolean
        {
            return this.prop.busy;
        }
        set busy(status: boolean)
        {
            this.prop.busy = status;
            this.element[status ? 'addClass' : 'removeClass']('status-busy');
        }

        // Get/set visibility
        get visible(): boolean
        {
            return this.element.is(':visible');
        }
        set visible(status: boolean)
        {
            this.prop.visible = status;
            this.element[status ? 'show' : 'hide']();
        }
    }
}
