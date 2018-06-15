define(function() {

    var active = false,
        last,
        timeend,
        snippet,
        ctx,
        w, h, scale;

    var requestAnimationFrame = (
        window.requestAnimationFrame ||
        window.mozRequestAnimationFrame ||
        window.oRequestAnimationFrame ||
        window.msRequestAnimationFrame ||
        function(callback) {
            window.setTimeout(callback, 1000 / 60);
        });

    var renderProgressAnimationFrame = function() {

        if (!active)
            return;

        var currentTime = snippet.currentTime * 1000;
        var duration = snippet.duration * 1000;
        var progress;

        if (timeend && currentTime === last) {
            currentTime = duration - (timeend - new Date().getTime());
        } else {
            timeend = new Date().getTime() - currentTime + duration;
            last = currentTime;
        }

        if (duration > 0) {
            progress = currentTime / duration;
            renderProgressAnimation(progress);
        }

        requestAnimationFrame(renderProgressAnimationFrame);
    };

    var renderProgressAnimation = function(progress) {

        if (ctx) {
            ctx.clearRect(0, 0, w, h);

            ctx.globalAlpha = 0.3;

            ctx.beginPath();
            ctx.arc(w / 2, h / 2, 1 / 2.6 * scale, 0, 2 * Math.PI, false);
            ctx.stroke();

            ctx.globalAlpha = 1.0;
            ctx.beginPath();
            ctx.arc(w / 2, h / 2, 1 / 2.6 * scale, -Math.PI / 2, -Math.PI / 2 + progress * 2 * Math.PI, false);
            ctx.stroke();
        }

    };

    var updateProgressAnimation = function() {

        var progress = 0;

        if (snippet && snippet.duration > 0)
            progress = snippet.currentTime / snippet.duration;

        renderProgressAnimation(progress);
    };

    var startProgressAnimation = function(selected) {

        snippet = selected;

        var canvas = snippet.element.querySelector('.spt-playback');
        var ratio = window.devicePixelRatio >= 1.5 ? 2 : 1;
        w = canvas.parentElement.offsetWidth * ratio;
        h = canvas.parentElement.offsetHeight * ratio;
        scale = Math.min(w, h);
        canvas.width = w;
        canvas.height = h;
        canvas.style.display = 'block';

        ctx = canvas.getContext('2d');

        ctx.lineWidth = scale / 12;
        ctx.strokeStyle = '#a0e2f5';

        active = true;
        timeend = new Date().getTime() + snippet.duration * 1000;
        last = null;

        renderProgressAnimationFrame();
    };

    var pauseProgressAnimation = function() {
        active = false;
        timeend = null;
        last = null;
    };

    var resumeProgressAnimation = function() {
        active = true;
        renderProgressAnimationFrame();
    };

    var stopProgressAnimation = function() {
        active = false;
        timeend = null;
        last = null;

        if (ctx)
            ctx.clearRect(0, 0, w, h);

        w = null;
        h = null;
        scale = null;
        ctx = null;
    };

    return {
        start: function(snippet) {
            startProgressAnimation(snippet);
        },

        pause: function() {
            pauseProgressAnimation();
        },

        update: function() {
            updateProgressAnimation();
        },

        resume: function() {
            resumeProgressAnimation();
        },

        stop: function() {
            stopProgressAnimation();
        }
    };

});