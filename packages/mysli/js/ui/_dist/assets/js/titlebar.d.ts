/// <reference path="box.d.ts" />
declare module mysli.js.ui {
    class Titlebar extends Box {
        constructor(options?: any);
        insert(widgets: Widget | Widget[], at: number, options?: any): Widget | Widget[];
    }
}
