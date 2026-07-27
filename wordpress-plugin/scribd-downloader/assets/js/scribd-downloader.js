/* Scribd Downloader Widget JS Script */
(function () {
    function initScribdDownloaderWidget() {
        var forms = document.querySelectorAll('.scribd-downloader-form');
        
        forms.forEach(function (form) {
            if (form.dataset.initialized) return;
            form.dataset.initialized = "true";

            var widget = form.closest('.scribd-downloader-widget-container');
            if (!widget) return;

            var submitBtn = widget.querySelector('.scribd-downloader-submit-btn');
            var resultDiv = widget.querySelector('.scribd-downloader-result');
            var apiUrl = form.dataset.apiUrl;
            var proxyUrl = form.dataset.proxyUrl;
            var originalBtnText = submitBtn ? submitBtn.innerText : 'Start download';
            var pollTimer = null;

            function stopPoll() {
                if (pollTimer) {
                    clearInterval(pollTimer);
                    pollTimer = null;
                }
            }

            function escapeHtml(s) {
                if (s == null) return '';
                return String(s)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function renderStatus(data) {
                var html = '<div class="scribd-downloader-status-box">';
                html += '<div class="scribd-downloader-status-title"><strong>Status:</strong> ' + escapeHtml(data.status) + '</div>';
                
                if (data.message) {
                    html += '<div style="margin-bottom: 8px; color: #475569; font-size: 14px;">' + escapeHtml(data.message) + '</div>';
                }

                if (data.progress && Array.isArray(data.progress) && data.progress.length > 0) {
                    var lines = data.progress.slice(-25);
                    html += '<div class="scribd-downloader-log">';
                    html += escapeHtml(lines.join('\n'));
                    html += '</div>';
                }

                if (data.status === 'completed' && data.job_id) {
                    var dlLink = '';
                    if (proxyUrl) {
                        dlLink = proxyUrl + (proxyUrl.indexOf('?') > -1 ? '&' : '?') + 'action=download&job_id=' + encodeURIComponent(data.job_id);
                    } else {
                        dlLink = apiUrl + (apiUrl.indexOf('?') > -1 ? '&' : '?') + 'action=download&job_id=' + encodeURIComponent(data.job_id);
                    }
                    html += '<a class="scribd-downloader-btn-download" href="' + dlLink + '" target="_blank" rel="noopener">Download PDF</a>';
                }

                if (data.status === 'failed' && data.error) {
                    html += '<div class="scribd-downloader-error">' + escapeHtml(data.error) + '</div>';
                }

                html += '</div>';
                resultDiv.innerHTML = html;
                resultDiv.style.display = 'block';
            }

            function pollStatus(jobId) {
                stopPoll();
                function check() {
                    var fetchUrl = '';
                    if (proxyUrl) {
                        fetchUrl = proxyUrl + (proxyUrl.indexOf('?') > -1 ? '&' : '?') + 'scribd_action=status&job_id=' + encodeURIComponent(jobId);
                    } else {
                        fetchUrl = apiUrl + (apiUrl.indexOf('?') > -1 ? '&' : '?') + 'action=status&job_id=' + encodeURIComponent(jobId);
                    }

                    fetch(fetchUrl, { method: 'GET' })
                        .then(function (res) { return res.json(); })
                        .then(function (data) {
                            if (!data || typeof data !== 'object') return;
                            renderStatus(data);
                            if (data.status === 'completed' || data.status === 'failed') {
                                stopPoll();
                                submitBtn.disabled = false;
                                submitBtn.innerText = originalBtnText;
                            }
                        })
                        .catch(function (err) {
                            resultDiv.innerHTML = '<div class="scribd-downloader-error">Status check failed. Please check connection.</div>';
                            resultDiv.style.display = 'block';
                        });
                }
                check();
                pollTimer = setInterval(check, 2500);
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                stopPoll();

                submitBtn.disabled = true;
                submitBtn.innerText = 'Starting…';
                resultDiv.style.display = 'none';
                resultDiv.innerHTML = '';

                var formData = new FormData(form);
                var requestUrl = apiUrl;
                
                if (proxyUrl) {
                    requestUrl = proxyUrl;
                    formData.append('scribd_action', 'submit');
                } else {
                    formData.append('action', 'submit');
                }

                fetch(requestUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(function (response) {
                    return response.json().then(function (json) {
                        return { ok: response.ok, status: response.status, data: json };
                    }).catch(function () {
                        return { ok: false, status: response.status, data: null };
                    });
                })
                .then(function (res) {
                    var data = res.data;
                    if (res.ok && data && data.job_id) {
                        submitBtn.innerText = 'Processing…';
                        renderStatus({
                            status: 'queued',
                            message: 'Job queued successfully...',
                            job_id: data.job_id,
                            progress: []
                        });
                        pollStatus(data.job_id);
                    } else if (data && data.error) {
                        resultDiv.innerHTML = '<div class="scribd-downloader-error">' + escapeHtml(data.error) + '</div>';
                        resultDiv.style.display = 'block';
                        submitBtn.disabled = false;
                        submitBtn.innerText = originalBtnText;
                    } else {
                        resultDiv.innerHTML = '<div class="scribd-downloader-error">Request failed. Please try again.</div>';
                        resultDiv.style.display = 'block';
                        submitBtn.disabled = false;
                        submitBtn.innerText = originalBtnText;
                    }
                })
                .catch(function (err) {
                    resultDiv.innerHTML = '<div class="scribd-downloader-error">Connection error: ' + escapeHtml(err.message || 'Unable to connect to backend') + '</div>';
                    resultDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerText = originalBtnText;
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScribdDownloaderWidget);
    } else {
        initScribdDownloaderWidget();
    }
})();

/* Turnstile Global Handlers */
function onScribdWidgetTurnstileSuccess() {
    var btns = document.querySelectorAll('.scribd-downloader-submit-btn');
    btns.forEach(function (btn) {
        btn.disabled = false;
    });
}

function onScribdWidgetTurnstileExpired() {
    var btns = document.querySelectorAll('.scribd-downloader-submit-btn');
    btns.forEach(function (btn) {
        btn.disabled = true;
    });
}
