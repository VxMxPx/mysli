var __extends = this.__extends || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    __.prototype = b.prototype;
    d.prototype = new __();
};
/// <reference path="widget.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var ui;
        (function (ui) {
            var Overlay = (function (_super) {
                __extends(Overlay, _super);
                function Overlay(options) {
                    _super.call(this, options);
                    this.prop = js.common.mix({
                        busy: false,
                        visible: false
                    }, this.prop);
                    js.common.use(this.prop, this, {
                        busy: 'busy',
                        visible: 'visible'
                    });
                }
                /**
                 * Get/Set busy state.
                 * @param  {boolean} status
                 * @return {boolean}
                 */
                Overlay.prototype.busy = function (status) {
                    if (typeof status !== 'undefined') {
                        this.prop.busy = status;
                        if (status) {
                            this.element.addClass('status-busy');
                        }
                        else {
                            this.element.removeClass('status-busy');
                        }
                    }
                    return this.prop.busy;
                };
                /**
                 * Get/Set visibility state.
                 * @param  {boolean} status
                 * @return {boolean}
                 */
                Overlay.prototype.visible = function (status) {
                    if (typeof status !== 'undefined') {
                        this.prop.visble = status;
                        if (status) {
                            this.element.fadeIn();
                        }
                        else {
                            this.element.fadeOut(400);
                        }
                    }
                    return this.element.is(':visible');
                };
                Overlay.template = '<div class="ui-overlay ui-widget"><div class="ui-overlay-busy"><i class="fa fa-cog fa-spin"></i></div></div>';
                return Overlay;
            })(ui.Widget);
            ui.Overlay = Overlay;
        })(ui = js.ui || (js.ui = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
