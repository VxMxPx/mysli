/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class GenericInput extends Widget
    {
        protected static element: string = '<label><span></span><div class="ui-gi-input">Generic Input Should be Extended</div></label>';
        protected $input: JQuery;
        protected $label: JQuery;
        
        constructor (options: any = {})
        {
            super(options);

            this.element.addClass('ui-generic-input');
            this.$input = this.element.find('.ui-gi-input');
            this.$label = this.element.find('span');
            this.prop.def({
                label: null
            });
            this.prop.push(options, ['label!', 'disabled']);
        }
        
        // Override disabled status
        get disabled(): boolean
        {
            return this.prop.disabled;
        }
        set disabled(value: boolean)
        {
            if (!this.$input)
                return;

            this.prop.disabled = value;
            this.$input[value ? 'addClass' : 'removeClass']('disabled');
            this.$input.prop('disabled', value);
        }
        
        // Get/set label
        get label(): string
        {
            return this.prop.label;
        }
        set label(value: string)
        {
            this.prop.label = value;
            this.$label.text(value);
        }
    }
}