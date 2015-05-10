/// <reference path="widget.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
    export class HTML extends Widget {

        constructor(text: any = {}, options: any = {}) {
            if (text !== null && typeof text === 'object') {
                options = text;
            }
            super(options);
            this.element.addClass('ui-html');

            if (typeof text === 'string') {
                this.push(text);
            }
        }

        /**
         * Push new HTML to the container.
         * @param html
         */
        push(html: string): JQuery {
            var element: JQuery;

            // Wrap HTML in a div
            html = `<div class="ui-html-element">${html}</div>`;

            element = $(html);

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
