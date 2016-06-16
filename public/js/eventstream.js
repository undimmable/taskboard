(function (global) {
    // if ("EventSource" in global) return;
    var reTrim = /^(\s|\u00A0)+|(\s|\u00A0)+$/g;
    var EventSource = function (url) {
        var eventsource = this,
            interval = 30,
            lastEventId = null,
            cache = '';

        if (!url || typeof url != 'string') {
            throw new SyntaxError('Not enough arguments');
        }

        this.URL = url;
        this.readyState = this.CONNECTING;
        this._pollTimer = null;
        this._xhr = null;

        function pollAgain(interval) {
            eventsource._pollTimer = setTimeout(function () {
                poll.call(eventsource);
            }, interval);
        }

        function poll() {
            try {
                if (eventsource.readyState == eventsource.CLOSED) return;
                var xhr = new XMLHttpRequest();
                xhr.open('GET', eventsource.URL, true);
                xhr.setRequestHeader('Accept', 'text/event-stream');
                xhr.setRequestHeader('Cache-Control', 'no-cache');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                var userData = document.getElementById('user-data');
                xhr.setRequestHeader('X-CSRF-TOKEN', userData.getAttribute('data-evsid'));
                xhr.setRequestHeader('X-CURRENT-SNAPSHOT-TIMESTAMP', userData.getAttribute('data-current-snapshot-timestamp'));

                if (lastEventId != null) xhr.setRequestHeader('Last-Event-ID', lastEventId);
                cache = '';

                xhr.timeout = 50000;
                xhr.onreadystatechange = function () {
                    if (this.readyState == 3 || (this.readyState == 4 && (this.status == 200 || this.status == 0))) {
                        // on success
                        if (eventsource.readyState == eventsource.CONNECTING) {
                            eventsource.readyState = eventsource.OPEN;
                            eventsource.dispatchEvent('open', {type: 'open'});
                        }

                        var responseText = '';
                        try {
                            responseText = this.responseText || '';
                        } catch (e) {
                        }
                        var parts = responseText.substr(cache.length).split("\n"),
                            eventType = 'message',
                            data = [],
                            i = 0,
                            line = '';

                        cache = responseText;
                        for (; i < parts.length; i++) {
                            line = parts[i].replace(reTrim, '');
                            if (line.indexOf('event') == 0) {
                                eventType = line.replace(/event:?\s*/, '');
                            } else if (line.indexOf('retry') == 0) {
                                var retry = parseInt(line.replace(/retry:?\s*/, ''));
                                if (!isNaN(retry)) {
                                    interval = retry;
                                }
                            } else if (line.indexOf('data') == 0) {
                                data.push(line.replace(/data:?\s*/, ''));
                            } else if (line.indexOf('id:') == 0) {
                                lastEventId = line.replace(/id:?\s*/, '');
                            } else if (line.indexOf('id') == 0) { // this resets the id
                                lastEventId = null;
                            } else if (line == '') {
                                if (data.length) {
                                    var event = new MessageEvent(data.join('\n'), eventsource.url, lastEventId);
                                    eventsource.dispatchEvent(eventType, event);
                                    data = [];
                                    eventType = 'message';
                                }
                            }
                        }

                        if (this.readyState == 4 && this.status != 0) pollAgain(interval);
                    } else if (eventsource.readyState !== eventsource.CLOSED) {
                        if (this.readyState == 4) { // and some other status
                            // dispatch error
                            eventsource.readyState = eventsource.CONNECTING;
                            eventsource.dispatchEvent('error', {type: 'error'});
                            pollAgain(interval);
                        } else if (this.readyState == 0) { // likely aborted
                            pollAgain(interval);
                        } else {
                        }
                    }
                };

                xhr.send();

                setTimeout(function () {
                    if (true || xhr.readyState == 3) xhr.abort();
                }, xhr.timeout);

                eventsource._xhr = xhr;

            } catch (e) {
                eventsource.dispatchEvent('error', {type: 'error', data: e.message}); // ???
            }
        }

        poll(); // init now
    };
    EventSource.prototype = {
        close: function () {
            // closes the connection - disabling the polling
            this.readyState = this.CLOSED;
            clearInterval(this._pollTimer);
            this._xhr.abort();
        },
        CONNECTING: 0,
        OPEN: 1,
        CLOSED: 2,
        dispatchEvent: function (type, event) {
            var handlers = this['_' + type + 'Handlers'];
            if (handlers) {
                for (var i = 0; i < handlers.length; i++) {
                    handlers[i].call(this, event);
                }
            }

            if (this['on' + type]) {
                this['on' + type].call(this, event);
            }
        },
        addEventListener: function (type, handler) {
            if (!this['_' + type + 'Handlers']) {
                this['_' + type + 'Handlers'] = [];
            }

            this['_' + type + 'Handlers'].push(handler);
        },
        removeEventListener: function (type, handler) {
            var handlers = this['_' + type + 'Handlers'];
            if (!handlers) {
                return;
            }
            for (var i = handlers.length - 1; i >= 0; --i) {
                if (handlers[i] === handler) {
                    handlers.splice(i, 1);
                    break;
                }
            }
        },
        onerror: null,
        onmessage: null,
        onopen: null,
        readyState: 0,
        URL: ''
    };
    var MessageEvent = function (data, origin, lastEventId) {
        this.data = data;
        this.origin = origin;
        this.lastEventId = lastEventId || '';
    };
    MessageEvent.prototype = {
        data: null,
        type: 'message',
        lastEventId: '',
        origin: ''
    };
    if ('module' in global) global['module'].exports = EventSource;
    global.EventSource = EventSource;
})(window);