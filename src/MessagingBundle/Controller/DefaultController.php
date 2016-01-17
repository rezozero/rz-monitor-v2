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
 * @file DefaultController.php
 * @author Ambroise Maupate
 */
namespace MessagingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MessagingBundle\Message\WebsiteMessage;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $sayHelloProducer = $this->get('old_sound_rabbit_mq.say_hello_producer');
        $sayHelloProducer->publish('Hello!');

        return $this->render('MessagingBundle:Default:index.html.twig');
    }

    public function testCrawlAction()
    {
        $website = new WebsiteMessage();
        $website->setUrl('http://www.google.com');

        $sayHelloProducer = $this->get('old_sound_rabbit_mq.crawl_website_producer');
        $sayHelloProducer->publish($website);

        return $this->render('MessagingBundle:Default:index.html.twig');
    }

    public function testCrawlFailAction()
    {
        $website = new WebsiteMessage();
        $website->setUrl('http://www.google.nonexstingltd');

        $sayHelloProducer = $this->get('old_sound_rabbit_mq.crawl_website_producer');
        $sayHelloProducer->publish($website);

        return $this->render('MessagingBundle:Default:index.html.twig');
    }
}
