/// <reference path="widget.d.ts" />
/// <reference path="panel.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class PanelContainer extends Widget {
        private sum_size;
        private expandable_count;
        private active_id;
        private offseted;
        private container_width;
        private resize_timer;
        private collection;
        constructor(options?: any);
        /**
         * Update sum size, when panel is added, removed or away.
         * @param value can be either positive or negative
         * @param modify_before_id, if provided, panels before an id
         * will update position to fit difference.
         */
        update_sum(value: number, modify_before_id?: string): void;
        /**
         * Update view when panel is added/removed or window is resized.
         */
        update_view(): void;
        /**
         * Push panel after particular panel of different ID.
         */
        insert(panel: Panel, after_id: string): Panel;
        /**
         * Add a new panel to the collection.
         */
        push(panel: Panel): Panel;
        /**
         * Get panel by id.
         */
        get(id: string): Panel;
        /**
         * Remove panel by id.
         */
        remove(id: string): void;
        /**
         * Element will resize according to window resize.
         */
        set_resize_with_window(status: boolean, timeout?: number): void;
        /**
         * Set element's size to DOM element's size.
         */
        set_size_from_dom_element(selector: string): void;
        /**
         * Remove old focus, and set new
         */
        switch_focus(status: boolean, panel: Panel): void;
    }
}
