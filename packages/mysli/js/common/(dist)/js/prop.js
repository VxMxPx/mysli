/// <reference path="common.ts" />
var mysli;
(function (mysli) {
    var js;
    (function (js) {
        var common;
        (function (common) {
            var Prop = (function () {
                function Prop(properties, context) {
                    this._context = context;
                    this.def(properties);
                }
                /**
                 * Get/set any property
                 */
                Prop.prototype.get = function (property) {
                    return this[property];
                };
                Prop.prototype.set = function (property, value) {
                    this[property] = value;
                };
                /**
                 * Add default properties.
                 * It will not append those values that are already set.
                 * @param properties
                 */
                Prop.prototype.def = function (properties) {
                    var property;
                    for (property in properties) {
                        if (!properties.hasOwnProperty(property)) {
                            continue;
                        }
                        if (typeof this[property] === 'undefined') {
                            this[property] = properties[property];
                        }
                        else {
                            console.warn('Setting a default property which is already there: ' + property);
                        }
                    }
                };
                /**
                 * Push and apply list of properties into the stack!
                 * Options had to be predefined with a .def method.
                 * If you provide `use` list, those will be set using setter.
                 * If any of use values has ! e.g. ['icon!', 'label'], the
                 * setter will be used, even if option the same as the
                 * one already set (by default for example).
                 * @param properties
                 * @param use
                 */
                Prop.prototype.push = function (properties, use) {
                    if (use === void 0) { use = []; }
                    var property;
                    var force;
                    // Check which options to set with setter
                    if (use.length) {
                        for (var i = 0; i < use.length; i++) {
                            property = use[i];
                            force = (property.substr(property.length - 1, 1) === '!');
                            if (force) {
                                property = property.substr(0, property.length - 1);
                                use[i] = property;
                            }
                            if (typeof this[property] !== 'undefined') {
                                if (typeof properties[property] !== 'undefined') {
                                    if (properties[property] !== this[property] || force) {
                                        this._context[property] = properties[property];
                                    }
                                }
                                else if (force) {
                                    this._context[property] = this[property];
                                }
                            }
                        }
                    }
                    // Run through the rest of the properties
                    for (property in properties) {
                        if (!properties.hasOwnProperty(property)) {
                            continue;
                        }
                        if (typeof this[property] !== 'undefined' && use.indexOf(property) === -1) {
                            if (this[property] && typeof this[property] === 'object' &&
                                properties[property] && typeof properties[property] === 'object') {
                                this[property] = common.mix(this[property], properties[property]);
                            }
                            else {
                                this[property] = properties[property];
                            }
                        }
                    }
                };
                /**
                 * Query settings, by path, eg: {icon: {name: 'foo'}} ... path='icon.name' => 'foo'
                 * @param path
                 * @param def
                 */
                Prop.prototype.q = function (path, def) {
                    var last = this;
                    var segments = path.split('.');
                    for (var i = 0; i < segments.length; i++) {
                        if (typeof last[segments[i]] === 'undefined') {
                            return def;
                        }
                        else {
                            last = last[segments[i]];
                        }
                    }
                    return last;
                };
                return Prop;
            })();
            common.Prop = Prop;
        })(common = js.common || (js.common = {}));
    })(js = mysli.js || (mysli.js = {}));
})(mysli || (mysli = {}));
