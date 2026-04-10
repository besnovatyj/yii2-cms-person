// Добавляет CSRF-токен Yii2 ко всем htmx POST-запросам.
// Yii2 регистрирует <meta name="csrf-token"> через layout автоматически.
document.addEventListener('htmx:configRequest', function (evt) {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        evt.detail.headers['X-CSRF-Token'] = meta.getAttribute('content') || '';
    }
});
