(function(win, doc) {

    function ajax(url, fn) {
        if (typeof fetch === "function") {
            fetch(url, {
                headers: new Headers({
                    'X-Requested-With': 'XHR'
                })
            }).then(function(response) {
                if (!response.ok) {
                    throw Error(response.statusText);
                }
                response.json().then(fn);
            }).catch(function(err) {
                fn({
                    log: {
                        "0": {
                            status: 408,
                            description: err
                        }
                    },
                    next: false
                });
            });
            return;
        }
        var xhr = new XMLHttpRequest;
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                fn(JSON.parse(xhr.responseText));
            }
        };
        xhr.onerror = function() {
            fn({
                log: {
                    "0": {
                        status: 408,
                        description: 'Network error.'
                    }
                },
                next: false
            });
        };
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-Requested-With', 'XHR');
        xhr.send();
    }

    function next(data) {
        if (data.log) {
            var k, v, li, parent;
            for (var k in data.log) {
                v = data.log[k];
                li = doc.createElement('li');
                li.className = v.status ? 'status:' + v.status : "";
                li.innerHTML = '<p>' + v.description + '</p>';
                if (v.parent && (parent = doc.getElementById('log:' + v.parent))) {
                    parent.children[1].appendChild(li);
                } else {
                    ul.appendChild(li);
                }
                if (v.id) {
                    li.id = 'log:' + v.id;
                    li.appendChild(doc.createElement('ul'));
                }
                if (v.next && 'string' === typeof v.next) {
                    ajax(v.next, next);
                }
            }
        }
        if ('string' === typeof data.next) {
            ajax(data.next, next);
        }
    }

    var ul = doc.querySelector('#import-log');
    doc.querySelector('#import-form').addEventListener('submit', function(e) {
        this.setAttribute('disabled', 'disabled');
        ul.innerHTML = "";
        var li = doc.createElement('li');
        li.className = 'status:202';
        li.innerHTML = '<p>' + this.getAttribute('data-loading') + '</p>';
        ul.appendChild(li);
        var query = new URLSearchParams(new FormData(this));
        ajax(this.action + '?' + query, next);
        e.preventDefault();
    });

})(window, document);
