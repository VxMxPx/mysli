/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui {
    export class Entry extends Widget {
        protected static template: string = '<input />';
        protected $input: JQuery;

        public static get TYPE_TEXT(): string { return 'text'; }
        public static get TYPE_PASSWORD(): string { return 'password'; }

        constructor (options: any = {}) {
            super(options);

            this.element.addClass('ui-entry');
            this.$input = this.element;
            this.prop.def({
                type: Entry.TYPE_TEXT,
                placeholder: null,
                label: null
            });
            this.prop.push(options, ['type!', 'label!', 'placeholder']);
        }

        // Get/set type
        get type(): string {
            return this.prop.type;
        }
        set type(value: string) {
            switch (value) {
                case Entry.TYPE_TEXT:
                    this.$input.prop('type', 'text');
                    break;

                case Entry.TYPE_PASSWORD:
                    this.$input.prop('type', 'password');
                    break;

                default:
                    throw new Error(`Invalid type: ${value}`);
            }
            
            this.prop.type = value;
        }
        
        // Get/set placeholder
        get placeholder(): string {
            return this.prop.placeholder;
        }
        set placeholder(value: string) {
            this.prop.placeholder = value;
            this.$input.prop('placeholder', value);
        }
        
        // Get/set label
        get label(): string {
            return this.prop.label;
        }
        set label(value: string) {
            if (this.prop.label) {
                if (!value) {
                    this.$input.unwrap();
                    this.$element = this.$input;
                } else {
                    this.element.find('span').text(value);
                }
                this.prop.label = value;
                return;
            } else {
                if (value) {
                    this.$input.wrap('<label />'); 
                    this.$element = this.$input.parent();
                    this.element.prepend(`<span>${value}</span>`);
                    this.prop.label = value;
                }
            }
        }
    }
}
