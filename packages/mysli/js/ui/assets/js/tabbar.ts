/// <reference path="widget.ts" />
/// <reference path="box.ts" />
/// <reference path="button.ts" />
/// <reference path="_inc.common.ts" />
module mysli.js.ui {
	export class Tabbar extends Widget {
		
		protected container: Box;
		
		constructor(items: any, options: any = {}) {
			super(options);
			
			this.prop.def({
				// Which tab is active at the moment
				active: null
			});
			this.prop.push(options);
			
			options.orientation = ui.Box.VERTICAL;
			this.container = new Box(options);
			this.$element = this.container.element;
			this.element.addClass('ui-tabbar');
			
			this.events = common.mix({
				// Respond to a tabbar action (tab click)
				// => ( id: string, event: any, widget: Tabbar)
				action: {}
			});
			
			for (var item in items) {
				if (items.hasOwnProperty(item)) {
					this.container.push(this.produce(items[item], item), item);
				}
			}
		}
		
		// Get/set active tab
		get active(): string {
			return this.prop.active;
		}
		set active(value: string) {
			if (this.container.has(value)) {
				if (this.prop.active) {
					(<Button>this.container.get(this.prop.active)).pressed = false;
				}
				this.prop.active = value;
				(<Button>this.container.get(value)).pressed = true;
			}
		}
		
		private produce(title: string, id: string): Widget {
			var button: Button = new Button({uid: id, toggle: true, label: title, flat: true, style: this.style});
			if (this.prop.active === id) {
				button.pressed = true;
			}
			button.connect('click', (e) => {
				this.active = button.uid;
				this.trigger('action', [id, e]);
			});
			return button;
		}
		
	}
}