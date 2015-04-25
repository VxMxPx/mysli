/// <reference path="widget.ts" />
/// <reference path="_arr.ts" />
module mysli.js.ui {
    export class Navigation extends Widget {

        private static collection: Arr = new Arr();

        constructor(options: {items: any}) {
            super(options);

            this.events.list = Util.mix({
                // When any navigation item is clicked
                // ( string id, object this )
                action: {}
            }, this.events.list);

            this.prop.allowed_styles = ['default', 'alt'];

            this.element().on('click', '.ui-navigation-item', function(e) {
                var id: string;
                e.stopPropagation();
                id = e.currentTarget.id.substr(6);
                this.trigger('action', [id]);
            });
        }

    }
}
