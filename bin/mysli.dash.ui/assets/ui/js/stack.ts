/// <reference path="container.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Stack extends Container
    {
        // VARIABLES
        private current: string;

        // CONSTANTS
        static get ANI_SLIDE_UP(): string { return 'animate-slide-up'; }
        static get ANI_SLIDE_DOWN(): string { return 'animate-slide-down'; }
        static get ANI_SLIDE_LEFT(): string { return 'animate-slide-left'; }
        static get ANI_SLIDE_RIGHT(): string { return 'animate-slide-right'; }
        static get ANI_SLIDE_DIRECTION_VERTICAL(): string { return 'animate-slide-direction-vertical'; }
        static get ANI_SLIDE_DIRECTION_HORIZONTAL(): string { return 'animate-slide-direction-horizontal'; }
        static get ANI_FADE(): string { return 'animate-fade'; }

        // METHODS
        constructor(options: any = {})
        {
            super(options);
            this.element.addClass('ui-stack');

            this.prop.def({
                // Which animation to use, when switching between tabs...
                animation: Stack.ANI_SLIDE_DIRECTION_HORIZONTAL
            });
            this.prop.push(options, ['animation']);
        }

        // Get/set animation
        get animation(): string
        {
            return this.prop.animation;
        }
        set animation(value: string)
        {
            this.element.removeClass(this.prop.animation);
            this.element.addClass(value);
            this.prop.animation = value;
        }

        /**
         * Go to particular view.
         * @param id
         */
        to(id: string): void
        {
            var direction: string;

            if (this.current !== id)
            {
                if (this.current)
                {
                    var from: number = this.collection.get_index(this.current);
                    var to: number = this.collection.get_index(id);
                    direction = from < to ? 'positive' : 'negative';

                    this.animate(
                        (<Cell>this.get(this.current, true)),
                        'hide',
                        this.prop.animation,
                        direction
                    );
                }
                else
                {
                    direction = 'positive';
                }

                this.current = id;
                this.animate(
                    (<Cell>this.get(id, true)),
                    'show',
                    this.prop.animation,
                    direction
                );
            }
        }

        /**
         * Animate cell(s)
         * @param cell
         * @param visibility show|hide
         * @param animation_type
         * @param direction positive|negative
         */
        private animate(cell: Cell, visibility: string, animation_type: string, direction: string): void
        {
            var animation: any;

            if (animation_type === Stack.ANI_SLIDE_DIRECTION_HORIZONTAL)
            {
                animation_type = direction === 'positive' ? Stack.ANI_SLIDE_LEFT : Stack.ANI_SLIDE_RIGHT;
            }
            else if (animation_type === Stack.ANI_SLIDE_DIRECTION_VERTICAL)
            {
                animation_type = direction === 'positive' ? Stack.ANI_SLIDE_DOWN : Stack.ANI_SLIDE_UP;
            }

            switch (animation_type)
            {
                case Stack.ANI_FADE:
                    if (visibility === 'show')
                    {
                        animation = [
                            {
                                position: 'absolute',
                                display: 'block',
                                opacity: 0
                            },
                            {
                                opacity: 1
                            },
                            {
                                position: 'relative'
                            }
                        ];
                    }
                    else
                    {
                        animation = [
                            {
                                position: 'absolute'
                            },
                            {
                                opacity: 0
                            },
                            {
                                display: 'none',
                                opacity: 1
                                position: 'relative'
                            }
                        ];
                    }
                    break;

                case Stack.ANI_SLIDE_LEFT:
                case Stack.ANI_SLIDE_RIGHT:

                    var left: [number, number] = [this.element.outerWidth() + 20]
                    if (animation_type === Stack.ANI_SLIDE_LEFT)
                    {
                        left[1] = -(left[0])
                    }
                    else
                    {
                        left[1] = left[0];
                        left[0] = -(left[0]);
                    }

                    if (visibility === 'show')
                    {
                        animation = [
                            {
                                position: 'absolute',
                                width: this.element.width() ? this.element.width() : 'auto',
                                display: 'block',
                                opacity: 0
                                left: left[0]
                            },
                            {
                                left: 0,
                                opacity: 1
                            },
                            {
                                position: 'relative'
                            }
                        ];
                    }
                    else
                    {
                        animation = [
                            {
                                position: 'absolute'
                            },
                            {
                                left: left[1]
                                opacity: 0
                            },
                            {
                                position: 'relative'
                                display: 'none',
                                opacity: 1
                                left: 0
                            }
                        ];
                    }
                    break;

                case Stack.ANI_SLIDE_UP:
                case Stack.ANI_SLIDE_DOWN:

                    var top: [number, number] = [this.element.outerHeight() + 20]
                    if (animation_type === Stack.ANI_SLIDE_UP)
                    {
                        top[1] = -(top[0])
                    }
                    else
                    {
                        top[1] = top[0];
                        top[0] = -(top[0]);
                    }

                    if (visibility === 'show')
                    {
                        animation = [
                            {
                                position: 'absolute',
                                width: this.element.width() ? this.element.width() : 'auto',
                                display: 'block',
                                opacity: 0
                                top: top[0]
                            },
                            {
                                top: 0,
                                opacity: 1
                            },
                            {
                                position: 'relative'
                            }
                        ];
                    }
                    else
                    {
                        animation = [
                            {
                                position: 'absolute'
                            },
                            {
                                top: top[1]
                                opacity: 0
                            },
                            {
                                position: 'relative'
                                display: 'none',
                                opacity: 1
                                top: 0
                            }
                        ];
                    }
                    break;
            }

            // Preform the actual animation...
            cell.element.css(animation[0]);
            cell.element.animate(animation[1], {
                always: function() {
                    cell.element.css(animation[2]);
                }
            });
        }
    }
}
