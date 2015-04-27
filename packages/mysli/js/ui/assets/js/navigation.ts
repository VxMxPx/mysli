/// <reference path="button.ts" />
/// <reference path="box.ts" />
/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class Navigation extends Widget {

        protected collection: common.Arr = new common.Arr();
        protected container: Box;

        constructor(items: any, options: any = {}) {
            super(options);
            this.container = new Box(options);
            this.$element = this.container.element;
            this.element.addClass('ui-navigation');

            this.events = common.mix({
                // Respond to a navigation element click!
                // => ( id: string, event: any, navigation: Navigation )
                action: {}
            }, this.events);

            for (var item in items) {
                if (items.hasOwnProperty(item)) {
                    this.container.push(Navigation.produce(items[item], item, this), item);
                }
            }
        }

        private static produce(title: string, id: string, sender: Navigation): Widget {
            var button: Button = new Button({label: title});
            button.connect('click', function (e) {
                this.trigger('action', [id, e]);
            }.bind(sender));
            return button;
        }
    }
}
