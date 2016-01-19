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
 * @file EmailNotificationSubscriber.php
 * @author Ambroise Maupate
 */
namespace WebsiteBundle\Event;

use MessagingBundle\Consumer\NotifyWebsiteConsumer;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use WebsiteBundle\Event\WebsiteEvent;

class EmailNotificationSubscriber implements EventSubscriberInterface
{
    use ContainerAwareTrait;

    public static function getSubscribedEvents()
    {
        return array(
            WebsiteEvent::DECLARED_BROKEN => 'sendBrokenEmail',
            WebsiteEvent::BACK_TO_NORMAL => 'sendBackToNormalEmail',
        );
    }

    public function sendBrokenEmail(WebsiteEvent $event)
    {
        $website = $event->getWebsite();

        $logger = $this->container->get('logger');
        $templating = $this->container->get('templating');

        $logger->info("[email] Email sent about website (" . $website->getUrl() . ") declared broken.");

        $assignation = array(
            'emailTitle' => '[Website down] ' . $website->getUrl() . ' seems to be broken.',
            'description' => $website->getName() . ' website has been declared broken on '.$website->getLastCrawl()->format('Y/m/d H:i:s e').' after trying ' . NotifyWebsiteConsumer::RETRY_COUNT . ' times.',
        );

        $this->sendEmail($assignation, $templating->render('email/alert.html.twig', $assignation));
    }

    public function sendBackToNormalEmail(WebsiteEvent $event)
    {
        $website = $event->getWebsite();

        $logger = $this->container->get('logger');
        $templating = $this->container->get('templating');

        $logger->info("[email] Email sent about website (" . $website->getUrl() . ") back to normal.");

        $assignation = array(
            'emailTitle' => '[Website up] ' . $website->getUrl() . ' is back to normal.',
            'description' => $website->getName() . ' website is now back to normal since '.$website->getLastCrawl()->format('Y/m/d H:i:s e').'.',
        );

        $this->sendEmail($assignation, $templating->render('email/alert.html.twig', $assignation));
    }

    protected function sendEmail(array $assignation, $content)
    {
        try {
            $mailer = $this->container->get('mailer');

            $email = \Swift_Message::newInstance()
                ->setSubject($assignation['emailTitle'])
                ->setFrom($this->container->getParameter('mailer_from'))
                ->setTo($this->container->getParameter('mailer_to'))
                ->setBody($content, 'text/html')
                ->addPart(
                    $assignation['description'],
                    'text/plain'
                )
            ;
            $mailer->send($email);
        } catch (\Swift_SwiftException $e) {
            $logger = $this->container->get('logger');
            $logger->error("[email] Cannot send email about website (" . $website->getUrl() . ") back to normal. " . $e->getMessage());
        }
    }
}
