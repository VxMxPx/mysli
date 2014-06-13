;(function ($, MU) {

    'use strict';

    // container : string  selector of element which will contain all panels
    var Panels = function (container) {

        // panel's properties
        var _this = this,
            resizeTimer = null;

        // panels container's properties
        this.properties = {
            // sum of all panels (widths, px)
            sumSize : 0,
            // number of panels which are expanded
            fillCount : 0,
            // currently selected panel (id)
            activeId : false,
            // weather panels are offseted (overflown)
            offseted : 0,
            // width of container
            containerWidth : 0
        };

        // container of all panels (dom)
        this.container = $(container);
        // array of all panels (objects)
        this.panelsStack = new MU.Aarray();

        this.updateContainerWidth();

        // register events
        this.container.on('click', 'div.panel.multi', function () {
            _this.focus(this.id, true);
        });
        this.container.on('self/close', 'div.panel.multi', function () {
           _this.remove(this.id);
        });
        $(window).on('resize', function () {
            if (resizeTimer) { clearTimeout(resizeTimer); }
            resizeTimer = setTimeout(function () {
                _this.updateContainerWidth();
                _this.refreshView();
            }, 300);
        });
    };

    Panels.prototype = {

        constructor : Panels,

        // update container width
        updateContainerWidth : function () {
            this.properties.containerWidth = this.container.width();
        },

        // update sum size, when panel is added, remove or away
        // value          : integer  positive or negative
        // modifyBeforeId : string   if provided, panels before this id, will
        //                           update position to fit difference.
        updateSum : function (value, modifyBeforeId) {
            this.properties.sumSize = this.properties.sumSize + value;
            if (modifyBeforeId) {
                this.panelsStack.eachAfter(modifyBeforeId, function (index, panelInside) {
                    panelInside.zIndex(10000 - index);
                    panelInside.properties.position = panelInside.properties.position + value;
                    panelInside.animate();
                });
            }
        },

        // refresh view - when panel is added/removed or window is resized
        refreshView : function () {
            // no active id, nothing to do
            if (!this.properties.activeId) { return; }

            var overflow        = this.properties.containerWidth - this.properties.sumSize,
                overflowPart    = this.properties.fillCount > 0 ? Math.ceil(overflow / this.properties.fillCount) : 0,
                activePanel     = this.panelsStack.get(this.properties.activeId),
                screenLeft      = this.properties.containerWidth - activePanel.properties.width,
                overflowPercent = 100 - MU.Calc.getPercent(screenLeft, this.properties.sumSize - activePanel.properties.width),
                offsetSoFar     = 0,
                panelCalculated = 0;

            if (overflowPart <= 0) {
                overflowPart = overflow;
            }

            if (overflow > 0) {
                overflowPercent = 0;
                this.properties.offseted = false;
            } else {
                this.properties.offseted = true;
            }

            this.panelsStack.each(function (index, panel) {
                if (panel.properties.away && !panel.hasFocus()) {
                    panel.properties.expandFor = 0;
                    panel.properties.offset = -(panel.properties.width - panel.properties.awayWidth + offsetSoFar);
                    panel.animate();
                    offsetSoFar += panel.properties.width - panel.properties.awayWidth;
                    return;
                }
                if (panel.properties.expand) {
                    if (overflow > 0) {
                        panel.properties.offset = -(offsetSoFar);
                        panel.properties.expandFor = overflowPart;
                        panel.animate();
                        offsetSoFar += -(overflowPart);
                        return;
                    } else {
                        panel.properties.expandFor = 0;
                        panel.animate();
                    }
                }
                if (panel.hasFocus()) {
                    panel.properties.expandFor = 0;
                    panel.properties.offset = -(offsetSoFar);
                    panel.animate();
                    return;
                }
                // panelCalculated = Math.ceil(MU.Calc.setPercent(overflowPercent, panel.properties.width))
                panelCalculated = MU.Calc.setPercent(overflowPercent, panel.properties.width);

                // is shrinkable and still can be shrinked
                if (panel.properties.shrink && panel.properties.width + panel.properties.expandFor > panel.properties.shrink) {
                    // can whole offset be shrinked?
                    if (panel.properties.shrink > panel.properties.width - panelCalculated) {
                        var diff = panelCalculated - (panel.properties.width - panel.properties.shrink);
                        panel.properties.expandFor = -(diff);
                        panel.properties.offset = -(panelCalculated - diff + offsetSoFar);
                        panel.animate();
                        offsetSoFar += panelCalculated;
                        return;
                    } else {
                        panel.properties.expandFor = -(panelCalculated);
                        panel.properties.offset = -(offsetSoFar);
                        panel.animate();
                        offsetSoFar += panelCalculated;
                        return;
                    }
                }
                panel.properties.expandFor = 0;
                panel.properties.offset = -(panelCalculated + offsetSoFar);
                panel.animate();
                offsetSoFar += panelCalculated;
            });
        },

        // add a new panel
        // if you provide afterId the panel will be added to the right side of it
        // otherwise, the panel will be added to the end (to the very right)
        // options : object  panel's options
        // afterId : string  optional, panel will be insrted after _id_
        add : function (options, afterId) {

            if (typeof options !== 'object') {
                options = {};
            }

            var panel = new MU.Panel(this, options),
                beforeSize = 0,
                stackIndex = 0;

            if (this.panelsStack.get(panel.properties.id)) {
                throw new Error('Duplicated ID: ' + panel.properties.id);
            }

            if (afterId) {
                beforeSize = this.panelsStack.get(afterId).properties.width;
                this.panelsStack.eachBefore(afterId, function (index, panelInside) {
                    panelInside.zIndex(10000 - index);
                    beforeSize += panelInside.properties.width;
                });
            } else {
                beforeSize = this.properties.sumSize;
            }

            panel.properties.position = beforeSize;
            this.updateSum(panel.properties.width, afterId);

            if (afterId) {
                this.panelsStack.get(afterId).zIndex(10000 - this.panelsStack.getIndex(afterId));
                stackIndex = this.panelsStack.pushAfter(afterId, panel.properties.id, panel);
            } else {
                stackIndex = this.panelsStack.push(panel.properties.id, panel);
            }

            panel.zIndex(10000 - stackIndex);

            // check weather current panel is full screen - then don't move focus
            if (!this.properties.activeId || !this.panelsStack.get(this.properties.activeId).properties.full) {
                this.focus(panel.properties.id, false);
            }

            if (panel.properties.expand) {
                this.properties.fillCount++;
            }

            this.refreshView();

            panel.element.css({
                opacity: 0,
                left   : (panel.properties.position + panel.properties.offset) - (panel.properties.width + panel.properties.expandFor)
            });
            this.container.append(panel.element);
            panel.animate();

            return panel;
        },

        // Remove particular panel.
        // id : string
        remove : function (id) {
            // inavlid id
            if (!this.panelsStack.get(id)) { return; }

            var panel = this.panelsStack.get(id),
                width = panel.properties.width;

            // panel is loced (perhaps is preforming some taks, cannot close)
            if (panel.properties.locked) { return; }

            // cannot take focus anymore
            panel.properties.insensitive = true;
            panel.properties.closing = true;

            if (id === this.properties.activeId) { this.properties.activeId = false; }

            // remove all Dependant children
            if (panel.getChildren().length) {
                for (var i = panel.getChildren().length - 1; i >= 0; i--) {
                    this.remove(panel.getChildren()[i].properties.id, false);
                }
            }

            if (panel.properties.expand) {
                this.properties.fillCount--;
            }

            this.updateSum(-(width), id);

            panel.element.animate({
                left    : (panel.properties.position + panel.properties.offset) - (width + panel.properties.expandFor) - 10,
                opacity : 0
            }, 'normal', function () {
                panel.element.remove();
            });

            this.focusNext(id);
            this.panelsStack.remove(id);

            this.refreshView();
        },

        // get panel by id
        // id : string
        get : function (id) {
            if (this.panelsStack.get(id)) {
                return this.panelsStack.get(id);
            }
        },

        // wehn panel is closed or set to be sensitive,
        // this will find next target and focus it
        // lastId : string
        focusNext : function (lastId) {
            // are there any panels left?
            var focusTo = this.panelsStack.getFrom(lastId, -1);
            if (!focusTo || focusTo.properties.insensitive) {
                focusTo = this.panelsStack.each(function (id, panel) {
                    if (!panel.properties.insensitive) {
                        return panel;
                    }
                });
                if (!focusTo) { return; } // nothing we can do...
            }
            this.focus(focusTo.properties.id, false);
        },

        // blur current, and focus different panel
        // id      : string
        // refresh : boolean  weather to refresh panels positions
        focus : function (id, refresh) {
            //refresh = typeof refresh === 'undefined' ? false : true;
            if (id === this.properties.activeId) { return true; }

            var panel = this.panelsStack.get(id);

            if (panel.properties.insensitive) { return; }

            if (this.properties.activeId) {
                this.panelsStack.get(this.properties.activeId).setFocus(false);
            }

            panel.setFocus(true);
            this.properties.activeId = id;

            if (refresh === true) {
                this.refreshView();
            }
        }
    };

    MU.Panels = Panels;

}(Zepto, MU));
