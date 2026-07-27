<!-- JavaScript files-->
<script src="/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function onTurnstileSuccess() {
        var submitButton = document.getElementById('submit');
        if (submitButton) {
            submitButton.disabled = false;
        }
    }

    function onTurnstileExpired() {
        var submitButton = document.getElementById('submit');
        if (submitButton) {
            submitButton.disabled = true;
        }
    }

    function onTurnstileSuccessScribd() {
        var submitButton = document.getElementById('scribd_submit');
        if (submitButton) {
            submitButton.disabled = false;
        }
    }

    function onTurnstileExpiredScribd() {
        var submitButton = document.getElementById('scribd_submit');
        if (submitButton) {
            submitButton.disabled = true;
        }
    }

    $(document).ready(function () {
        var form = $("#codehap_form");
        var submitButton = $("#submit");
        var resultDiv = $("#codehap_result");
        var x = submitButton.text();

        form.submit(function (e) {
            $('#codehap_result').hide();
            e.preventDefault(); // Prevent form submission

            submitButton.prop("disabled", true); // Disable the button
            submitButton.text("Loading...");

            var formData = new FormData(form[0]);

            $.ajax({
                type: "POST",
                url: "/result.php", // Change this to your PHP file URL
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#codehap_result').show();
                    resultDiv.html(response); // Show response in the div
                },
                error: function (xhr) {
                    $('#codehap_result').show();
                    var serverMsg = xhr && xhr.responseText ? xhr.responseText : '';
                    if (serverMsg && serverMsg.length < 1000) {
                        resultDiv.html(serverMsg);
                    } else {
                        resultDiv.html('<div class="codehap_danger">Request failed. Please try again.</div>');
                    }
                },
                complete: function () {
                    submitButton.text(x);
                    submitButton.prop("disabled", false);
                }
            });
        });

        var scribdForm = $('#scribd_form');
        if (scribdForm.length) {
            var scribdBtn = $('#scribd_submit');
            var scribdResult = $('#scribd_result');
            var scribdBtnText = scribdBtn.text();
            var scribdPollTimer = null;

            function stopScribdPoll() {
                if (scribdPollTimer) {
                    clearInterval(scribdPollTimer);
                    scribdPollTimer = null;
                }
            }

            function escapeHtml(s) {
                return $('<div/>').text(s == null ? '' : String(s)).html();
            }

            function renderScribdStatus(data) {
                var html = '<div class="codehap-container">';
                html += '<p class="mb-1"><strong>Status:</strong> ' + escapeHtml(data.status) + '</p>';
                if (data.message) {
                    html += '<p class="mb-2">' + escapeHtml(data.message) + '</p>';
                }
                if (data.progress && data.progress.length) {
                    var lines = data.progress.slice(-30);
                    html += '<pre class="small bg-light p-2 rounded mb-2 text-start" style="max-height:220px;overflow:auto;white-space:pre-wrap;">';
                    html += escapeHtml(lines.join('\n'));
                    html += '</pre>';
                }
                if (data.status === 'completed' && data.job_id) {
                    html += '<a class="button-5 d-inline-block text-decoration-none mt-1" href="/scribd_api.php?action=download&job_id=' + encodeURIComponent(data.job_id) + '">Download PDF</a>';
                }
                if (data.status === 'failed' && data.error) {
                    html += '<p class="codehap_danger mb-0">' + escapeHtml(data.error) + '</p>';
                }
                html += '</div>';
                scribdResult.html(html).show();
            }

            function pollScribd(jobId) {
                stopScribdPoll();
                function tick() {
                    $.ajax({
                        url: '/scribd_api.php',
                        type: 'GET',
                        data: { action: 'status', job_id: jobId },
                        dataType: 'json',
                        success: function (data) {
                            if (!data || typeof data !== 'object') {
                                return;
                            }
                            renderScribdStatus(data);
                            if (data.status === 'completed' || data.status === 'failed') {
                                stopScribdPoll();
                                scribdBtn.prop('disabled', false).text(scribdBtnText);
                            }
                        },
                        error: function (xhr) {
                            var msg = 'Status check failed.';
                            try {
                                var j = JSON.parse(xhr.responseText);
                                if (j && j.error) {
                                    msg = j.error;
                                }
                            } catch (e) {}
                            scribdResult.html('<div class="codehap_danger">' + escapeHtml(msg) + '</div>').show();
                        }
                    });
                }
                tick();
                scribdPollTimer = setInterval(tick, 2500);
            }

            scribdForm.on('submit', function (e) {
                e.preventDefault();
                stopScribdPoll();
                scribdBtn.prop('disabled', true).text('Starting…');
                scribdResult.hide().empty();

                var fd = new FormData(scribdForm[0]);
                fd.append('action', 'submit');

                $.ajax({
                    type: 'POST',
                    url: '/scribd_api.php',
                    data: fd,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (res) {
                        if (res && res.job_id) {
                            scribdBtn.text('Processing…');
                            renderScribdStatus({
                                status: 'queued',
                                message: 'Job queued…',
                                job_id: res.job_id,
                                progress: []
                            });
                            pollScribd(res.job_id);
                        } else if (res && res.error) {
                            scribdResult.html('<div class="codehap_danger">' + escapeHtml(res.error) + '</div>').show();
                            scribdBtn.prop('disabled', false).text(scribdBtnText);
                        } else {
                            scribdResult.html('<div class="codehap_danger">Unexpected response from server.</div>').show();
                            scribdBtn.prop('disabled', false).text(scribdBtnText);
                        }
                    },
                    error: function (xhr) {
                        var msg = 'Request failed.';
                        try {
                            var j = JSON.parse(xhr.responseText);
                            if (j && j.detail) {
                                msg = typeof j.detail === 'string' ? j.detail : JSON.stringify(j.detail);
                            } else if (j && j.error) {
                                msg = j.error;
                            }
                        } catch (e) {
                            if (xhr.responseText && xhr.responseText.length < 800) {
                                msg = xhr.responseText;
                            }
                        }
                        scribdResult.html('<div class="codehap_danger">' + escapeHtml(msg) + '</div>').show();
                        scribdBtn.prop('disabled', false).text(scribdBtnText);
                    }
                });
            });
        }
    });
</script>


    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
  </body>
</html>