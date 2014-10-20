;(function ($, MU) {

    'use strict';

    // calculate placement of an target
    // target : object  mouseEvent|domElement|object{top,left}
    // return : object  {top: [max,min,mid], left: [max,min,mid]}
    function calculateTargetPosition(target) {

        var result = {top : {}, left: {}};

        // mouse cursor
        if (typeof target.pageX !== 'undefined') {
            result = {
                top  : {min : target.pageY, max : target.pageY},
                left : {min : target.pageX, max : target.pageX}
            };
        }

        // element
        else if (typeof target.offset === 'function') {
            result = {
                top  : {
                    min : target.offset().top,
                    max : target.offset().top + target.height()
                },
                left : {
                    min : target.offset().left,
                    max : target.offset().left + target.width()
                }
            };
        }

        // fixed numeric value
        else if (typeof target.top === 'number') {
            result = {
                top  : { min : target.top, max : target.top},
                left : { min : target.left, max : target.left}
            };
        }

        // fixed numeric, exact
        else if (typeof target.top === 'object') {
            result = target;
        }

        // none of the above
        else {
            throw new Error('Invalid target!');
        }

        // push middle
        result.top.mid = result.top.min   + ((result.top.max - result.top.min) / 2);
        result.left.mid = result.left.min + ((result.left.max - result.left.min) / 2);

        return result;
    }

    // result               : object  to be modified
    // where                : string  top|left
    // targetPosition       : integer
    // elementDimension     : integer
    // elementDimensionHalf : integer
    // parentDimension      : integer
    // spacing              : integer  spacing from parent's borders
    function getMidPosition(result, where, targetPosition, elementDimension, elementDimensionHalf, parentDimension, spacing) {

        var diff = {min : 0, max : 0}.
            result = {};

        diff.min = targetPosition - elementDimensionHalf;
        diff.max = parentDimension - (targetPosition + elementDimensionHalf);

        if (-(diff.min) > (elementDimensionHalf - 8) || -(diff.max) > (elementDimensionHalf - 8)) {
            return false;
        }

        if (diff.min >= spacing) {
            if (diff.max <= spacing) {
                result.pointerDiff = -(diff.max);
                result[where] = parentDimension - spacing - elementDimension;
            } else {
                result[where] = diff.min;
            }
            return true;
        }

        if ((targetPosition + elementDimensionHalf - diff.min) < parentDimension) {
            result[where] = spacing;
            result.pointerDiff = diff.min;
            return true;
        }
    }

    // placementOrder   : array   top,left,right,bottom
    // targetPosition   : object  {top: [max,min,mid], left: [max,min,mid]}
    // parentDimension  : object  {width, height}
    // elementDimension : object  {width, height}
    function getElementPlacement(placementOrder, targetPosition, parentDimension, elementDimension) {

        var result = {pointerDiff : 0},
            targetSpacing = 10;

        for (var i = 0, len = placementOrder.length; i < len; i++) {
            switch (placementOrder[i]) {
                case 'top':
                    result.position = 'down';
                    if ((targetPosition.top.min - elementDimension.height - (targetSpacing * 2)) >= 0) {
                        result.top = targetPosition.top.min - elementDimension.height - targetSpacing;
                        if (getMidPosition(
                            result,
                            'left',
                            targetPosition.left.mid,
                            elementDimension.width,
                            Math.round(elementDimension.width / 2),
                            parentDimension.width,
                            targetSpacing)
                        ) {
                            return result;
                        }
                    }
                    continue;

                case 'bottom':
                    result.position = 'up';
                    if ((targetPosition.top.max + elementDimension.height + (targetSpacing * 2)) < parentDimension.height) {
                        result.top = targetPosition.top.max + targetSpacing;
                        if (getMidPosition(
                            result,
                            'left',
                            targetPosition.left.mid,
                            elementDimension.width,
                            Math.round(elementDimension.width / 2),
                            parentDimension.width,
                            targetSpacing)
                        ) {
                            return result;
                        }
                    }
                    continue;

                case 'left':
                    result.position = 'right';
                    if ((targetPosition.left.min - elementDimension.width - (targetSpacing * 2)) >= 0) {
                        result.left = targetPosition.left.min - elementDimension.width - targetSpacing;
                        if (getMidPosition(
                            result,
                            'top',
                            targetPosition.top.mid,
                            elementDimension.height,
                            Math.round(elementDimension.height / 2),
                            parentDimension.height,
                            targetSpacing)
                        ) {
                            return result;
                        }
                    }
                    continue;

                case 'right':
                    result.position = 'left';
                    if ((targetPosition.left.max + elementDimension.width + (targetSpacing * 2)) < parentDimension.width) {
                        result.left = targetPosition.left.max + targetSpacing;
                        if (getMidPosition(
                            result,
                            'top',
                            targetPosition.top.mid,
                            elementDimension.height,
                            Math.round(elementDimension.height / 2),
                            parentDimension.height,
                            targetSpacing)
                        ) {
                            return result;
                        }
                    }
                    continue;
            }
        }
    }

    // options : object
    //   spaced       : boolean  default space between border and content
    //   position     : mixed    array|string top|bottom|left|right - preferred placement
    //   content      : string   html
    //   pointerSpace : integer  how far pointer should be shifter to be in
    //                           center, this is usually negative value,
    //                           half size of pointer's width
    //   visible      : boolean  weather popup is visible when created
    //   trigger      : mixed    either object which will show pupup on click,
    //                           or null, if you want mouse click to be a trigger
    //   event        : string   mouseover|click which event will display popup
    //   delay        : integer  mostly used for when event is `mouseover`
    //   selfClose    : boolean  click on popup will close it
    //   sticky       : boolean  if true, click outside popup won't close it
    var Popup = function (options) {

        options = options || {};

        if (typeof options.position === 'string') {
            options.position = [options.position, 'bottom', 'top', 'left', 'right'];
        }

        this.properties = $.extend({}, {
            spaced         : true,
            position       : ['bottom', 'top', 'left', 'right'],
            content        : null,
            pointerSpace   : -8,
            visible        : false,
            style          : null,
            trigger        : null,
            event          : 'click',
            toggle         : true,
            delay          : 0,
            selfClose      : true,
            sticky         : false
        }, options);

        var delayTimer = null;

        this.element = $('<div class="popup point" style="display:none;"><div class="pointer" /><div class="contents" /></div>');
        this.element.appendTo('body');

        if (this.properties.content) {
            this.content(this.properties.content);
        }

        if (this.properties.style) {
            this.style(this.properties.style);
        }

        this.spaced(this.properties.spaced);

        if (this.properties.visible) {
            this.show();
        }

        // register events
        if (this.properties.selfClose) {
            this.element.on('click', this.hide.bind(this));
        }
        else {
            this.element.on('click', function (e) {
                e.stopPropagation();
            });
        }
        if (!this.sticky && this.properties.trigger) {
            $(document).on('click', function () {
                if (this.properties.visible) {
                    this.hide();
                }
            }.bind(this));
        }
        if (this.properties.trigger && this.properties.event) {
            if (this.properties.event === 'click') {
                this.properties.trigger.on('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (this.properties.visible) {
                        if (this.properties.toggle) {
                            this.hide();
                        }
                        return;
                    }
                    if (this.properties.delay) {
                        clearTimeout(delayTimer);
                        delayTimer = setTimeout(function () {
                            this.show();
                        }.bind(this), this.properties.delay);
                    } else {
                        this.show();
                    }
                }.bind(this));
            }
            else if (this.properties.event === 'mouseover') {
                this.properties.trigger.on('mouseover', function () {
                    if (this.properties.visible) { return; }
                    if (this.properties.delay) {
                        clearTimeout(delayTimer);
                        delayTimer = setTimeout(function () {
                            this.show();
                        }.bind(this), this.properties.delay);
                    } else {
                        this.show();
                    }
                }.bind(this));
                if (this.properties.toggle) {
                    this.properties.trigger.on('mouseout', function () {
                        if (this.properties.delay) {
                            clearTimeout(delayTimer);
                            delayTimer = setTimeout(function () {
                                this.hide();
                            }.bind(this), this.properties.delay);
                        } else {
                            this.hide();
                        }
                    }.bind(this));
                }
            }
            else {
                throw new Error('Unsupported event. Allowed are: "click" and "mouseover".');
            }
        }
    };

    Popup.prototype = {

        constructor : Popup,

        updatePosition : function (targetPosition) {
            var elementDimension = {
                width  : this.element.width(),
                height : this.element.height()
            },
            windowDimension = {
                width  : $(window).width(),
                height : $(window).height() + $(window).scrollTop()
            },
            elementPlacement,
            pointerPosition;

            elementPlacement = getElementPlacement(
                this.properties.position,
                calculateTargetPosition(targetPosition),
                windowDimension,
                elementDimension
            );

            if (!elementPlacement) {
                return;
            }

            // finally position element accordingly.
            this.element.css({
                top: elementPlacement.top,
                left: elementPlacement.left
            });
            this.element.removeClass('up down left right');
            this.element.addClass(elementPlacement.position);

            if (elementPlacement.position === 'left' || elementPlacement.position === 'right') {
                pointerPosition = {
                    marginTop : (elementPlacement.pointerDiff + this.properties.pointerSpace) + 'px',
                    marginLeft : '0px'
                };
            } else {
                pointerPosition = {
                    marginTop : '0px',
                    marginLeft : (elementPlacement.pointerDiff + this.properties.pointerSpace) + 'px'
                };
            }

            this.element.find('.pointer').css(pointerPosition);
        },

        show : function (targetPosition) {
            // if we have set global trigger, then we'll ignore this one
            if (this.properties.trigger) {
                targetPosition = this.properties.trigger;
            }
            // no dimension on invisible element
            this.element.fadeIn(200);
            this.updatePosition(targetPosition);
            this.properties.visible = true;
        },

        hide : function () {
            this.element.fadeOut(200);
            this.properties.visible = false;
        },

        toggle : function (e) {
            this[this.properties.visible ? 'hide' : 'show'](e);
        },

        content : function (value) {
            this.element.find('.contents').html(value);
        },

        spaced : function (value) {
            this.element[value ? 'addClass' : 'removeClass']('spaced');
        },

        // set/get style
        // variant: string (alt, default)
        // return : string
        style : function (variant) {
            var classes = ['alt'];

            // Get style
            if (typeof variant === 'undefined') {
                for (var i = classes.length - 1; i >= 0; i--) {
                    if (this.element.hasClass(classes[i])) return classes[i];
                }
                return 'default';
            }

            // Set style
            this.element.removeClass(classes.join(' '));

            if (variant !== 'default') {
                this.element.addClass(variant);
            }
        },
    };

    MU.Popup = Popup;

}(Zepto, Mysli.UI));
