document.addEventListener('DOMContentLoaded', function () {
    var togglers = document.querySelectorAll('.mtech-caret');

    togglers.forEach(function (toggler) {
        toggler.addEventListener('click', function () {
            var nested = this.parentElement.querySelector(':scope > .mtech-nested');
            if (nested) {
                nested.classList.toggle('mtech-active');
                this.classList.toggle('mtech-caret-open');
            }
        });
    });
});