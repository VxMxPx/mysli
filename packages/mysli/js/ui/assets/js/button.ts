/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class Button extends Widget {

        protected static template: string = '<button class="ui-widget ui-button"></button>';
        protected static allowed_styles: string[] = ['default', 'alt', 'primary', 'confirm', 'attention'];

        constructor(options: any = {}) {

            super(options);

            this.prop.def({
                label: null,
                icon: {
                    name: null,
                    position: 'left',
                    spin: false
                }
            });
            this.prop.push(options, ['icon!', 'label!']);
        }

        // Get/set button's label
        get label(): string {
            return this.prop.label;
        }
        set label(value: string) {
            var $label: JQuery = this.element.find('span.label');
            var method: string;

            this.prop.label = value;

            if (!value) {
                $label.remove();
                return;
            }

            if (!$label.length) {
                $label = $('<span class="label" />');
                method = this.icon.position === 'right' ? 'prepend' : 'append';
                this.element[method]($label);
            }

            $label.text(value);
        }

        // Get/set icon
        get icon(): string|{name?: string; position?: string; spin?: boolean} {
            return this.prop.icon;
        }
        set icon(options: string|{name?: string; position?: string; spin?: boolean}) {
            var $icon: JQuery;
            var method: string;
            var spin: string;

            $icon = this.element.find('i.fa');
            $icon.remove();

            if (typeof  options === 'string') {
                options = {name: <string> options};
            }

            if (!options['name']) {
                this.prop.icon.name = null;
                return;
            }

            this.prop.icon = common.mix(this.prop.icon, options);

            method = this.prop.icon.position === 'right' ? 'append' : 'prepend';
            spin = this.prop.icon.spin ? ' fa-spin' : '';

            this.element[method]($(`<i class="fa fa-${this.prop.icon.name}${spin}" />`));
        }
    }
}
