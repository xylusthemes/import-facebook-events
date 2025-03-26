jQuery(document).ready(function ($) {
    const watchVideoBtn = $('#ife-watch-video-btn');
    const videoPopup    = $('#ife-wizard-video-popup');
    const videoFrame    = $('#ife-wizard-video-frame');
    const closePopup    = $('#ife-wizard-close-popup');

    // YouTube Video URL - replace with your own
    const videoURL = "https://www.youtube.com/embed/r_186_GDwso?si=wHzFW7Xzbn8610TL&autoplay=1";

    // Open the popup and set video source
    watchVideoBtn.on('click', function () {
        videoFrame.attr('src', videoURL);
        videoPopup.css('display', 'flex');
    });

    // Close popup on close button click
    closePopup.on('click', function () {
        videoFrame.attr('src', '');
        videoPopup.css('display', 'none');
    });

    // Close popup when clicking outside the video frame
    videoPopup.on('click', function (e) {
        if (e.target === this) {
            videoFrame.attr('src', '');
            videoPopup.css('display', 'none');
        }
    });
});
