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
 * @file WebsiteMessage.php
 * @author Ambroise Maupate
 */
namespace MessagingBundle\Message;

use WebsiteBundle\Entity\Website;

/**
 * WebsiteMessage.
 */
class WebsiteMessage
{
    protected $id;
    protected $name;
    protected $url;
    protected $status = Website::STATUS_OK;
    protected $generator;
    protected $responseTime;
    protected $httpCode;
    protected $failMessage;

    /**
     * @param string $json
     */
    public function __construct($json = '')
    {
        if (is_string($json) && $json != '') {
            $body = json_decode($json, true);
            $has = get_object_vars($this);
            $fields = array_keys($has);

            foreach ($body as $name => $value) {
                if (in_array($name, $fields)) {
                    $this->$name = $value;
                }
            }
        }
    }

    /**
     * Gets the value of url.
     *
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the value of url.
     *
     * @param mixed $url the url
     *
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function __toString()
    {
        return json_encode(get_object_vars($this));
    }

    /**
     * Gets the value of status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param int $status the status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = (int) $status;

        return $this;
    }

    public function isUp()
    {
        return $this->status === Website::STATUS_OK;
    }

    /**
     * Gets the value of id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param mixed $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * Gets the value of name.
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param mixed $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of generator.
     *
     * @return mixed
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    /**
     * Sets the value of generator.
     *
     * @param mixed $generator the generator
     *
     * @return self
     */
    public function setGenerator($generator)
    {
        $this->generator = $generator;

        return $this;
    }

    /**
     * Gets the value of responseTime.
     *
     * @return mixed
     */
    public function getResponseTime()
    {
        return $this->responseTime;
    }

    /**
     * Sets the value of responseTime.
     *
     * @param mixed $responseTime the response time
     *
     * @return self
     */
    public function setResponseTime($responseTime)
    {
        $this->responseTime = $responseTime;

        return $this;
    }

    /**
     * Gets the value of httpCode.
     *
     * @return mixed
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Sets the value of httpCode.
     *
     * @param mixed $httpCode the http code
     *
     * @return self
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    /**
     * Gets the value of failMessage.
     *
     * @return mixed
     */
    public function getFailMessage()
    {
        return $this->failMessage;
    }

    /**
     * Sets the value of failMessage.
     *
     * @param mixed $failMessage the fail message
     *
     * @return self
     */
    public function setFailMessage($failMessage)
    {
        $this->failMessage = $failMessage;

        return $this;
    }
}
