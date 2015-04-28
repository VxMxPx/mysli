/// <reference path="box.ts" />

module mysli.js.ui {
    export class Titlebar extends Box {
        constructor(options: any = {}) {
            options.orientation = Box.VERTICAL;
            super(options);
            this.element.addClass('ui-titlebar');
        }

        insert(...options): Widget {
            options[0].flat = true;
            return super.insert.apply(this, options);
        }
    }
}
