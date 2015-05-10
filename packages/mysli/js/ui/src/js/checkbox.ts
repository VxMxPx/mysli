/// <reference path="generic_input.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui {
    export class Checkbox extends GenericInput {
        protected static template: string = '<label><div class="ui-gi-input"><i class="fa fa-check" /></div><span></span></label>';
        protected $checked: JQuery;
        
        constructor(options: any = {}) {
            super(options);
            
            this.$checked = this.$input.find('i');
            this.element.addClass('ui-checkbox');
            this.prop.def({
                checked: false
            });
            this.prop.push(options, ['checked']);
            
            this.connect('click', () => {
                if (!this.disabled) {
                    this.checked = !this.checked;
                }
            });
        }
        
        // Get/set checked value
        get checked(): boolean {
            return this.prop.checked;
        }
        set checked(value: boolean) {
            this.prop.checked = value;
            this.$input[value ? 'addClass' : 'removeClass']('checked');
        }
    }
}