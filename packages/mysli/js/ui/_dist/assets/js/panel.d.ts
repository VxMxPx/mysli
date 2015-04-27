/// <reference path="widget.d.ts" />
/// <reference path="panel_side.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Panel extends Widget {
        static SIZE_TINY: number;
        static SIZE_SMALL: number;
        static SIZE_NORMAL: number;
        static SIZE_BIG: number;
        static SIZE_HUGE: number;
        private closing;
        private old_zindex;
        private old_width;
        front: PanelSide;
        back: PanelSide;
        constructor(options?: any);
        /**
         * Animate all the changes made to the element.
         */
        animate(callback?: () => any): void;
        /**
         * Get/set panel's width
         */
        width: number;
        /**
         * Get/set away status for panel.
         */
        away: boolean;
        /**
         * Get/set panel's popout status.
         */
        popout: boolean;
        /**
         * Get/get insensitive status.
         */
        insensitive: boolean;
        /**
         * Get/set panel's min size.
         */
        min_size: number;
        /**
         * Get/set focus.
         */
        focus: boolean;
        /**
         * Get/set expandable status.
         */
        expandable: boolean;
        /**
         * Get/set panel's position
         */
        position: number;
        /**
         * Get/set panel's offset
         */
        offset: number;
        /**
         * Get/set panel's locked state
         */
        locked: boolean;
        /**
         * Get/set panel's locked state
         */
        expand: number;
        /**
         * Get away width
         */
        away_width: number;
        /**
         * Close the panel.
         */
        close(): void;
    }
}
