document.getElementById('upload-form').onsubmit = function() {
    setTimeout(function() {
        const msg = document.querySelector('.message');
        if (msg) {
            msg.style.opacity = '1';
            setTimeout(() => { msg.style.opacity = '0'; }, 3000);
        }
    }, 100);
};