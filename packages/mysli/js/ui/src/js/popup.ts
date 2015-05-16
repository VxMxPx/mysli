/// <reference path="container.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Popup extends Container
    {
        // Placement consts
        static get POSITION_TOP(): string { return 'top'; }
        static get POSITION_LEFT(): string { return 'left'; }
        static get POSITION_RIGHT(): string { return 'right'; }
        static get POSITION_BOTTOM(): string { return 'bottom'; }
        
        // Weather popup is visible at the moment
        private visible: boolean = false;
        
        constructor(options: any = {})
        {
            super(options);
            this.element.addClass('ui-popup');
            
            this.prop.def({
                position: [
                    Popup.POSITION_TOP, 
                    Popup.POSITION_BOTTOM, 
                    Popup.POSITION_LEFT, 
                    Popup.POSITION_RIGHT
                ],
                width: null,
                pointer: false,
                margin: [0, 0]
            });
            this.prop.push(options, ['position', 'width', 'pointer', 'position', 'margin']);
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
        private place(placement: any)
        {
            // Declare variables
            var element_dimension: {width: number, height: number} = {
                width:  this.element.outerWidth(),
                height: this.element.outerHeight() + 20
            };

            var window_dimension: {width: number, height: number} = {
                width:  $(window).width(),
                height: $(window).height() + $(document).scrollTop()
            };
            
            var final_placement = {};

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

                placement  = {};

                // Calculate top point
                placement.top = {
                    top:  top,
                    left: left + parseInt(String(height / 2), 10)
                };
                
                // Calculate bottom point
                placement.bottom = {
                    left : left + parseInt(String(height / 2), 10),
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

                if (current === 'top') 
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

                    final_placement = placement.top;
                    final_placement['position'] = 'top';
                    
                    break;
                }

                if (current === 'bottom')
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

                    final_placement = placement.bottom;
                    final_placement['position'] = 'bottom';
                    
                    break;
                }

                if (current === 'left')
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

                    final_placement = placement.left;
                    final_placement['position'] = 'left';
                    
                    break;
                }

                if (current === 'right')
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

                    final_placement = placement.right;
                    final_placement['position'] = 'right';
                    
                    break;
                }
            }

            // If we was unable to calculate actual placement,
            // then we'll use the default one.
            if (typeof final_placement['position'] === 'undefined')
            {
                final_placement = placement[this.prop.position[0]];
                final_placement['position'] = this.prop.position[0];
            }
            
            // Apply margin
            final_placement['top'] =+ this.prop.margin[0];
            final_placement['left'] =+ this.prop.margin[1];

            // Finally position element accordingly...
            if (final_placement['position'] === 'top')
            {
                this.element.css({
                    top:  final_placement['top']  - element_dimension.height,
                    left: final_placement['left'] - parseInt(String(element_dimension.width / 2), 10)
                }).addClass('top');
            }
            else if (final_placement['position'] === 'left')
            {
                this.element.css({
                    top:  final_placement['top'] - parseInt(String(element_dimension.height / 2), 10),
                    left: final_placement['left'] - element_dimension.width
                }).addClass('left');
            }
            else if (final_placement['position'] === 'bottom')
            {
                this.element.css({
                    top:  final_placement['top'],
                    left: final_placement['left'] - parseInt(String(element_dimension.width / 2), 10)
                }).addClass('bottom');
            }
            else if (final_placement['position'] === 'right')
            {
                this.element.css({
                    top:  final_placement['top'] - parseInt(String(element_dimension.height / 2), 10),
                    left: final_placement['left']
                }).addClass('right');
            }
        }
        
        /**
         * Show the popup.
         * @param placement use one of the following: 
         *   Click event (e), to position to mouse cursor,
         *   Widget, to position to widget
         *   Array [top, left]
         */
        show(placement: any): void
        {
            // Costume width?
            if (this.prop.width)
            {
                this.element.width(this.prop.width);
            }
            
            // Place element to the correct position
            this.place(placement);
            
            // Element is appended each time, this is
            // so that when panel is closed and hence
            // instance of popup not used anymore,
            // the poput doesn't hang in DOM
            this.element.appendTo('body');
            this.element.animate({
                opacity: 1
            });
            
            // Register events to hide popup when clicked outside
            setTimeout(() => {
                this.element.one('click', (e) => {
                    e.stopPropagation();
                });
                $('body').one('click', () => {
                    this.hide();
                });
            }, 1000);
        }
        
        /**
         * Hide the popup.
         */
        hide(): void
        {
            this.element.animate({
                opacity: 0
            }, {
                always: () => {
                    // See `show` method.
                    this.element.remove();
                } 
            });
        }
    }
}