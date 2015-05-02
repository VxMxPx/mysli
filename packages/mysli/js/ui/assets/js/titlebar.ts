/// <reference path="box.ts" />

module mysli.js.ui {
    export class Titlebar extends Box {
        constructor(options: any = {}) {
            options.orientation = Box.VERTICAL;
            super(options);
            this.element.addClass('ui-titlebar');
        }

        insert(widgets: Widget|Widget[], at: number, options?: any): Widget|Widget[] {
            if (widgets.constructor === Array) {
                for (var i = 0; i < (<Widget[]> widgets).length; i++) {
                    widgets[i].flat = true;
                    super.insert(widgets[i], at, options);
                }
                return widgets;
            } else {
                (<Widget> widgets).flat = true;
                return super.insert(widgets, at, options);
            }
        }
    }
}
