<?php
/**
 * Copyright © 2016, Ambroise Maupate
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @file RetryCrawlSubscriber.php
 * @author Ambroise Maupate
 */
namespace WebsiteBundle\Event;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WebsiteBundle\Event\WebsiteEvent;

/**
 * Produce a new crawl request if website has failed until
 * it’s broken or back to normal.
 */
class RetryCrawlSubscriber implements EventSubscriberInterface
{
    use ContainerAwareTrait;

    public static function getSubscribedEvents()
    {
        return array(
            WebsiteEvent::HAS_FAILED => 'retryCrawl',
        );
    }

    public function retryCrawl(WebsiteEvent $event)
    {
        $website = $event->getWebsite();
        $producer = $this->container->get('old_sound_rabbit_mq.crawl_website_producer');
        $producer->publish($website->getMessage());
    }
}
