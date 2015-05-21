/// <reference path="container.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Popover extends Container
    {
        // Placement consts
        static get POSITION_TOP(): string { return 'top'; }
        static get POSITION_LEFT(): string { return 'left'; }
        static get POSITION_RIGHT(): string { return 'right'; }
        static get POSITION_BOTTOM(): string { return 'bottom'; }

        // Weather popover is visible at the moment
        private visible: boolean = false;

        constructor(options: any = {})
        {
            super(options);
            this.element.addClass('ui-popover');

            this.prop.def({
                position: [
                    Popover.POSITION_TOP,
                    Popover.POSITION_BOTTOM,
                    Popover.POSITION_LEFT,
                    Popover.POSITION_RIGHT
                ],
                width: null,
                pointer: true,
                margin: [0, 0]
            });
            this.prop.push(options, ['position', 'width', 'pointer!', 'margin']);
        }

        // Get/set width
        get width(): number
        {
            return this.prop.width;
        }
        set width(value: number)
        {
            this.prop.width = value;
        }

        // Get/set place
        get position(): string[]|string
        {
            return this.prop.position;
        }
        set position(where: string[]|string)
        {
            var available: string[] = ['top', 'left', 'bottom', 'right'];

            if (typeof where === 'string' || where.length < 4)
            {
                if (typeof where === 'string')
                {
                    where = [<string> where];
                }

                this.prop.position = where;

                for (var i = available.length - 1; i >= 0; i--)
                {
                    if (where.indexOf(available[i]) > -1)
                    {
                        continue;
                    }
                    this.prop.position.push(available[i]);
                }
            }
            else
            {
                this.prop.position = where;
            }
        }

        // Get/set pointer
        get pointer(): boolean
        {
            return this.prop.pointer;
        }
        set pointer(value: boolean)
        {
            this.prop.pointer = value;

            if (value)
            {
                this.element.addClass('pointer');
            }
            else
            {
                this.element.removeClass('pointer');
            }
        }

        // Get/set margin
        get margin(): [number, number]
        {
            return this.prop.margin;
        }
        set margin(to: [number, number])
        {
            this.prop.margin = to;
        }

        /**
         * Set place on element.
         * See show for more information.
         * @param placement
         */
        private place(placement: any): {position: string, left: number, top: number}
        {
            // Declare variables
            var element_dimension: {width: number, height: number} = {
                width:  this.element.outerWidth(),
                height: this.element.outerHeight()
            };
            var window_dimension: {width: number, height: number} = {
                width:  $(window).width(),
                height: $(window).height() /*+ $(document).scrollTop()*/
            };
            var final_placement: {position: string, left: number, top: number};

            // Remove position classes
            this.element.removeClass('top left bottom right');

            // Cursor's position (event)
            if (typeof placement.pageX !== 'undefined')
            {
                placement = {
                    top: {
                        top:  placement.pageY,
                        left: placement.pageX
                    }
                };
                placement.bottom = placement.top;
                placement.left   = placement.top;
                placement.right  = placement.top;
            }
            else if (placement instanceof Widget)
            {
                // Element's position
                var top: number    = placement.element.offset().top;
                var left: number   = placement.element.offset().left;
                var width: number  = placement.element.outerWidth();
                var height: number = placement.element.outerHeight();

                placement = {};

                // Calculate top point
                placement.top = {
                    top:  top,
                    left: left + parseInt(String(width / 2), 10)
                };

                // Calculate bottom point
                placement.bottom = {
                    left : left + parseInt(String(width / 2), 10),
                    top  : top  + height
                };

                // Calculate left point
                placement.left = {
                    top  : top + parseInt(String(height / 2), 10),
                    left : left
                };

                // Calculate right point
                placement.right = {
                    left : left + width,
                    top  : top + parseInt(String(height / 2), 10)
                };
            }
            else if (typeof placement.top === 'number')
            {
                // Number, we have valid absolute point
                placement = {
                    top : placement
                };

                placement.bottom = placement.top;
                placement.left   = placement.top;
                placement.right  = placement.top;
            }
            else
            {
                throw new Error('You need to provide a valid placement.');
            }

            // Try to set placement now
            for (var i = 0, l = this.prop.position.length; i < l; i++)
            {
                var current: string = this.prop.position[i];

                if (current === Popover.POSITION_TOP)
                {
                    if (placement.top.top - element_dimension.height < $(document).scrollTop())
                    {
                        continue;
                    }

                    if (placement.top.left + parseInt(String(element_dimension.width / 2), 10) > window_dimension.width)
                    {
                        continue;
                    }

                    if (placement.top.left - parseInt(String(element_dimension.width / 2), 10) < 0)
                    {
                        continue;
                    }

                    final_placement = {
                        top: placement.top.top,
                        left: placement.top.left,
                        position: Popover.POSITION_TOP
                    };
                    break;
                }

                if (current === Popover.POSITION_BOTTOM)
                {
                    if (placement.bottom.top + element_dimension.height > window_dimension.height)
                    {
                        continue;
                    }

                    if (placement.bottom.left + parseInt(String(element_dimension.width / 2), 10) > window_dimension.width)
                    {
                        continue;
                    }

                    if (placement.bottom.left - parseInt(String(element_dimension.width / 2), 10) < 0)
                    {
                        continue;
                    }

                    final_placement = {
                        left: placement.bottom.left,
                        top: placement.bottom.top,
                        position: Popover.POSITION_BOTTOM
                    }
                    break;
                }

                if (current === Popover.POSITION_LEFT)
                {
                    if (placement.left.top - parseInt(String(element_dimension.height / 2), 10) < $(document).scrollTop())
                    {
                        continue;
                    }

                    if (placement.left.top + parseInt(String(element_dimension.height / 2), 10) > window_dimension.height)
                    {
                        continue;
                    }

                    if (placement.left.left < 0)
                    {
                        continue;
                    }

                    final_placement = {
                        left: placement.left.left,
                        top: placement.left.top,
                        position: Popover.POSITION_LEFT
                    }
                    break;
                }

                if (current === Popover.POSITION_RIGHT)
                {
                   if (placement.right.top - parseInt(String(element_dimension.height / 2), 10) < $(document).scrollTop())
                   {
                        continue;
                    }

                    if (placement.right.left + element_dimension.width > window_dimension.width)
                    {
                        continue;
                    }

                    if (placement.right.top + parseInt(String(element_dimension.height / 2), 10) > window_dimension.height)
                    {
                        continue;
                    }

                    final_placement = {
                        left: placement.right.left,
                        top: placement.right.top,
                        position: Popover.POSITION_RIGHT
                    }
                    break;
                }
            }

            // If we was unable to calculate actual placement,
            // then we'll use the default one.
            if (typeof final_placement.position === 'undefined')
            {
                final_placement = {
                    left: placement[this.prop.position[0]].left,
                    top: placement[this.prop.position[0]].top,
                    position: this.prop.position[0]
                }
            }

            // Apply margin
            final_placement['top'] += this.prop.margin[0];
            final_placement['left'] += this.prop.margin[1];

            // Finally position element accordingly...
            if (final_placement['position'] === Popover.POSITION_TOP)
            {
                final_placement['top'] -= element_dimension.height;
                final_placement['left'] -= parseInt(String(element_dimension.width / 2), 10);
                this.element.css({
                    top:  final_placement['top'],
                    left: final_placement['left']
                }).addClass('top');
            }
            else if (final_placement['position'] === Popover.POSITION_LEFT)
            {
                final_placement['top'] -= parseInt(String(element_dimension.height / 2), 10);
                final_placement['left'] -= element_dimension.width;
                this.element.css({
                    top:  final_placement['top'],
                    left: final_placement['left']
                }).addClass('left');
            }
            else if (final_placement['position'] === Popover.POSITION_BOTTOM)
            {
                final_placement['left'] -= parseInt(String(element_dimension.width / 2), 10);
                this.element.css({
                    top:  final_placement['top'],
                    left: final_placement['left']
                }).addClass('bottom');
            }
            else if (final_placement['position'] === Popover.POSITION_RIGHT)
            {
                final_placement['top'] -= parseInt(String(element_dimension.height / 2), 10);
                this.element.css({
                    top:  final_placement['top'],
                    left: final_placement['left']
                }).addClass('right');
            }

            return final_placement;
        }

        /**
         * Show the popover.
         * @param align_to use one of the following:
         *   Click event (e), to position to mouse cursor,
         *   Widget, to position to widget
         *   Array [top, left]
         */
        show(align_to: any): void
        {
            var placement: { position: string, left: number, top: number; };
            var animation: any = {
                opacity: 1
            };

            if (this.visible)
            {
                return;
            }

            // Costume width?
            if (this.prop.width)
            {
                this.element.width(this.prop.width);
            }

            // Element is appended each time, this is
            // so that when panel is closed and hence
            // instance of popover not used anymore,
            // the poput doesn't hang in DOM
            this.element.appendTo('body');

            // Place element to the correct placement
            // Element MUST be appended before placed.
            placement = this.place(align_to);

            // Animate position a bit
            switch (placement.position)
            {
                case Popover.POSITION_TOP:
                    animation['top'] = (placement.top - 10) + 'px';
                    break;

                case Popover.POSITION_BOTTOM:
                    animation['top'] = (placement.top + 10) +'px';
                    break;

                case Popover.POSITION_LEFT:
                    animation['left'] = (placement.left - 10) +'px';
                    break;

                case Popover.POSITION_RIGHT:
                    animation['left'] = (placement.left + 10) +'px';
                    break;
            }

            this.element.animate(animation);
            this.visible = true;

            // Register events to hide popover when clicked outside
            setTimeout(() => {
                this.element.on('click', function (e) {
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                });
                $('body').one('click', () => {
                    this.hide();
                });
            }, 100);
        }

        /**
         * Hide the popover.
         */
        hide(): void
        {
            this.element.animate({
                opacity: 0
            }, {
                always: () => {
                    this.visible = false;
                    // See `show` method.
                    this.element.remove();
                    this.element.off('click');
                }
            });
        }
    }
}
