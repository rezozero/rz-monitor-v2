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
 * @file WebsiteEvent.php
 * @author Ambroise Maupate
 */
namespace WebsiteBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use WebsiteBundle\Entity\Website;

/**
 * WebsiteEvent.
 */
class WebsiteEvent extends Event
{
    protected $website;

    const DECLARED_BROKEN = "website.declare_broken";
    const HAS_FAILED = "website.has_failed";
    const BACK_TO_NORMAL = "website.back_to_normal";

    public function __construct(Website $website)
    {
        $this->website = $website;
    }

    /**
     * Gets the value of website.
     *
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
