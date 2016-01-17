<?php

namespace WebsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use MessagingBundle\Message\WebsiteMessage;
/**
 * Website
 *
 * @ORM\Table(name="website")
 * @ORM\Entity(repositoryClass="WebsiteBundle\Repository\WebsiteRepository")
 */
class Website
{
    const STATUS_UNKNOWN = 0;
    const STATUS_OK = 10;
    const STATUS_FAIL = 20;
    const STATUS_BROKEN = 30;

    static $humanStatus = array(
        Website::STATUS_UNKNOWN => 'status.unknown',
        Website::STATUS_OK => 'status.ok',
        Website::STATUS_FAIL => 'status.failed',
        Website::STATUS_BROKEN => 'status.broken',
    );
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     * @Assert\Url()
     */
    private $url;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status = Website::STATUS_UNKNOWN;

    /**
     * @var string
     *
     * @ORM\Column(name="generator", type="string", length=255, nullable=true)
     */
    private $generator;

    /**
     * @var float
     *
     * @ORM\Column(name="avgResponseTime", type="float", nullable=true)
     */
    private $avgResponseTime ;

    /**
     * @var float
     *
     * @ORM\Column(name="lastResponseTime", type="float", nullable=true)
     */
    private $lastResponseTime;

    /**
     * @var int
     *
     * @ORM\Column(name="httpCode", type="integer", nullable=true)
     */
    private $httpCode;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="lastCrawl", type="datetime", nullable=true, unique=false)
     */
    private $lastCrawl;

    /**
     * @var integer
     *
     * @ORM\Column(name="crawlCount", type="integer", unique=false)
     */
    private $crawlCount = 0;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Website
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Website
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set generator
     *
     * @param string $generator
     * @return Website
     */
    public function setGenerator($generator)
    {
        $this->generator = $generator;

        return $this;
    }

    /**
     * Get generator
     *
     * @return string
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    /**
     * Set avgResponseTime
     *
     * @param float $avgResponseTime
     * @return Website
     */
    public function setAvgResponseTime($avgResponseTime)
    {
        $this->avgResponseTime = $avgResponseTime;

        return $this;
    }

    /**
     * Get avgResponseTime
     *
     * @return float
     */
    public function getAvgResponseTime()
    {
        return $this->avgResponseTime;
    }

    /**
     * Set lastResponseTime
     *
     * @param float $lastResponseTime
     * @return Website
     */
    public function setLastResponseTime($lastResponseTime)
    {
        $this->lastResponseTime = $lastResponseTime;

        return $this;
    }

    /**
     * Get lastResponseTime
     *
     * @return float
     */
    public function getLastResponseTime()
    {
        return $this->lastResponseTime;
    }

    /**
     * Set httpCode
     *
     * @param integer $httpCode
     * @return Website
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    /**
     * Get httpCode
     *
     * @return integer
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Website
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get message from entity.
     *
     * @return WebsiteMessage
     */
    public function getMessage()
    {
        $message = new WebsiteMessage();
        $message->setId($this->getId());
        $message->setUrl($this->getUrl());
        $message->setName($this->getName());
        $message->setStatus($this->getStatus());

        return $message;
    }

    /**
     * Gets the value of lastCrawl.
     *
     * @return string
     */
    public function getLastCrawl()
    {
        return $this->lastCrawl;
    }

    /**
     * Sets the value of lastCrawl.
     *
     * @param string $lastCrawl the last crawl
     *
     * @return self
     */
    public function setLastCrawl($lastCrawl)
    {
        $this->lastCrawl = $lastCrawl;

        return $this;
    }

    /**
     * Gets the value of crawlCount.
     *
     * @return integer
     */
    public function getCrawlCount()
    {
        return $this->crawlCount;
    }

    /**
     * Sets the value of crawlCount.
     *
     * @param integer $crawlCount the crawl count
     *
     * @return self
     */
    public function setCrawlCount($crawlCount)
    {
        $this->crawlCount = $crawlCount;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getHumanStatus()
    {
        return static::$humanStatus[$this->getStatus()];
    }
}
