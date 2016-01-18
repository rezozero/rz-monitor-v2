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
 * @file CrawlAllCommand.php
 * @author Ambroise Maupate
 */
namespace WebsiteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlAllCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('website:crawl-all')
            ->setDescription('Crawl all registered websites in RZMonitor')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $text = '<info>Requesting crawl for every registered websites…</info>';

        $em = $this->getContainer()->get('doctrine')->getManager();
        $websites = $em->getRepository('WebsiteBundle:Website')->findAll();

        foreach ($websites as $website) {
            $producer = $this->getContainer()->get('old_sound_rabbit_mq.crawl_website_producer');
            $producer->publish($website->getMessage());
        }

        $output->writeln($text);
    }
}
