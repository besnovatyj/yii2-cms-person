let videoSrc = '';
let videoImages = document.querySelectorAll('.img-container');
let videoModal = document.getElementById('videoModal');

// Вешаем события а все превьюхи
videoImages.forEach(function (img) {
    img.addEventListener('click', function (e) {
        videoSrc = e.target.dataset.url;
        openModal();
    });
});

// Закрыть модальное окно, если кликнули мимо него
window.onclick = function (event) {
    if (event.target === videoModal) {
        closeModal();
    }
};
// Закрыть модальное окно, если кликнули на кнопку закрытия модального окна
document.querySelector('button.btn-close').onclick = function (event) {
    if (event.target.closest('.modal') === videoModal) {
        closeModal();
    }
};

// Открываем модальное окно
function openModal() {
    document.getElementById("backdrop").style.display = "block";
    videoModal.style.display = "block";
    videoModal.classList.add("show");

    document.querySelector('#videoModal iframe').setAttribute('src', videoSrc);

}
// Закрываем модальное окно
function closeModal() {
    document.getElementById("backdrop").style.display = "none";
    videoModal.style.display = "none";
    videoModal.classList.remove("show");

    document.querySelector('#videoModal iframe').removeAttribute('src');
    videoSrc = '';

}

