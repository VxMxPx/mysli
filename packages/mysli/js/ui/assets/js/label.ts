/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui {
    export class Label extends Widget {

        public static get DEFAULT(): number { return 1; }
        public static get TITLE(): number { return 2; }
        public static get INPUT(): number { return 3; }

        protected static template: string = '<span class="ui-widget ui-title" />';

        constructor(options: any = {}) {
            super(options);

            this.prop.def({
                // Label's text
                text: '',
                // Type, see constants defined above
                type: Label.DEFAULT,
                // Weather this label is connected to an input.
                // Setting this to Widget, will force label's type to INPUT
                input: null
            });
            this.prop.push(options, ['type!', 'text!', 'input']);
        }

        /**
         * Get/set type.
         */
        get type(): number {
            return this.prop.type;
        }
        set type(type: number) {
            var element: JQuery;

            switch (type) {
                case Label.DEFAULT:
                    this.input = null;
                    element = $('<span />');
                    break;
                case Label.TITLE:
                    this.input = null;
                    element = $('<h2 />');
                    break;
                case Label.INPUT:
                    element = $('<label />');
                    break;
                default:
                    throw new Error(`Invalid type provided: ${type}`);
            }

            this.element.empty();
            this.prop.type = type;
            element.text(this.text);
            this.element.append(element);
        }

        /**
         * Get/set text
         */
        get text(): string {
            return this.prop.text;
        }
        set text(value: string) {
            this.prop.text = value;
            this.element.find(':first-child').text(value);
        }

        /**
         * Connect an input to a wiget.
         */
        get input(): Widget {
            return this.prop.input;
        }
        set input(widget: Widget) {
            if (!widget) {
                if (this.input) {
                    this.element.find('label').prop('for', false);
                    this.prop.input = null;
                    this.prop.input.destroy();
                }
            } else {
                this.prop.input = widget;
                if (!widget.element.prop('id')) {
                    widget.element.prop('id', widget.uid);
                }
                this.type = Label.INPUT;
                this.element.find('label').prop('for', widget.uid);
            }
        }
    }
}
