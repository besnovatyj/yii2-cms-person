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
            showAddError('Ошибка сети: ' + err.message);
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
            alert('Ошибка сети: ' + err.message);
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
            overlay.textContent = 'Ошибка сети';
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
                overlay.textContent = 'Обновлено';
                overlay.className = 'video-status-overlay success';
            } else {
                overlay.textContent = result.message || 'Ошибка';
                overlay.className = 'video-status-overlay error';
            }
        } catch (err) {
            overlay.textContent = 'Ошибка сети';
            overlay.className = 'video-status-overlay error';
        }

        setTimeout(function () {
            overlay.style.display = 'none';
        }, 3000);
    }

    function appendVideoCard(data) {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-sm-6 video-card';
        col.dataset.videoId = data.id;
        col.innerHTML =
            '<div class="card h-100">' +
            '  <div class="video-thumbnail-wrap">' +
            '    <img src="' + escapeHtml(data.thumbnail_url) + '" class="card-img-top" alt="' + escapeHtml(data.provider_type) + '" loading="lazy">' +
            '  </div>' +
            '  <div class="card-body p-2">' +
            '    <span class="badge bg-secondary">' + escapeHtml(data.provider_type) + '</span>' +
            '    <small class="d-block text-muted text-truncate mt-1" title="' + escapeHtml(data.source_url) + '">' + escapeHtml(data.source_url) + '</small>' +
            '  </div>' +
            '  <div class="card-footer p-2 d-flex gap-1">' +
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
    const endpoint = testBtn.dataset.endpoint;
    const csrfParam = testBtn.dataset.csrfParam;
    const csrfToken = testBtn.dataset.csrfToken;

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

        try {
            const result = await postJson(endpoint, { url: url }, csrfParam, csrfToken);

            if (result.status === 'success') {
                document.getElementById('test-result-thumbnail').src = result.data.thumbnail_url;
                document.getElementById('test-result-thumbnail-url').textContent = result.data.thumbnail_url;
                document.getElementById('test-result-iframe').src = result.data.iframe_url;
                document.getElementById('test-result-iframe-url').textContent = result.data.iframe_url;
                resultBlock.style.display = '';
            } else {
                errorBlock.textContent = result.message || 'Неизвестная ошибка.';
                errorBlock.style.display = '';
            }
        } catch (err) {
            errorBlock.textContent = 'Ошибка сети: ' + err.message;
            errorBlock.style.display = '';
        } finally {
            loading.style.display = 'none';
            testBtn.disabled = false;
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
            params.append(key, data[key]);
        }
    }

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With-Fetch': 'true',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString(),
    });

    return await response.json();
}

/**
 * Экранирование HTML.
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
