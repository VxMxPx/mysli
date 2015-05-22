/// <reference path="widget.ts" />
/// <reference path="box.ts" />
/// <reference path="button.ts" />
/// <reference path="stack.ts" />
/// <reference path="_inc.common.ts" />

module mysli.js.ui
{
    export class Tabbar extends Widget
    {
        protected container: Box;

        constructor(options: any = {})
        {
            super(options);

            // Construct container
            options.orientation = ui.Box.VERTICAL;
            this.container = new Box(options);

            // Register events...
            this.events = common.mix({
                // Respond to a tabbar action (tab click)
                // => ( id: string, event: any, widget: Tabbar)
                action: {}
            });

            // Set defaults
            this.prop.def({
                // Which tab is active at the moment
                active: null,
                // Stack, to which tab will be connected...
                stack: null
            });
            this.prop.push(options, ['stack']);

            // Set proper element and class
            this.$element = this.container.element;
            this.element.addClass('ui-tabbar');

            // Push options if any
            if (typeof options.add !== 'undefined')
            {
                this.add(options.add);
            }
        }

        // Get/set stack
        get stack(): Stack
        {
            return this.prop.stack;
        }
        set stack(stack: Stack)
        {
            this.prop.stack = stack;
            this.container.each(function(_: number, widget: Widget): any
            {
                if (!stack.has(widget.uid))
                {
                    stack.push(new ui.Container(), widget.uid);
                }
            });

            if (this.active)
            {
                this.stack.to(this.active);
            }
        }

        // Get/set active tab
        get active(): string
        {
            return this.prop.active;
        }
        set active(value: string)
        {
            if (this.container.has(value))
            {
                if (this.prop.active)
                {
                    (<Button>this.container.get(this.prop.active)).pressed = false;
                }

                this.prop.active = value;
                (<Button>this.container.get(value)).pressed = true;
            }
        }

        /**
         * Add items to the container
         * @param items in format: {id: Label, id: Label}
         */
        add(items: any)
        {
            for (var item in items)
            {
                if (items.hasOwnProperty(item))
                {
                    if (this.stack)
                    {
                        if (!this.stack.has(item))
                        {
                            this.stack.push(new ui.Container(), item);
                        }
                    }
                    this.container.push(this.produce(items[item], item), item);
                }
            }
        }

        /**
         * Produce a new tab element.
         * @param title
         * @param id
         */
        private produce(title: string, id: string): Widget
        {
            var button: Button = new Button({
                uid: id,
                toggle: true,
                label: title,
                flat: true,
                style: this.style
            });

            if (this.prop.active === id)
            {
                button.pressed = true;
                if (this.stack)
                {
                    this.stack.to(id);
                }
            }

            button.connect('click', (e) => {
                if (this.stack)
                {
                    this.stack.to(button.uid);
                }
                this.active = button.uid;
                this.trigger('action', [id, e]);
            });

            return button;
        }

    }
}
