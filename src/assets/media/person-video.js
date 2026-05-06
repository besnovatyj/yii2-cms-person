/**
 * Управление видеороликами актёров.
 */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // --- Страница управления видео актёра (index) ---

    const grid = document.getElementById('video-grid');
    if (grid) {
        initVideoManager(grid);
    }

    // --- Страница тестирования парсинга (test-parse) ---

    const testBtn = document.getElementById('test-parse-btn');
    if (testBtn) {
        initTestParse(testBtn);
    }
});

/**
 * Инициализация страницы управления видео.
 */
function initVideoManager(grid) {
    const personId = grid.dataset.personId;
    const endpoints = JSON.parse(grid.dataset.endpoints);
    const csrfParam = grid.dataset.csrfParam;
    const csrfToken = grid.dataset.csrfToken;

    const addBtn = document.getElementById('video-add-btn');
    const urlInput = document.getElementById('video-source-url');
    const addError = document.getElementById('video-add-error');
    const addLoading = document.getElementById('video-add-loading');
    const emptyMessage = document.getElementById('video-empty-message');

    // Добавление видеоролика
    addBtn.addEventListener('click', function () {
        const sourceUrl = urlInput.value.trim();
        if (!sourceUrl) {
            showAddError('Введите URL видеоролика.');
            return;
        }
        addVideo(sourceUrl);
    });

    urlInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            addBtn.click();
        }
    });

    // Делегирование событий для карточек
    grid.addEventListener('click', function (e) {
        const deleteBtn = e.target.closest('.video-delete-btn');
        if (deleteBtn) {
            const card = deleteBtn.closest('.video-card');
            const videoId = card.dataset.videoId;
            if (confirm('Удалить видеоролик?')) {
                deleteVideo(videoId, card);
            }
            return;
        }

        const verifyBtn = e.target.closest('.video-verify-btn');
        if (verifyBtn) {
            const card = verifyBtn.closest('.video-card');
            const videoId = card.dataset.videoId;
            verifyVideo(videoId, card);
            return;
        }

        const refreshBtn = e.target.closest('.video-refresh-btn');
        if (refreshBtn) {
            const card = refreshBtn.closest('.video-card');
            const videoId = card.dataset.videoId;
            refreshVideo(videoId, card);
            return;
        }

        // Сортировка: переместить выше
        const moveUpBtn = e.target.closest('.video-move-up-btn');
        if (moveUpBtn) {
            const card = moveUpBtn.closest('.video-card');
            moveCard(card, 'up');
            return;
        }

        // Сортировка: переместить ниже
        const moveDownBtn = e.target.closest('.video-move-down-btn');
        if (moveDownBtn) {
            const card = moveDownBtn.closest('.video-card');
            moveCard(card, 'down');
            return;
        }

        // Копирование source_url
        const copyBtn = e.target.closest('.video-copy-btn');
        if (copyBtn) {
            const card = copyBtn.closest('.video-card');
            copySourceUrl(card.dataset.sourceUrl, copyBtn);
            return;
        }

        // Просмотр видео по клику на превью
        const playBtn = e.target.closest('.video-play-btn');
        if (playBtn) {
            const card = playBtn.closest('.video-card');
            openVideoPreview(
                card.dataset.iframeUrl,
                card.dataset.iframeAllow || '',
                card.dataset.iframeReferrerpolicy || ''
            );
            return;
        }
    });

    async function addVideo(sourceUrl) {
        hideAddError();
        addLoading.style.display = '';
        addBtn.disabled = true;

        try {
            const result = await postJson(endpoints.add, {
                person_id: personId,
                source_url: sourceUrl,
            }, csrfParam, csrfToken);

            if (result.status === 'success') {
                appendVideoCard(result.data);
                urlInput.value = '';
                if (emptyMessage) {
                    emptyMessage.style.display = 'none';
                }
            } else {
                showAddError(result.message || 'Неизвестная ошибка.');
            }
        } catch (err) {
            showAddError(err.message);
        } finally {
            addLoading.style.display = 'none';
            addBtn.disabled = false;
            urlInput.focus();
        }
    }

    async function deleteVideo(videoId, cardElement) {
        try {
            const result = await postJson(endpoints.delete, {
                video_id: videoId,
            }, csrfParam, csrfToken);

            if (result.status === 'success') {
                cardElement.remove();
                if (grid.querySelectorAll('.video-card').length === 0 && emptyMessage) {
                    emptyMessage.style.display = '';
                }
            } else {
                alert(result.message || 'Ошибка удаления.');
            }
        } catch (err) {
            alert(err.message);
        }
    }

    async function verifyVideo(videoId, cardElement) {
        const overlay = cardElement.querySelector('.video-status-overlay');
        overlay.textContent = 'Проверка...';
        overlay.className = 'video-status-overlay checking';
        overlay.style.display = '';

        try {
            const result = await postJson(endpoints.verify, {
                video_id: videoId,
            }, csrfParam, csrfToken);

            if (result.status === 'success') {
                const data = result.data;
                if (data.match) {
                    overlay.textContent = 'OK';
                    overlay.className = 'video-status-overlay success';
                } else if (data.new_iframe === null) {
                    overlay.textContent = 'URL не распознан';
                    overlay.className = 'video-status-overlay error';
                } else {
                    overlay.textContent = 'Данные изменились';
                    overlay.className = 'video-status-overlay warning';
                }
            } else {
                overlay.textContent = result.message || 'Ошибка';
                overlay.className = 'video-status-overlay error';
            }
        } catch (err) {
            overlay.textContent = err.message;
            overlay.className = 'video-status-overlay error';
        }

        setTimeout(function () {
            overlay.style.display = 'none';
        }, 3000);
    }

    async function refreshVideo(videoId, cardElement) {
        const overlay = cardElement.querySelector('.video-status-overlay');
        overlay.textContent = 'Обновление...';
        overlay.className = 'video-status-overlay checking';
        overlay.style.display = '';

        try {
            const result = await postJson(endpoints.refresh, {
                video_id: videoId,
            }, csrfParam, csrfToken);

            if (result.status === 'success') {
                const img = cardElement.querySelector('.card-img-top');
                if (img) {
                    img.src = result.data.thumbnail_url;
                }
                // Обновляем data-атрибуты после refresh
                cardElement.dataset.iframeAllow = result.data.iframe_allow || '';
                cardElement.dataset.iframeReferrerpolicy = result.data.iframe_referrerpolicy || '';
                overlay.textContent = 'Обновлено';
                overlay.className = 'video-status-overlay success';
            } else {
                overlay.textContent = result.message || 'Ошибка';
                overlay.className = 'video-status-overlay error';
            }
        } catch (err) {
            overlay.textContent = err.message;
            overlay.className = 'video-status-overlay error';
        }

        setTimeout(function () {
            overlay.style.display = 'none';
        }, 3000);
    }

    /**
     * Перемещает карточку вверх или вниз и сохраняет порядок на сервере.
     */
    function moveCard(card, direction) {
        const cards = Array.from(grid.querySelectorAll('.video-card'));
        const index = cards.indexOf(card);

        if (direction === 'up' && index > 0) {
            grid.insertBefore(card, cards[index - 1]);
        } else if (direction === 'down' && index < cards.length - 1) {
            cards[index + 1].after(card);
        } else {
            return;
        }

        card.classList.add('moving');
        card.addEventListener('animationend', function () {
            card.classList.remove('moving');
        }, { once: true });

        saveOrder();
    }

    /**
     * Собирает текущий порядок ID и отправляет на сервер.
     */
    async function saveOrder() {
        const orderedIds = Array.from(grid.querySelectorAll('.video-card'))
            .map(function (el) { return el.dataset.videoId; });

        try {
            await postJson(endpoints.reorder, {
                person_id: personId,
                'ordered_ids[]': orderedIds,
            }, csrfParam, csrfToken);
        } catch (err) {
            console.error('Ошибка сохранения порядка:', err);
        }
    }

    /**
     * Копирует исходную ссылку в буфер обмена.
     */
    function copySourceUrl(sourceUrl, btnElement) {
        var icon = btnElement.querySelector('i');

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(sourceUrl).then(function () {
                showCopySuccess(icon, btnElement);
            }).catch(function () {
                fallbackCopy(sourceUrl, icon, btnElement);
            });
        } else {
            fallbackCopy(sourceUrl, icon, btnElement);
        }
    }

    /**
     * Fallback-копирование через textarea (для старых браузеров и мобильных).
     */
    function fallbackCopy(text, icon, btnElement) {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showCopySuccess(icon, btnElement);
        } catch (err) {
            alert('Не удалось скопировать. Ссылка:\n' + text);
        }
        document.body.removeChild(textarea);
    }

    /**
     * Визуальная обратная связь при успешном копировании.
     */
    function showCopySuccess(icon, btnElement) {
        icon.className = 'bi bi-check-lg';
        btnElement.classList.add('copied');

        // Toast-уведомление
        var toastEl = document.getElementById('video-copy-toast');
        if (toastEl && typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            var toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 1500 });
            toast.show();
        }

        setTimeout(function () {
            icon.className = 'bi bi-clipboard';
            btnElement.classList.remove('copied');
        }, 1500);
    }

    /**
     * Открывает модальное окно с видеоплеером.
     * Атрибуты allow и referrerpolicy берутся из данных провайдера (хранятся в БД).
     */
    function openVideoPreview(iframeUrl, iframeAllow, iframeReferrerPolicy) {
        var iframe = document.getElementById('video-preview-iframe');
        if (!iframe) {
            alert('Элемент iframe#video-preview-iframe не найден — возможно, расширение браузера удалило его.');
            return;
        }

        // Устанавливаем атрибуты iframe из данных провайдера
        if (iframeAllow) {
            iframe.setAttribute('allow', iframeAllow);
        } else {
            iframe.removeAttribute('allow');
        }
        if (iframeReferrerPolicy) {
            iframe.setAttribute('referrerpolicy', iframeReferrerPolicy);
        } else {
            iframe.removeAttribute('referrerpolicy');
        }

        iframe.src = iframeUrl;

        var modalEl = document.getElementById('video-preview-modal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }

        // Остановить видео при закрытии модалки
        modalEl.addEventListener('hidden.bs.modal', function () {
            iframe.src = 'about:blank';
            iframe.removeAttribute('allow');
            iframe.removeAttribute('referrerpolicy');
        }, { once: true });
    }

    function appendVideoCard(data) {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-sm-6 video-card';
        col.dataset.videoId = data.id;
        col.dataset.iframeUrl = data.iframe_url;
        col.dataset.iframeAllow = data.iframe_allow || '';
        col.dataset.iframeReferrerpolicy = data.iframe_referrerpolicy || '';
        col.dataset.sourceUrl = data.source_url;
        col.innerHTML =
            '<div class="card h-100">' +
            '  <div class="video-thumbnail-wrap video-play-btn" role="button" title="Смотреть видео">' +
            '    <img src="' + escapeHtml(data.thumbnail_url) + '" class="card-img-top" alt="' + escapeHtml(data.provider_type) + '" loading="lazy">' +
            '    <div class="video-play-icon"><i class="bi bi-play-circle-fill"></i></div>' +
            '  </div>' +
            '  <div class="card-body p-2">' +
            '    <div class="d-flex align-items-center gap-1 mb-1">' +
            '      <span class="badge bg-secondary">' + escapeHtml(data.provider_type) + '</span>' +
            '      <button type="button" class="btn btn-sm btn-outline-secondary video-copy-btn p-0 px-1 border-0" title="Скопировать исходную ссылку"><i class="bi bi-clipboard"></i></button>' +
            '    </div>' +
            '    <small class="d-block text-muted text-truncate" title="' + escapeHtml(data.source_url) + '">' + escapeHtml(data.source_url) + '</small>' +
            '  </div>' +
            '  <div class="card-footer p-2 d-flex gap-1">' +
            '    <button type="button" class="btn btn-sm btn-outline-secondary video-move-up-btn" title="Переместить выше"><i class="bi bi-arrow-up"></i></button>' +
            '    <button type="button" class="btn btn-sm btn-outline-secondary video-move-down-btn" title="Переместить ниже"><i class="bi bi-arrow-down"></i></button>' +
            '    <button type="button" class="btn btn-sm btn-outline-info video-verify-btn" title="Проверить работоспособность"><i class="bi bi-check-circle"></i></button>' +
            '    <button type="button" class="btn btn-sm btn-outline-primary video-refresh-btn" title="Обновить данные"><i class="bi bi-arrow-clockwise"></i></button>' +
            '    <button type="button" class="btn btn-sm btn-outline-danger video-delete-btn ms-auto" title="Удалить"><i class="bi bi-trash"></i></button>' +
            '  </div>' +
            '  <div class="video-status-overlay" style="display: none;"></div>' +
            '</div>';
        grid.appendChild(col);
    }

    function showAddError(message) {
        addError.textContent = message;
        addError.style.display = '';
    }

    function hideAddError() {
        addError.style.display = 'none';
    }
}

/**
 * Инициализация страницы тестирования парсинга.
 */
function initTestParse(testBtn) {
    const urlInput = document.getElementById('test-parse-url');
    const loading = document.getElementById('test-parse-loading');
    const errorBlock = document.getElementById('test-parse-error');
    const resultBlock = document.getElementById('test-parse-result');
    const thumbnailImg = document.getElementById('test-result-thumbnail');
    const thumbnailUrlCode = document.getElementById('test-result-thumbnail-url');
    const iframeUrlCode = document.getElementById('test-result-iframe-url');
    const endpoint = testBtn.dataset.endpoint;
    const csrfParam = testBtn.dataset.csrfParam;
    const csrfToken = testBtn.dataset.csrfToken;

    /**
     * Возвращает актуальный iframe-элемент.
     * Пересоздаёт его, если расширение браузера (напр. Privacy Badger) подменило оригинал.
     */
    function getOrRestoreIframe() {
        var existing = document.getElementById('test-result-iframe');
        if (existing) {
            return existing;
        }
        var container = resultBlock.querySelector('.ratio');
        if (!container) {
            return null;
        }
        container.innerHTML = '';
        var iframe = document.createElement('iframe');
        iframe.id = 'test-result-iframe';
        iframe.setAttribute('frameborder', '0');
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('allow', 'autoplay; encrypted-media');
        container.appendChild(iframe);
        return iframe;
    }

    testBtn.addEventListener('click', function () {
        const url = urlInput.value.trim();
        if (!url) return;
        testParse(url);
    });

    urlInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            testBtn.click();
        }
    });

    async function testParse(url) {
        errorBlock.style.display = 'none';
        resultBlock.style.display = 'none';
        loading.style.display = '';
        testBtn.disabled = true;

        var result;
        try {
            result = await postJson(endpoint, { url: url }, csrfParam, csrfToken);
        } catch (err) {
            errorBlock.textContent = err.message;
            errorBlock.style.display = '';
            loading.style.display = 'none';
            testBtn.disabled = false;
            return;
        }

        loading.style.display = 'none';
        testBtn.disabled = false;

        if (result.status !== 'success') {
            errorBlock.textContent = 'Ошибка сервера: ' + (result.message || 'Неизвестная ошибка.');
            errorBlock.style.display = '';
            return;
        }

        // Отображение результата — ошибки здесь не связаны с сетью
        var warnings = [];

        if (thumbnailImg) {
            thumbnailImg.src = result.data.thumbnail_url;
        } else {
            warnings.push('Элемент превью (img#test-result-thumbnail) не найден в DOM');
        }
        if (thumbnailUrlCode) {
            thumbnailUrlCode.textContent = result.data.thumbnail_url;
        }

        var iframe = getOrRestoreIframe();
        if (iframe) {
            iframe.src = result.data.iframe_url;
        } else {
            warnings.push('Не удалось создать iframe — возможно, расширение браузера блокирует встраивание');
        }
        if (iframeUrlCode) {
            iframeUrlCode.textContent = result.data.iframe_url;
        }

        resultBlock.style.display = '';

        if (warnings.length > 0) {
            errorBlock.textContent = 'Данные получены, но: ' + warnings.join('; ');
            errorBlock.style.display = '';
        }
    }
}

// --- Утилиты ---

/**
 * POST-запрос с JSON-ответом.
 */
async function postJson(url, data, csrfParam, csrfToken) {
    const params = new URLSearchParams();
    params.append(csrfParam, csrfToken);
    for (const key in data) {
        if (data.hasOwnProperty(key)) {
            var value = data[key];
            if (Array.isArray(value)) {
                for (var i = 0; i < value.length; i++) {
                    params.append(key, value[i]);
                }
            } else {
                params.append(key, value);
            }
        }
    }

    var response;
    try {
        response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With-Fetch': 'true',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString(),
        });
    } catch (err) {
        throw new Error('Сеть недоступна или запрос заблокирован: ' + err.message);
    }

    if (!response.ok) {
        throw new Error('HTTP ' + response.status + ' ' + response.statusText + ' (' + url + ')');
    }

    try {
        return await response.json();
    } catch (err) {
        throw new Error('Сервер вернул не-JSON ответ (HTTP ' + response.status + '): ' + err.message);
    }
}

/**
 * Экранирование HTML.
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
