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
                    this.container.push(this.produce(items[item], item), item);
                }
            }
        }

        private produce(title: string, id: string): Widget {
            var button: Button = new Button({flat: true, label: title, style: this.style});
            button.connect('click', (e) => {
                this.trigger('action', [id, e]);
            });
            return button;
        }
    }
}
