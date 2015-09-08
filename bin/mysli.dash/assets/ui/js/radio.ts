/// <reference path="widget.ts" />
/// <reference path="box.ts" />
/// <reference path="generic_input.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Radio extends GenericInput
    {
        protected static template: string = '<label><div class="ui-gi-input" /><span></span></label>';

        constructor(options: any = {})
        {
            super(options);

            this.element.addClass('ui-radio');
            this.prop.def({
                // Weather items is checked
                checked: false,
                // Weather item can be toggled
                // (Switched on/off)
                toggle: false
            });
            this.prop.push(options, ['toggle', 'checked']);

            this.connect('click', () => {
                if (!this.disabled)
                {
                    if (!this.prop.toggle && this.checked)
                        return;
                    
                    this.checked = !this.checked;
                }
            });
        }

        // Get/set checked value
        get checked(): boolean
        {
            return this.prop.checked;
        }
        set checked(value: boolean)
        {
            this.prop.checked = value;
            this.$input[value ? 'addClass' : 'removeClass']('checked');
        }

        // Get/set toggle state
        get toggle(): boolean
        {
            return this.prop.toggle;
        }
        set toggle(value: boolean)
        {
            this.prop.toggle = value;
        }
    }

    export class RadioGroup extends Widget
    {
        protected box: Box;
        protected checked: Radio;

        constructor(elements: any[], options: any = {})
        {
            super(options);
            this.box = new Box(options);
            this.$element = this.box.element;
            this.element.addClass('ui-radio-group');

            var radio: Radio;
            for (var i: number = 0; i<elements.length; i++)
            {
                radio = new Radio(elements[i]);
                if (radio.checked)
                {
                    if (this.checked)
                        throw new Error("Cannot have two checked radios.");
                    else
                        this.checked = radio;
                }
                radio.connect('click', (e, self) => {
                    if (this.checked && (self.uid !== this.checked.uid))
                        this.checked.checked = false;

                    this.checked = self;
                });
                this.box.push(radio);
            }
        }
    }
}
