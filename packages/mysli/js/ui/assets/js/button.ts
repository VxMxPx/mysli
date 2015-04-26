/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class Button extends Widget {

        protected static template: string = '<button class="ui-widget ui-button" />';

        constructor(options:any={}) {
            super(options);

            // Properties
            this.prop = common.mix({
                label: null,
                icon: {
                    name: null,
                    position: 'left',
                    spin: false
                }
            }, this.prop);

            // Apply defaults
            common.use(this.prop, this, {
                label: 'label',
                icon: 'icon',
                style: 'style',
                flat: 'flat',
                disabled: 'disabled'
            });
        }

       /**
         * Get/Set button's label.
         * @param  {string} value
         * @return {string}
         */
        label(value?:string):string {
            var $label:JQuery = this.element.find('span.label');
            var method:string;

            if (typeof value !== 'undefined') {
                if (value === null) {
                    $label.remove();
                    return '';
                }

                if (!$label.length) {
                    $label = $('<span class="label" />');
                    method = this.prop.icon.position === 'right' ? 'prepend' : 'append';
                    this.element[method]($label);
                }

                $label.text(value);
            }

            return $label.text();
        }

        /**
         * Get/Set Button's Icon
         * @param  {any} String: icon || Object: {icon: 0, position: 0, spin: 0} || false: remove icon
         * @return {any} {icon: 0, position: 0, spin: 0}
         */
        icon(icon?:any):any {
            var $icon:JQuery;
            var method:string;
            var spin:string;

            if (typeof icon !== 'undefined') {
                $icon = this.element.find('i.fa');
                $icon.remove();

                if (icon === null || icon.name === null) {
                    this.prop.icon.name = null;
                    return this.prop.icon;
                }

                if (typeof icon === 'string') {
                    this.prop.icon.name = icon;
                } else {
                    this.prop.icon = common.mix(this.prop.icon, icon);
                }

                method = this.prop.icon.position === 'right' ? 'append' : 'prepend';
                spin = this.prop.icon.spin ? ' fa-spin' : '';

                this.element[method]($('<i class="fa fa-'+this.prop.icon.name+spin+'" />'));
            }

            return this.prop.icon;
        }
    }
}
