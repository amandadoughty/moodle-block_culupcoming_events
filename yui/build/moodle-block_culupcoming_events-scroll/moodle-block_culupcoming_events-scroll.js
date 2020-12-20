YUI.add('moodle-block_culupcoming_events-scroll', function (Y, NAME) {

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Scroll functionality.
 *
 * @package   block_culupcoming_events
 * @copyright 2013 onwards Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU Public License
 */

M.block_culupcoming_events = M.block_culupcoming_events || {};
M.block_culupcoming_events.scroll = {

    lookahead: 365,
    courseid: 0,
    limitnum: null,
    scroller: null,
    reloader: null,
    timer: null,

    init: function(params) {
        if (Y.one('.pages')) {
            Y.one('.pages').hide();
        }

        try {
            var doc = Y.one(Y.config.doc);
            var reloaddiv = Y.one('.block_culupcoming_events .reload');
            var block = Y.one('.block_culupcoming_events');
            var id = block.get('id');
            id = id.replace('inst', '');
            var h2 = Y.one('#instance-' + id + '-header');
            h2.append(reloaddiv);
            reloaddiv.setStyle('display', 'inline-block');
            doc.delegate('click', this.reloadblock, '.block_culupcoming_events_reload', this);
        } catch (e) {
        }

        this.scroller = Y.one('.block_culupcoming_events .culupcoming_events');
        this.scroller.on('scroll', this.filltobelowblock, this);
        this.lookahead = params.lookahead;
        this.courseid = params.courseid;
        this.limitnum = params.limitnum;
        // Refresh the feed every 5 mins.
        this.timer = Y.later(1000 * 60 * 5, this, this.simulateclick, [], true);
        this.filltobelowblock();

        Y.publish('culcourse-upcomingevents:reloadevents', {
            broadcast:2
        });
    },

    filltobelowblock: function() {
        var scrollHeight = this.scroller.get('scrollHeight');
        var scrollTop = this.scroller.get('scrollTop');
        var clientHeight = this.scroller.get('clientHeight');
        var lastid;
        var lastdate;

        if ((scrollHeight - (scrollTop + clientHeight)) < 10) {
            // Pause the automatic refresh.
            this.timer.cancel();
            var num = Y.all('.block_culupcoming_events .culupcoming_events li.item').size();
            if (num > 0) {
                var lastitem = Y.all('.block_culupcoming_events .culupcoming_events li.item').item(num - 1);
                lastid = lastitem.get('id').split('_')[0];
                lastdate = lastitem.get('id').split('_')[1];
            } else {
                lastid = 0;
                lastdate = 0;
            }
            this.addevents(num, lastid, lastdate);
            // Start the automatic refresh again now we have the correct last item.
            this.timer = Y.later(1000 * 60 * 5, this, this.simulateclick, [], true);
        }
    },

    reloadblock: function(e) {
        e.preventDefault();
        this.reloadevents(e);
    },

    addevents: function(num, lastid, lastdate) {
        // Disable the scroller until this completes.
        this.scroller.detach('scroll');
        Y.one('.block_culupcoming_events_reload').setStyle('display', 'none');
        Y.one('.block_culupcoming_events_loading').setStyle('display', 'inline-block');

        var params = {
            sesskey : M.cfg.sesskey,
            lookahead: this.lookahead,
            courseid: this.courseid,
            lastid : lastid,
            limitnum: this.limitnum
        };

        Y.io(M.cfg.wwwroot + '/blocks/culupcoming_events/scroll_ajax.php', {
            method: 'POST',
            data: window.build_querystring(params),
            context: this,
            on: {
                success: function(id, e) {
                    var data = Y.JSON.parse(e.responseText);
                    if (data.error) {
                        this.timer.cancel();
                    } else {
                        if (data.output) {
                            Y.one('.block_culupcoming_events .events').append(data.output);
                        }
                    }
                    // Renable the scroller if there are more events.
                    if (!data.end) {
                        this.scroller.on('scroll', this.filltobelowblock, this);
                    }
                    Y.one('.block_culupcoming_events_loading').setStyle('display', 'none');
                    Y.one('.block_culupcoming_events_reload').setStyle('display', 'inline-block');
                },
                failure: function() {
                    // Error message.
                    Y.one('.block_culupcoming_events_loading').setStyle('display', 'none');
                    Y.one('.block_culupcoming_events_reload').setStyle('display', 'inline-block');
                    this.timer.cancel();
                }
            }
        });
    },

    reloadevents: function() {
        var lastid = 0;
        var count = Y.all('.block_culupcoming_events .culupcoming_events li.item').size();

        if (count) {
            lastid = this.scroller.all('li.item').item(count - 1).get('id').split('_')[0];
        }

        Y.one('.block_culupcoming_events_reload').setStyle('display', 'none');
        Y.one('.block_culupcoming_events_loading').setStyle('display', 'inline-block');

        var params = {
            sesskey : M.cfg.sesskey,
            lookahead: this.lookahead,
            courseid: this.courseid,
            limitnum : this.limitnum
        };

        Y.io(M.cfg.wwwroot + '/blocks/culupcoming_events/reload_ajax.php', {
            method: 'POST',
            data: window.build_querystring(params),
            context: this,
            on: {
                success: function(id, e) {
                    var data = Y.JSON.parse(e.responseText);

                    if (data.error) {
                        this.timer.cancel();
                    } else {
                        if (data.output) {
                            var eventlist = Y.one('.block_culupcoming_events .culupcoming_events .events');
                            eventlist.setHTML(data.output);
                        }
                    }

                    Y.one('.block_culupcoming_events_loading').setStyle('display', 'none');
                    Y.one('.block_culupcoming_events_reload').setStyle('display', 'inline-block');
                },
                failure: function() {
                    // Error message.
                    Y.one('.block_culupcoming_events_loading').setStyle('display', 'none');
                    Y.one('.block_culupcoming_events_reload').setStyle('display', 'inline-block');
                    this.timer.cancel();
                },
                end: function() {
                    Y.fire('culcourse-upcomingevents:reloadevents', {
                    });
                }
            }
        });
    },
    simulateclick: function() {
        Y.one('.block_culupcoming_events_reload').simulate('click');
    }
};

}, '@VERSION@', {
    "requires": [
        "base",
        "node",
        "io",
        "json-parse",
        "dom-core",
        "querystring",
        "event-custom",
        "moodle-core-dock",
        "node-event-simulate"
    ]
});
