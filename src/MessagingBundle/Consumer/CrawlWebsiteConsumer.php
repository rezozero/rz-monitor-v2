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
use MessagingBundle\Message\WebsiteMessage;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use WebsiteBundle\Entity\Website;

/**
 * CrawlWebsiteConsumer.
 */
class CrawlWebsiteConsumer implements ConsumerInterface
{
    use ContainerAwareTrait;

    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 3.0,
            'defaults' => array(
                'headers' => array(
                    'DNT' => 1,
                    'Cache-Control' => 'no-cache',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36'
                ),
            ),
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
        $website = new WebsiteMessage($msg->body);
        $logger = $this->container->get('logger');
        $logger->debug("[website] Crawling: " . $website->getUrl() . "…");

        try {
            $startTime = microtime(true);
            $res = $this->client->get($website->getUrl());
            $endTime = microtime(true);

            $website->setHttpCode($res->getStatusCode());
            $website->setResponseTime($endTime - $startTime);
            $website->setDatetime(new \Datetime('now'));

            if ($res->getStatusCode() == 200) {
                $website->setStatus(Website::STATUS_OK);
                $website->setGenerator($this->getResponseGenerator($res));
                $this->produceNotification($website);
                return true;
            } else {
                $website->setStatus(Website::STATUS_FAIL);
                $website->setFailMessage($res->getReasonPhrase());
                $this->produceNotification($website);
                return true;
            }
        } catch (RequestException $e) {
            $website->setStatus(Website::STATUS_FAIL);
            $website->setFailMessage($e->getMessage());
            $this->produceNotification($website);
            return true;
        }
    }

    protected function produceNotification(WebsiteMessage $website)
    {
        $producer = $this->container->get('old_sound_rabbit_mq.notify_website_producer');
        return $producer->publish($website);
    }

    /**
     * @param  [type] $res
     * @return string|null
     */
    protected function getResponseGenerator($res)
    {
        $body = $res->getBody();
        $cmsVersion = array();
        if (preg_match("/\<meta name\=\"generator\" content\=\"([^\"]+)\"/", (string) $body, $cmsVersion) > 0) {
            return $cmsVersion[1];
        }

        return null;
    }
}
