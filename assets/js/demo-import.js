(function ($) {
    function setProgress(progress, message) {
        var $progressWrap = $('#cp-demo-import-progress');
        var $progressBar = $('#cp-demo-import-progress-bar');
        var $status = $('#cp-demo-import-status');

        $progressWrap.show();
        $progressBar.css('width', String(progress) + '%');

        if (message) {
            $status.text(message);
        }
    }

    function showError(message) {
        var $button = $('#cp-import-demo-button');
        setProgress(0, message || (campaignpressDemoImport && campaignpressDemoImport.strings && campaignpressDemoImport.strings.error) || 'Demo import failed.');
        $button.prop('disabled', false);
    }

    function runStep() {
        $.ajax({
            url: campaignpressDemoImport.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cp_demo_import_step',
                nonce: campaignpressDemoImport.nonce,
            },
        })
            .done(function (response) {
                if (!response || !response.success) {
                    showError((response && response.data && response.data.message) || null);
                    return;
                }

                var data = response.data || {};
                setProgress(data.progress || 0, data.message || campaignpressDemoImport.strings.working);

                if (data.done) {
                    setProgress(100, campaignpressDemoImport.strings.complete);
                    window.location = data.redirect_url || campaignpressDemoImport.redirect_url;
                    return;
                }

                window.setTimeout(runStep, 150);
            })
            .fail(function () {
                showError();
            });
    }

    function startImport() {
        var $button = $('#cp-import-demo-button');
        $button.prop('disabled', true);

        setProgress(1, campaignpressDemoImport.strings.starting);

        $.ajax({
            url: campaignpressDemoImport.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'cp_demo_import_start',
                nonce: campaignpressDemoImport.nonce,
            },
        })
            .done(function (response) {
                if (!response || !response.success) {
                    showError((response && response.data && response.data.message) || null);
                    return;
                }

                var data = response.data || {};
                setProgress(1, data.message || campaignpressDemoImport.strings.starting);

                window.setTimeout(runStep, 150);
            })
            .fail(function () {
                showError();
            });
    }

    $(function () {
        if (typeof campaignpressDemoImport === 'undefined') {
            return;
        }

        $('#cp-import-demo-form').on('submit', function (e) {
            e.preventDefault();
            startImport();
        });
    });
})(jQuery);
