/// <reference path="widget.ts" />
module mysli.js.ui {
    export class Overlay extends Widget {

        protected static template: string = '<div class="ui-overlay ui-widget"><div class="ui-overlay-busy"><i class="fa fa-cog fa-spin"></i></div></div>';

        constructor(options) {
            super(options);

            this.prop = common.mix({
                busy: false,
                visible: false
            }, this.prop);

            this.busy = this.prop.busy;
            this.visibility = this.prop.visible;
        }

        // Get/set busy state.
        get busy(): boolean {
            return this.prop.busy;
        }
        set busy(status: boolean) {
            this.prop.busy = status;
            this.element[status ? 'addClass' : 'removeClass']('status-busy');
        }

        // Get/set visibility
        get visibility(): boolean {
            return this.element.is(':visible');
        }
        set visibility(status: boolean) {
            this.prop.visible = status;
            this.element[status ? 'show' : 'hide']();
        }
    }
}
