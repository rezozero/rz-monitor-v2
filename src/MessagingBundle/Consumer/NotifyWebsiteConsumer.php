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
 * @file NotifyWebsiteConsumer.php
 * @author Ambroise Maupate
 */
namespace MessagingBundle\Consumer;

use MessagingBundle\Message\WebsiteMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use WebsiteBundle\Entity\Website;

/**
 * NotifyWebsiteConsumer.
 */
class NotifyWebsiteConsumer implements ConsumerInterface
{
    use ContainerAwareTrait;
    /**
     *  Main execute method
     *  Execute actions for a given message
     *
     *  @param (AMQPMessage) $msg       An instance of `PhpAmqpLib\Message\AMQPMessage` with the $msg->body being the data sent over RabbitMQ.
     *
     *  @return (boolean) Execution status (true if everything's of, false if message should be re-queued)
     */
    public function execute(AMQPMessage $msg)
    {
        $logger = $this->container->get('logger');
        $em = $this->container->get('doctrine')->getManager();
        $message = new WebsiteMessage($msg->body);
        $websiteEntity = $em->getRepository('WebsiteBundle\Entity\Website')
            ->findOneWithMessage($message);

        if (null === $websiteEntity) {
            throw new \RuntimeException("Website does not exists in database.", 1);
        } else {
            if ($websiteEntity->getStatus() === $message->getStatus()) {
                $websiteEntity->setCrawlCount($websiteEntity->getCrawlCount() + 1);
                /*
                 * Need to be declared failed twice to be broken.
                 */
                if ($message->getStatus() === Website::STATUS_FAIL &&
                    $websiteEntity->getCrawlCount() > 2) {
                    $websiteEntity->setStatus(Website::STATUS_BROKEN);
                } else {
                    $websiteEntity->setStatus($message->getStatus());
                }
            } else {
                /*
                 * If website is broken and crawling still failing.
                 * Still broken.
                 */
                if ($message->getStatus() === Website::STATUS_FAIL &&
                    $websiteEntity->getStatus() === Website::STATUS_BROKEN) {
                    $websiteEntity->setStatus(Website::STATUS_BROKEN);
                } else {
                    /*
                     * Reset crawl count when status changes.
                     */
                    $websiteEntity->setStatus($message->getStatus());
                    $websiteEntity->setCrawlCount(1);
                    $websiteEntity->setAvgResponseTime(0);
                }
            }

            $websiteEntity->setLastResponseTime($message->getResponseTime());
            $websiteEntity->setLastCrawl(new \Datetime('now'));
            $websiteEntity->setHttpCode($message->getHttpCode());
            $websiteEntity->setGenerator($message->getGenerator());

            $em->flush();
        }

        if ($message->isUp()) {
            $logger->info("[website] " . $message->getUrl() . " is up.");
        } else {
            $logger->info("[website] " . $message->getUrl() . " (" . $message->getFailMessage() . ") is down.");
        }

        return true;
    }
}
