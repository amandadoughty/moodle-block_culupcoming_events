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
 * @copyright 2014 onwards Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.block_culupcoming_events = M.block_culupcoming_events || {};
M.block_culupcoming_events.scroll = {

    limitnum: null,
    scroller: null,
    reloader: null,
    timer: null,

    init: function(params) {

        if (Y.one('.pages')) {
            Y.one('.pages').hide();
        }

        var reloaddiv = Y.one('.block_culupcoming_events .reload');
        var h2 = Y.one('.block_culupcoming_events .header .title h2');
        h2.append(reloaddiv);
        reloaddiv.setStyle('display', 'inline-block');
        Y.one('.reload .block_culupcoming_events_reload').on('click', this.reloadblock, this);
        this.scroller = Y.one('.block_culupcoming_events .culupcoming_events');
        this.scroller.on('scroll', this.filltobelowblock, this);
        this.limitnum = params.limitnum;
        this.courseid = params.courseid;
        // Refresh the feed every 5 mins.
        this.timer = Y.later(1000 * 60 * 5, this, this.reloadevents, [], true);
        this.filltobelowblock();
        // When the block is docked. the reload link is created on the fly as the block
        // is shown. This means that the click event is not attached. Here we listen for
        // published events about changes to the dock so that we can reattach the click
        // event to the reload link.
        var dock = M.core.dock.get();
        dock.on(['dock:initialised', 'dock:itemadded'], function() {
            Y.Array.each(dock.dockeditems, function(dockeditem) {
                dockeditem.on('dockeditem:showcomplete', function() {
                    if (dockeditem.get('blockclass') === 'culupcoming_events') {
                        var reloader = Y.one('.dockeditempanel_hd .block_culupcoming_events_reload');
                        if (!reloader) {
                            var reloaddiv = Y.one('.block_culupcoming_events .reload').cloneNode(true);
                            var h2 = Y.one('#instance-' + dockeditem.get('blockinstanceid') + '-header' );
                            h2.append(reloaddiv);
                            reloaddiv.setStyle('display', 'inline-block');
                            reloader = Y.one('.dockeditempanel_hd .block_culupcoming_events_reload');
                        }
                        if (reloader) {
                            reloader.on('click', this.reloadblock, this);
                        }
                    }
                },this);
            },this);
        },this);

    },

    filltobelowblock: function() {
        var scrollHeight = this.scroller.get('scrollHeight');
        var scrollTop = this.scroller.get('scrollTop');
        var clientHeight = this.scroller.get('clientHeight');

        if ((scrollHeight - (scrollTop + clientHeight)) < 10) {
            // Pause the automatic refresh
            this.timer.cancel();
            var num = Y.all('.block_culupcoming_events .culupcoming_events li').size();
            if (num > 0) {
                var lastitem = Y.all('.block_culupcoming_events .culupcoming_events li').item(num - 1);
                lastid = lastitem.get('id').split('_')[0];
                lastdate = lastitem.get('id').split('_')[1];
            } else {
                lastid = 0;
                lastdate = 0;
            }
            this.addevents(num, lastid, lastdate);
            // Start the automatic refresh again now we have the correct last item
            this.timer = Y.later(1000 * 60 * 5, this, this.reloadevents, [], true);
        }
    },

    reloadblock: function(e) {
        e.preventDefault();
        this.reloadevents(e);
    },

    addevents: function(num, lastid, lastdate) {
        // disable the scroller until this completes
        this.scroller.detach('scroll');
        Y.one('.block_culupcoming_events_reload').setStyle('display', 'none');
        Y.one('.block_culupcoming_events_loading').setStyle('display', 'inline-block');

        var params = {
            sesskey : M.cfg.sesskey,
            limitfrom: 0,
            limitnum: this.limitnum,
            lastid : lastid,
            lastdate : lastdate,
            courseid: this.courseid
        };

        Y.io(M.cfg.wwwroot + '/blocks/culupcoming_events/scroll_ajax.php', {
            method: 'POST',
            data: build_querystring(params),
            context: this,
            on: {
                success: function(id, e) {
                    var data = Y.JSON.parse(e.responseText);
                    if (data.error) {
                        this.timer.cancel();
                    } else {
                        Y.one('.block_culupcoming_events .culupcoming_events ul').append(data.output);
                    }
                    // renable the scroller if there are more events
                    if (!data.end) {
                        this.scroller.on('scroll', this.filltobelowblock, this);
                    }
                    Y.one('.block_culupcoming_events_loading').setStyle('display', 'none');
                    Y.one('.block_culupcoming_events_reload').setStyle('display', 'inline-block');
                },
                failure: function() {
                    // error message
                    Y.one('.block_culupcoming_events_loading').setStyle('display', 'none');
                    Y.one('.block_culupcoming_events_reload').setStyle('display', 'inline-block');
                    this.timer.cancel();
                }
            }
        });
    },

    reloadevents: function() {
        var lastid = 0;
        var count = Y.all('.block_culupcoming_events .culupcoming_events li').size();

        if (count) {
            lastid = this.scroller.all('li').item(count - 1).get('id').split('_')[0];
        }

        Y.one('.block_culupcoming_events_reload').setStyle('display', 'none');
        Y.one('.block_culupcoming_events_loading').setStyle('display', 'inline-block');

        var params = {
            sesskey : M.cfg.sesskey,
            lastid : lastid,
            courseid: this.courseid
        };

        Y.io(M.cfg.wwwroot + '/blocks/culupcoming_events/reload_ajax.php', {
            method: 'POST',
            data: build_querystring(params),
            context: this,
            on: {
                success: function(id, e) {
                    var data = Y.JSON.parse(e.responseText);

                    if (data.error) {
                        this.timer.cancel();
                    } else {
                        if (data.output) {
                            Y.one('.block_culupcoming_events .culupcoming_events ul').set('innerHTML', data.output);
                        }
                    }

                    Y.one('.block_culupcoming_events_loading').setStyle('display', 'none');
                    Y.one('.block_culupcoming_events_reload').setStyle('display', 'inline-block');
                },
                failure: function() {
                    // error message
                    Y.one('.block_culupcoming_events_loading').setStyle('display', 'none');
                    Y.one('.block_culupcoming_events_reload').setStyle('display', 'inline-block');
                    this.timer.cancel();
                }
            }
        });
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
        "moodle-core-dock"
    ]
});
