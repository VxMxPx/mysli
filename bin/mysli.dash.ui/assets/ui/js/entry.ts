/// <reference path="generic_input.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Entry extends GenericInput
    {
        protected static template: string = '<label><span></span><input class="ui-gi-input" /></label>';

        public static get TYPE_TEXT(): string { return 'text'; }
        public static get TYPE_PASSWORD(): string { return 'password'; }

        constructor (options: any = {})
        {
            super(options);

            this.element.addClass('ui-entry');
            this.prop.def({
                type: Entry.TYPE_TEXT,
                placeholder: null
            });
            this.prop.push(options, ['type!', 'placeholder']);
        }

        // Get/set type
        get type(): string
        {
            return this.prop.type;
        }
        set type(value: string)
        {
            switch (value)
            {
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
        get placeholder(): string
        {
            return this.prop.placeholder;
        }
        set placeholder(value: string)
        {
            this.prop.placeholder = value;
            this.$input.prop('placeholder', value);
        }
    }
}
