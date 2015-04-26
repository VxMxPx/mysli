/// <reference path="box.ts" />

module mysli.js.ui {
    export class Titlebar extends Box {
        constructor(options: any={}) {
            options.orientation = Box.HORIZONTAL;
            super(options);
            this.element.addClass('ui-titlebar');
        }
    }
}
