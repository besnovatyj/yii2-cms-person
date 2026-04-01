<?php

use yii\web\View;
use Besnovatyj\Person\thumbnails\VideoData;

/** @var $videoObjects VideoData[] */
/** @var $this View */

if (empty($videoObjects)) {
    return 'Попробуйте открыть видео еще раз...';
}
?>

<div class="row">
    <?php foreach ($videoObjects as $videoObj): ?>
        <div class="col-4 img-container" data-url="<?= $videoObj->iframeUrl ?>">
            <img class="img-fluid m-1"
                 src="<?= $videoObj->thumbnailUrl ?>"
                 data-url="<?= $videoObj->iframeUrl ?>" alt=""/>
            <i class="fa-solid fa-circle-play" data-url="<?= $videoObj->iframeUrl ?>"></i>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="videoModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered " role="document">
        <div class="modal-content">
            <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="embed-responsive embed-responsive-16by9">
                <iframe
                        class="embed-responsive-item"
                        loading="lazy"
                        scrolling="no"
                        frameBorder="0"
                        width="100%"
                        height="100%"
                        allow="clipboard-write; autoplay; fullscreen; accelerometer; gyroscope; picture-in-picture; encrypted-media"
                        src=""
                ></iframe>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show" id="backdrop" style="display: none;"></div>






























