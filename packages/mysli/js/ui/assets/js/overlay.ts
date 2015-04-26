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

            common.use(this.prop, this, {
                busy: 'busy',
                visible: 'visible'
            });
        }

        /**
         * Get/Set busy state.
         * @param  {boolean} status
         * @return {boolean}
         */
        busy(status?:boolean):boolean {
            if (typeof status !== 'undefined') {
                this.prop.busy = status;
                if (status) {
                    this.element.addClass('status-busy');
                } else {
                    this.element.removeClass('status-busy');
                }
            }
            return this.prop.busy;
        }

        /**
         * Get/Set visibility state.
         * @param  {boolean} status
         * @return {boolean}
         */
        visible(status?:boolean):boolean {
            if (typeof status !== 'undefined') {
                this.prop.visble = status;
                if (status) {
                    this.element.fadeIn();
                } else {
                    this.element.fadeOut(400);
                }
            }
            return this.element.is(':visible');
        }
    }
}
