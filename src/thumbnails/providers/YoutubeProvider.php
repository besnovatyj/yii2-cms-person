<?php

namespace Besnovatyj\Person\thumbnails\providers;

class YoutubeProvider extends AbstractProvider
{
    protected const string HOST = 'youtu';

    // https://www.youtube.com/watch?v=gxFLg9d_-9k
    // <iframe src="https://www.youtube.com/embed/Dzyn8Y-1Itg" ></iframe>

    // https://stackoverflow.com/questions/5830387/how-do-i-find-all-youtube-video-ids-in-a-string-using-a-regex
    protected array $regexes = [ // 'matchNumber' => 'regexp'
        '#https?://(?:[0-9A-Z-]+\.)?(?:youtu\.be/|youtube(?:-nocookie)?\.com\S*?[^\w\s-])([\w-]{11})(?=[^\w-]|$)[?=&+%\w.-]*#i', //1
        '~(?#!js YouTubeId Rev:20160125_1800)
                          # Match non-linked youtube URL in the wild. (Rev:20130823)
                          https?://          # Required scheme. Either http or https.
                          (?:[0-9A-Z-]+\.)?  # Optional subdomain.
                          (?:                # Group host alternatives.
                            youtu\.be/       # Either youtu.be,
                          | youtube          # or youtube.com or
                            (?:-nocookie)?   # youtube-nocookie.com
                            \.com            # followed by
                            \S*?             # Allow anything up to VIDEO_ID,
                            [^\w\s-]         # but char before ID is non-ID char.
                          )                  # End host alternatives.
                          ([\w-]{11})        # $1: VIDEO_ID is exactly 11 chars.
                          (?=[^\w-]|$)       # Assert next char is non-ID or EOS.
                          (?!                # Assert URL is not pre-linked.
                            [?=&+%\w.-]*     # Allow URL (query) remainder.
                            (?:              # Group pre-linked alternatives.
                              [\'"][^<>]*>   # Either inside a start tag,
                            | </a>           # or inside <a> element text contents.
                            )                # End recognized pre-linked alts.
                          )                  # End negative lookahead assertion.
                          [?=&+%\w.-]*       # Consume any URL (query) remainder.
                          ~ix',
        // i modifier: insensitive. Case insensitive match (ignores case of [a-zA-Z])
        // x modifier: extended. Spaces and text after a # in the pattern are ignored
    ]; //https://youtu.be/hgfeNWI4hyQ

    protected function resolveThumbnailUrl(string $match): string
    {
        // https://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api
        // https://i.ytimg.com/vi/lqj-QNYsZFk/maxresdefault.jpg // hqdefault
        // https://img.youtube.com/vi/F-K4UL2yX04/sddefault.jpg // mqdefault
        return 'https://img.youtube.com/vi/' . $match . '/mqdefault.jpg';
    }

    protected function resolveIframeUrl(string $markup, string $match): string
    {
        // <iframe src="https://www.youtube.com/embed/Dzyn8Y-1Itg" ></iframe>
        return 'https://www.youtube.com/embed/' . $match;
    }

    /**
     * {@inheritdoc}
     */
    protected function getIframeAllow(): string
    {
        return 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
    }

    /**
     * {@inheritdoc}
     */
    protected function getIframeReferrerPolicy(): string
    {
        return 'strict-origin-when-cross-origin';
    }
}
