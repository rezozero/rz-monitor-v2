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
 * @file CrawlWebsiteConsumer.php
 * @author Ambroise Maupate
 */
namespace MessagingBundle\Consumer;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerAware;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use MessagingBundle\Message\WebsiteMessage;

/**
 * CrawlWebsiteConsumer.
 */
class CrawlWebsiteConsumer extends ContainerAware implements ConsumerInterface
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 2.0,
        ]);
        $this->client->setDefaultOption('verify', false);
    }
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
        // Initialize
        $website = new WebsiteMessage($msg->body);
        echo "Crawling: " . $website->getUrl() . PHP_EOL;

        try {
            $res = $this->client->get($website->getUrl());
            if ($res->getStatusCode() == 200) {
                $this->declareSuccess($website);
                return true;
            } else {
                $this->declareBroken($website);
                return true;
            }
        } catch (RequestException $e) {
            $this->declareBroken($website);
            return true;
        }
    }

    protected function declareSuccess(WebsiteMessage $website)
    {
        $website->setStatus(WebsiteMessage::STATUS_OK);
        $producer = $this->container->get('old_sound_rabbit_mq.notify_website_producer');
        return $producer->publish($website);
    }

    protected function declareBroken(WebsiteMessage $website)
    {
        $website->setStatus(WebsiteMessage::STATUS_FAIL);
        $producer = $this->container->get('old_sound_rabbit_mq.notify_website_producer');
        return $producer->publish($website);
    }
}
