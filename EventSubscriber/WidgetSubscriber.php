<?php

/*
 * This file is part of the "AsyntaiChatbotBundle" for Kimai.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\AsyntaiChatbotBundle\EventSubscriber;

use App\Event\ThemeEvent;
use KimaiPlugin\AsyntaiChatbotBundle\Service\AsyntaiSettings;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Puts the chat widget on Kimai pages.
 *
 * The HTML_HEAD event fires on the normal layout and on the login page. The
 * script is added only after the page "load" event: an async script tag still
 * holds the load event open until its fetch ends, so an unreachable Asyntai
 * server would otherwise delay Kimai pages.
 */
final class WidgetSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly AsyntaiSettings $settings)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ThemeEvent::HTML_HEAD => ['addWidget', 0],
        ];
    }

    public function addWidget(ThemeEvent $event): void
    {
        $siteId = $this->settings->getSiteId();
        if ($siteId === '') {
            return;
        }

        $show = $event->getUser() !== null ? $this->settings->showToUsers() : $this->settings->showOnLogin();
        if (!$show) {
            return;
        }

        $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES;
        $id = json_encode($siteId, $flags);
        $src = json_encode($this->settings->getScriptUrl(), $flags);

        $event->addContent(<<<HTML
<script>
(function () {
  function inject() {
    if (document.querySelector('script[data-asyntai-id]')) { return; }
    var s = document.createElement('script');
    s.src = {$src};
    s.async = true;
    s.setAttribute('data-asyntai-id', {$id});
    document.head.appendChild(s);
  }
  if (document.readyState === 'complete') { inject(); } else { window.addEventListener('load', inject); }
})();
</script>
HTML);
    }
}
