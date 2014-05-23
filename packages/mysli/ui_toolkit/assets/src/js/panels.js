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
            _this.focus(this.id, true);
        });
        this.parent.on('self/close', 'div.panel.multi', function () {
           _this.remove(this.id);
        });
        $(document).on('MU/panels/refresh', function () {
            _this.refreshView();
        });
        $(document).on('MU/panels/updateSum', function (e, value) {
            _this.updateSum(value);
        });
        $(document).on('MU/panels/focusNext', function (e, lastId) {
            _this.focusNext(lastId);
            _this.refreshView();
        });
    };

    Panels.prototype = {

        constructor : Panels,

        // update sum size, when panel is added, remove or away
        // value : integer  positive or negative
        updateSum : function (value) {
            this.sumSize = this.sumSize + value;
        },

        // refresh view - usually when window is resized or panel is added / removed.
        // resized : boolean  was it tirggered by window resized
        refreshView : function (resized) {

            if (resized || this.innerWidth === false) {
                this.innerWidth = this.parent.width();
            }

            // no active id, nothing to do
            if (!this.activeId) { return; }

            var overflow        = this.innerWidth - this.sumSize,
                overflowPart    = this.fillCount > 0 ? Math.ceil(overflow / this.fillCount) : 0,
                activePanel     = this.panelsStack.get(this.activeId),
                screenLeft      = this.innerWidth - activePanel.width(),
                overflowPercent = 100 - MU.Calc.getPercent(screenLeft, this.sumSize - activePanel.width()),
                offsetSoFar     = 0,
                panelCalculated = 0;

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
                if (panel.away() && !panel.focus()) {
                    panel.expandFor(0);
                    panel.offset((panel.width() - panel._s.awayWidth + offsetSoFar) * -1);
                    panel.animate();
                    offsetSoFar += panel.width() - panel._s.awayWidth;
                    return;
                }
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
                if (panel.focus()) {
                    panel.expandFor(0);
                    panel.offset(offsetSoFar * -1);
                    panel.animate();
                    return;
                }
                // panelCalculated = Math.ceil(MU.Calc.setPercent(overflowPercent, panel.width()))
                panelCalculated = MU.Calc.setPercent(overflowPercent, panel.width());

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
            });
        },

        // Add new panel, if you provide after_id the panel will be added
        // to the right side of that one.
        // Otherwise, the panel will be added to the end (to the very right).
        // options  : object  panel's options
        // after_id : string  optional, panel will be insrted after _id_
        add : function (options, after_id) {

            var panel = new MU.Panel(options),
                _this = this,
                beforeSize = 0,
                stackIndex = 0;

            if (this.panelsStack.get(panel.id())) {
                throw new Error('Duplicated ID: ' + panel.id());
            }

            if (after_id) {
                beforeSize = this.panelsStack.get(after_id).width();
                this.panelsStack.eachBefore(after_id, function (index, panelInside) {
                    panelInside.zIndex(10000 - index);
                    beforeSize += panelInside.width();
                });
            } else {
                beforeSize = this.sumSize;
            }

            panel.position(beforeSize);
            this.updateSum(panel.width());

            if (after_id) {
                this.panelsStack.get(after_id).zIndex(10000 - this.panelsStack.getIndex(after_id));
                stackIndex = this.panelsStack.pushAfter(after_id, panel.id(), panel);
            } else {
                stackIndex = this.panelsStack.push(panel.id(), panel);
            }

            panel.zIndex(10000 - (panel.size() === 'full' ? stackIndex * -1 : stackIndex));
            // Check weather panel is full screen (width = 0)
            if (!this.activeId || !this.panelsStack.get(this.activeId).width() || panel.width()) {
                this.focus(panel.id(), false);
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
            this.parent.append(panel.element);
            panel.animate();

            return panel;
        },

        // Remove particular panel.
        // id        : string
        remove : function (id) {
            // inavlid id
            if (!this.panelsStack.get(id)) { return; }

            var panel = this.panelsStack.get(id),
                width = panel.width(),
                that  = this,
                focusTo = false;

            // panel is loced (perhaps is preforming some taks, cannot close)
            if (panel.locked()) { return; }

            // Cannot take focus anymore
            panel.insensitive(true);

            if (id === this.activeId) { this.activeId = false; }

            // Remove all Dependant children
            if (panel.getChildren().length) {
                for (var i = panel.getChildren().length - 1; i >= 0; i--) {
                    this.remove(panel.getChildren()[i].id(), false);
                }
            }

            if (panel.expand()) {
                this.fillCount--;
            }

            this.updateSum(width * -1);

            panel.element.animate({
                left    : (panel._s.position + panel._s.offset) - (width + panel._s.expandedFor) - 10,
                opacity : 0
            }, 'normal', function () {
                panel.element.remove();
            });

            this.panelsStack.eachAfter(id, function (index, panelInside) {
                if (that.offseted) {
                    panelInside.position((panelInside.position() - width));
                } else {
                    panelInside.position(panelInside.position() - width);
                    panelInside.animate();
                }
            });

            this.focusNext(id);
            this.panelsStack.remove(id);

            this.refreshView();
        },

        // Get panel by id
        // id : string
        get : function (id) {
            if (this.panelsStack.get(id)) {
                return this.panelsStack.get(id);
            }
        },

        // wehn panel is closed or set to be sensitive, this will find next
        // target and focus it.
        // lastId : string
        focusNext : function (lastId) {
            // Are there any panels left?
            var focusTo = this.panelsStack.getFrom(lastId, -1);
            if (!focusTo || focusTo.insensitive()) {
                focusTo = this.panelsStack.each(function (id, panel) {
                    if (!panel.insensitive()) {
                        return panel;
                    }
                });
                if (!focusTo) { return; } // nothing we can do...
            }
            this.focus(focusTo.id(), false);
        },

        // blur current, and focus different panel
        // id      : string
        // refresh : boolean  weather to refresh panels positions
        focus : function (id, refresh) {
            //refresh = typeof refresh === 'undefined' ? false : true;
            if (id === this.activeId) { return true; }

            var panel = this.panelsStack.get(id);

            if (panel.insensitive()) { return; }

            if (this.activeId) {
                this.panelsStack.get(this.activeId).focus(false);
            }

            panel.focus(true);
            this.activeId = id;

            if (refresh === true) {
                this.refreshView();
            }
        }
    };

    MU.Panels = Panels;

}(Zepto, MU));
