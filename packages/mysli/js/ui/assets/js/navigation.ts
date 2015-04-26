/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class Navigation extends Widget {

        protected static allowed_styles: string[] = ['default', 'alt'];
        private static collection: common.Arr = new common.Arr();

        constructor(options: {items: any}) {
            super(options);

            this.events = common.mix({
                // When any navigation item is clicked
                // ( string id, object this )
                action: {}
            }, this.events);

            this.element.on('click', '.ui-navigation-item', function(e) {
                var id: string;
                e.stopPropagation();
                id = e.currentTarget['id'].substr(6);
                this.trigger('action', [id]);
            });
        }

    }
}
