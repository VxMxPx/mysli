/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class HTML extends Widget {

        constructor(options: any = {}) {
            super(options);
            this.element.addClass('ui-html');
        }

        /**
         * Push new HTML to the container.
         * @param html
         */
        push(html: string): JQuery {
            var element: JQuery = $(html);
            this.element.append(element);
            return element;
        }

        /**
         * Remove element(s) by specific jQuery selector.
         * @param selector
         */
        remove(selector: string): void {
            this.element.filter(selector).remove();
        }
    }
}
