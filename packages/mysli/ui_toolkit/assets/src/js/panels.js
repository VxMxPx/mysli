;(function ($, MU) {

    'use strict';

    // parent : string  selector of element which will contain all panels
    var Panels = function (parent) {
        var _this = this;
        this.parent = $(parent);
        this.sumSize = 0;
        this.fillCount = 0;
        this.activeId = false;
        this.offseted = false;
        this.panelsStack = new MU.Aarray();
        this.innerWidth = false;

        this.parent.on('click', 'div.panel.multi', function () {
            _this.activate(this.id, true);
        });
    };

    Panels.prototype = {

        constructor : Panels,

        // refresh view - usually when window is resized or panel is added / removed.
        // resized : boolean  was it tirggered by window resized
        refreshView : function (resized) {

            if (resized || this.innerWidth === false) {
                this.innerWidth = this.parent.width();
            }

            var overflow        = this.innerWidth - this.sumSize,
                overflowPart    = Math.ceil(overflow / this.fillCount),
                activePanel     = this.panelsStack.get(this.activeId),
                screenLeft      = this.innerWidth - activePanel.width(),
                overflowPercent = 100 - MU.Calc.getPercent(screenLeft, this.sumSize - activePanel.width()),
                offsetSoFar     = 0,
                panelCalculated = 0;

            // console.log(overflow);

            if (overflowPart <= 0) {
                overflowPart = overflow;
            }

            if (overflow > 0) {
                overflowPercent = 0;
                this.offseted = false;
            } else {
                this.offseted = true;
            }

            this.panelsStack.each(function (index, panel) {
                if (activePanel.size() === 'full') {
                    if (panel.size() !== 'full') {
                        offsetSoFar += panel.width();
                        panel.offset(offsetSoFar * -1);
                    } else {
                        panel.offset(offsetSoFar * -1);
                    }
                    return;
                }
                if (panel.expand()) {
                    if (overflow > 0) {
                        panel.offset(offsetSoFar * -1);
                        panel.expandFor(overflowPart);
                        panel.animate();
                        offsetSoFar += overflowPart * -1;
                        return;
                    } else {
                        panel.expandFor(0);
                        panel.animate();
                    }
                }
                panelCalculated = Math.ceil(MU.Calc.setPercent(overflowPercent, panel.width()));
                if (panel.focus()) {
                    panel.expandFor(0);
                    panel.offset(offsetSoFar * -1);
                    panel.animate();
                } else {
                    // Is shrinkable and still can be shrinked
                    if (panel.shrink() && panel.width() + panel._s.expandedFor > panel.shrink()) {
                        // Can whole offset be shrinked?
                        if (panel.shrink() > panel.width() - panelCalculated) {
                            var diff = panelCalculated - (panel.width() - panel.shrink());
                            panel.expandFor(diff * -1);
                            panel.offset((panelCalculated - diff + (offsetSoFar)) * -1);
                            panel.animate();
                            offsetSoFar += panelCalculated;
                            return;
                        } else {
                            panel.expandFor(panelCalculated * -1);
                            panel.offset(offsetSoFar * -1);
                            panel.animate();
                            offsetSoFar += panelCalculated;
                            return;
                        }
                    }
                    panel.expandFor(0);
                    panel.offset((panelCalculated + offsetSoFar) * -1);
                    panel.animate();
                    offsetSoFar += panelCalculated;
                }
            });
        },

        // Add new panel, if you provide after_id the panel will be added
        // to the right side of that one.
        // Otherwise, the panel will be added to the end (to the very right).
        // options  : object  panel's options
        // after_id : string  optional, panel will be insrted after _id_
        add : function (options, after_id) {

            var panel = new MU.Panel(options),
                beforeSize = 0,
                stackIndex = 0;

            if (this.panelsStack.get(panel.id())) {
                throw new Error('Duplicated ID: ' + panel.id());
            }

            if (after_id) {
                beforeSize = this.panelsStack.get(id).width();
                this.panelsStack.eachBefore(id, function (index, panelInside) {
                    panelInside.zIndex(10000 - index);
                    beforeSize += panelInside.width();
                });
            } else {
                beforeSize = this.sumSize;
            }

            panel.position(beforeSize);
            this.sumSize += panel.width();

            if (after_id) {
                this.panelsStack.get(after_id).zIndex(10000 - this.panelsStack.getIndex(after_id));
                stackIndex = this.panelsStack.pushAfter(after_id, panel.id(), panel);
            } else {
                stackIndex = this.panelsStack.push(panel.id(), panel);
            }

            panel.zIndex(10000 - (panel.size() === 'full' ? stackIndex * -1 : stackIndex));
            // Check weather panel is full screen (width = 0)
            if (!this.activeId || !this.panelsStack.get(this.activeId).width() || panel.width()) {
                this.activate(panel.id(), false);
            }

            if (panel.expand()) {
                this.fillCount++;
            }

            if (after_id) {
                if (panel.id() !== this.panelsStack.getLast().id()) {
                    this.panelsStack.eachAfter(panel.id(), function (index, panelInside) {
                        panelInside.zIndex(10000 - index);
                        panelInside.position(panelInside.position() + panel.width());
                        panelInside.animate();
                    });
                }
            }

            this.refreshView();

            panel.element.css({
                opacity: 0,
                left   : (panel.position() + panel.offset()) - (panel.width() + panel._s.expandedFor)
            });
            panel.append(this.parent);
            panel.animate();

            return panel;
        },

        // Remove particular panel.
        // id        : string
        // focusMove : boolean  foucs last panel in the stack
        remove : function (id, focusMove) {
            if (!this.panelsStack.get(id)) {
                return;
            }

            var panel = this.panelsStack.get(id),
                width = panel.width(),
                that  = this,
                focusTo = false;

            if (panel.locked()) {
                return;
            }

            if (focusMove === undefined) {
                focusMove = true;
            }

            if (id === this.activeId) {
                this.activeId = false;
            }


            // Remove all Dependant children
            if (panel.getChildren().length) {
                for (var i = panel.getChildren().length - 1; i >= 0; i--) {
                    this.remove(panel.getChildren()[i].id(), false);
                }
            }

            if (panel.expand()) {
                this.fillCount--;
            }

            focusTo = this.panelsStack.getFrom(id, -1);
            this.sumSize -= width;
            panel.remove();

            this.panelsStack.eachAfter(id, function (index, panelInside) {
                if (that.offseted) {
                    panelInside.position((panelInside.position() - width));
                } else {
                    panelInside.position(panelInside.position() - width);
                    panelInside.animate();
                }
            });

            this.panelsStack.remove(panel.id());

            // Are there any panels left?
            focusTo = focusTo || this.panelsStack.getLast();
            if (focusMove && typeof focusTo !== undefined && focusTo) {
                this.activate(focusTo.id(), false);
            }

            this.refreshView();
        },

        // Get panel by id
        // id : string
        get : function (id) {
            if (this.panelsStack.get(id)) {
                return this.panelsStack.get(id);
            }
        },

        // Activate particular panel
        // id      : string
        // refresh : boolean  weather to refresh panels positions
        activate : function (id, refresh) {
            //refresh = typeof refresh === 'undefined' ? false : true;

            if (id === this.activeId) { return true; }
            if (this.activeId) {
                this.panelsStack.get(this.activeId).focus(false);
            }
            this.panelsStack.get(id).focus(true);
            this.activeId = id;

            if (refresh === true) {
                this.refreshView();
            }
        }
    };

    MU.Panels = Panels;

}(Zepto, MU));
