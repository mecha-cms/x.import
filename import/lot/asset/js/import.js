(function(win, doc, _) {

    function onChange() {

        let queue = [{}, 0, 0, 0], // [request(s), loading, loaded, failed]
            importForm = doc.querySelector('#import-form'),
            importLog = doc.querySelector('#import-log'),
            importLogItem,
            importLogStatus = doc.createElement('li'),
            importLogTab = doc.querySelector('.lot\\:tab a[data-name="log"]');

        if (!importForm) return;

        function abort() {
            for (k in queue[0]) {
                v = queue[0][k];
                v.abort && 'function' === typeof v.abort && v.abort();
            }
        }

        function ajax(url, fn) {
            ++queue[1];
            if ('function' === typeof fetch) {
                let controller = new AbortController,
                    signal = controller.signal;
                fetch(url, {
                    headers: new Headers({
                        'X-Requested-With': 'XHR'
                    }),
                    method: 'get',
                    signal: signal
                }).then(function(response) {
                    if (!response.ok) {
                        ++queue[3];
                        update();
                        throw Error(response.statusText);
                    }
                    return response.json();
                }).then(function(json) {
                    fn(json);
                    --queue[1];
                    ++queue[2];
                    update();
                }).catch(function(err) {
                    ++queue[3];
                    let data = {
                        log: {},
                        next: false
                    };
                    data.log['0 ' + (Date.now() / 1000)] = {
                        status: 408,
                        description: err
                    };
                    fn(data);
                    update();
                });
                queue[0][url] = controller;
            } else {
                const xhr = new XMLHttpRequest;
                xhr.addEventListener('readystatechange', function() {
                    if (4 === xhr.readyState && 200 === xhr.status) {
                        fn(JSON.parse(xhr.responseText));
                        --queue[1];
                        ++queue[2];
                        update();
                    }
                });
                xhr.addEventListener('error', function() {
                    ++queue[3];
                    let data = {
                        log: {},
                        next: false
                    };
                    data.log['0 ' + (Date.now() / 1000)] = {
                        status: 408,
                        description: 'Network error.'
                    };
                    fn(data);
                    update();
                });
                xhr.open('GET', url, true);
                xhr.setRequestHeader('X-Requested-With', 'XHR');
                xhr.send();
                queue[0][url] = xhr;
            }
            update();
        }

        function next(data) {
            if (data.log) {
                let k, v, parent;
                for (k in data.log) {
                    v = data.log[k];
                    importLogItem = doc.createElement('li');
                    importLogItem.className = v.status ? 'status:' + v.status : "";
                    importLogItem.innerHTML = '<p>' + time(+(k.split(' ')[1] || "") * 1000) + ' ' + v.description + '</p>';
                    if (v.parent && (parent = doc.getElementById('log:' + v.parent))) {
                        parent.children[1].appendChild(importLogItem);
                    } else {
                        importLog && importLog.appendChild(importLogItem);
                    }
                    if (v.id) {
                        importLogItem.id = 'log:' + v.id;
                        importLogItem.appendChild(doc.createElement('ul'));
                    }
                    if (v.next && 'string' === typeof v.next) {
                        ajax(v.next, next);
                    }
                }
            }
            if ('string' === typeof data.next) {
                ajax(data.next, next);
            } else {
                update();
            }
        }

        function time(k) {
            let date = new Date(k),
                s = "";
            s += '[';
            s += date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0') + ':' + date.getSeconds().toString().padStart(2, '0');
            s += ']';
            return s;
        }

        function update() {
            if (importLogStatus) {
                importLogStatus.className = 'status:' + (queue[3] > 0 ? '408' : (0 === queue[1] ? '201' : '102'));
                importLogStatus.children[0].innerHTML = '[Request: ' + Object.keys(queue[0]).length + '] [Loading: ' + queue[1] + '] [Loaded: ' + queue[2] + '] [Failed: ' + queue[3] + ']';
            }
        }

        importForm.addEventListener('submit', function(e) {
            this.setAttribute('disabled', 'disabled');
            let k, v;
            abort();
            queue = [{}, 0, 0, 0]; // Reset queue
            if (importLog) {
                importLog.removeChild(importLogStatus);
                importLog.innerHTML = "";
                importLogItem = doc.createElement('li');
                importLogItem.className = 'status:202';
                importLogItem.innerHTML = '<p>' + time(Date.now()) + ' ' + this.getAttribute('data-loading') + '</p>';
                importLog.appendChild(importLogStatus);
                update();
                importLogStatus.appendChild(importLogAborterSpace);
                importLogStatus.appendChild(importLogAborter);
                importLog.appendChild(importLogItem);
            }
            importLogTab && importLogTab.click();
            let query = new URLSearchParams(new FormData(this));
            // console.log(this.action + '?' + query);
            ajax(this.action + '?' + query, next);
            e.preventDefault();
        });

        importLogStatus.id = 'import-log-status';
        importLogStatus.innerHTML = '<p></p>';
        importLog.appendChild(importLogStatus);

        update();

        let importLogAborter = doc.createElement('a'),
            importLogAborterSpace = doc.createTextNode(' ');

        importLogAborter.innerHTML = '\u00d7';
        importLogAborter.href = "";
        importLogAborter.addEventListener('click', function(e) {
            importLogAborterSpace.parentNode.removeChild(importLogAborterSpace);
            importLogAborter.parentNode.removeChild(importLogAborter);
            abort();
            e.preventDefault();
        });

    } onChange();

    _.on('change', onChange);

})(this, this.document, this._);
