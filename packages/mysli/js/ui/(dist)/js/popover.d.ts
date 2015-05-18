/// <reference path="container.d.ts" />
/// <reference path="_inc.common.d.ts" />
declare module mysli.js.ui {
    class Popover extends Container {
        static POSITION_TOP: string;
        static POSITION_LEFT: string;
        static POSITION_RIGHT: string;
        static POSITION_BOTTOM: string;
        private visible;
        constructor(options?: any);
        position: string[] | string;
        pointer: boolean;
        margin: [number, number];
        /**
         * Set place on element.
         * See show for more information.
         * @param placement
         */
        private place(placement);
        /**
         * Show the popover.
         * @param align_to use one of the following:
         *   Click event (e), to position to mouse cursor,
         *   Widget, to position to widget
         *   Array [top, left]
         */
        show(align_to: any): void;
        /**
         * Hide the popover.
         */
        hide(): void;
    }
}
