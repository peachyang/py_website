(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require("jquery"));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        var sender = $('#livechat #chat-form [name=sender]').val();
        $('#livechat #chat-form [name=sender]').remove();
        var msg = null;
        var instance = null;
        var ws = function (url) {
            this.url = url;
            this.retry = 0;
            this.partial = {};
            this.query = [];
            this.connect();
        };
        ws.prototype = {
            check: function () {
                return this.socket && this.socket.readyState == 1;
            },
            ping: function (_this) {
                _this = _this ? _this : this;
                var buffer = new ArrayBuffer(2);
                var i8V = new Int8Array(buffer);
                i8V[0] = 0x09;
                i8V[1] = 0;
                if (_this.check()) {
                    _this.socket.send(buffer);
                }
                clearTimeout(_this.pingTimeout);
                _this.pingTimeout = setTimeout(_this.ping, 60000, _this);
            },
            connect: function (_this) {
                _this = _this ? _this : this;
                try {
                    _this.lock = false;
                    if (_this.check()) {
                        return;
                    }
                    $.ajax(GLOBAL.BASE_URL + 'livechat/index/prepare/', {
                        type: 'head',
                        context: _this,
                        success: function () {
                            this.socket = new WebSocket(this.url);
                            this.socket.onopen = this.onopen;
                            this.socket.onmessage = this.onmessage;
                            this.socket.onclose = this.onclose;
                        },
                        error: function () {
                            _this.reconnect.call(_this);
                        }
                    });
                } catch (e) {
                    _this.reconnect.call(_this);
                }
            },
            reconnect: function (_this) {
                _this = _this ? _this : this;
                if (!_this.lock || _this.retry <= 5) {
                    _this.lock = true;
                    setTimeout(_this.connect, 2000 * Math.pow(2, _this.retry), _this);
                    _this.retry = _this.retry + 1;
                }
            },
            onopen: function () {
                instance.retry = 0;
                instance.send('{"sender":' + msg.sender + '}');
                if (instance.query.length) {
                    for (var i in instance.query) {
                        instance.send(instance.query[i]);
                    }
                    instance.query = [];
                }
            },
            onmessage: function (e) {
                var data = e.data;
                if (typeof data === 'string') {
                    data = eval('(' + data + ')');
                }
                if (data.partial !== undefined) {
                    if (instance.partial[data.session + '-' + data.sender] === undefined) {
                        instance.partial[data.session + '-' + data.sender] = {};
                    }
                    instance.partial[data.session + '-' + data.sender][data.partial] = data.msg;
                    if (!data.end) {
                        return;
                    }
                }
                if (data.end) {
                    data.msg = '';
                    var f = true;
                    var m = 1;
                    var p = instance.partial[data.session + '-' + data.sender];
                    while (f) {
                        var j = true;
                        var f = false;
                        for (var i in p) {
                            if (j || parseInt(i) < m) {
                                m = parseInt(i);
                                j = false;
                                f = true;
                            }
                        }
                        if (p[m]) {
                            data.msg += p[m];
                            delete p[m];
                        } else {
                            break;
                        }
                    }
                    instance.log(data);
                    delete instance.partial[data.session + '-' + data.sender];
                    delete msg.end;
                    delete msg.partial;
                } else if (data.new) {
                    $('#livechat').trigger('new.livechat', data.new);
                } else {
                    instance.log(data);
                }
            },
            onclose: function () {
                instance.reconnect.call(instance);
            },
            send: function (m) {
                if (this.check()) {
                    this.socket.send(m);
                    clearTimeout(this.pingTimeout);
                    this.pingTimeout = setTimeout(instance.ping, 60000, instance);
                } else {
                    this.query.push(m);
                }
            },
            log: function (data) {
                var c = data.sender == msg.sender ? 'self' : 'others';
                var t = data.type;
                var m = data.msg;
                if (t === 'image') {
                    m = '<img src="' + m + '" />';
                } else if (t === 'audio') {
                    m = '<audio controls="controls" src="' + m + '" />'
                }
                if (t === 'text' || t === 'image' || t === 'audio') {
                    $('#livechat #' + data.session + ' .chat-list').append($('<li class="' + c + '">' + m + '</li>'));
                    if (localStorage[data.session]) {
                        var r = JSON.parse(localStorage[data.session]);
                        r.push({class: c, msg: m});
                        localStorage[data.session] = JSON.stringify(r);
                    } else {
                        localStorage[data.session] = JSON.stringify([{class: c, msg: m}]);
                    }
                }
                $('#livechat #' + data.session + ' .chat-history').scrollTop($('#livechat #' + data.session + ' .chat-list').height());
                $('#livechat').trigger('notify', t === 'text' ? m : '');
                return false;
            }
        };
        var notify = function (e, t) {
            if (Notification.permission === 'granted') {
                var n = new Notification(translate('You have received a new message.'), {
                    body: t
                });
                setTimeout(function () {
                    n.close();
                }, 3000);
            }
        };
        var init = function () {
            msg = {
                session: '',
                sender: sender,
                type: 'text',
                msg: ''
            }
            instance = new ws($('#livechat #chat-form').attr('action'));
            if (Notification.permission !== 'granted') {
                Notification.requestPermission();
            }
        };
        $(window).on({
            focus: function () {
                $('#livechat').off('notify');
            },
            blur: function () {
                $('#livechat').on('notify', notify);
            }
        });
        var send = function (session) {
            var o = $('#livechat #' + session + ' [name=msg]');
            msg.session = session;
            msg.type = 'text';
            msg.msg = $(o).val();
            $(o).val('');
            if (instance && instance.check()) {
                instance.send(JSON.stringify(msg));
            } else {
                init();
                $(instance).one('opened', function () {
                    instance.send(JSON.stringify(msg));
                });
            }
        };
        var sendBin = function (session, type) {
            var o = $('#livechat #' + session + ' [type=file].send-' + type)[0];
            msg.partial = 0;
            var reader = new FileReader();
            reader.readAsDataURL(o.files[0]);
            reader.onload = function () {
                msg.session = session;
                msg.type = type;
                var end = this.result.length;
                for (var i = 0; i < end; i += 1000) {
                    msg.partial = i;
                    msg.msg = this.result.slice(i, i + 1000);
                    if (i + 1000 >= end) {
                        msg.end = 1;
                    }
                    if (instance && instance.check()) {
                        instance.send(JSON.stringify(msg));
                    } else {
                        init();
                        $(instance).one('opened', function () {
                            this.send(JSON.stringify(msg));
                        });
                    }
                }
            };
        };
        var ids = '[';
        $('#livechat .tab-pane [name=session]').each(function () {
            $(this).siblings('[name=sender]').remove();
            ids += '"' + this.value + '",';
        });
        init();
        $('#livechat #chat-form').on('click', '[type=submit]', function () {
            var session = $(this).val();
            send(session);
            return false;
        }).on('change', '.send-image', function () {
            var session = $(this).parents('.tab-pane').first().find('[name=session]').val();
            sendBin(session, 'image');
        }).on('change', '.send-audio', function () {
            var session = $(this).parents('.tab-pane').first().find('[name=session]').val();
            sendBin(session, 'audio');
        });
    });
}));
