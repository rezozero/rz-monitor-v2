RZ Monitor v2
=============

A crawling tool to check websites health written with Symfony and using 
*RabbitMQ* to dispatch *page crawls* and *notifications*.

## Launch consumers

* `php app/console rabbitmq:consumer -w notify_website` 
* `php app/console rabbitmq:consumer -w crawl_website` 

## Register a crontab on “crawl-all” Command

* `php /var/www/app/console website:crawl-all`

When a website has failed, it will be tried until it is declared as broken (after 3 times failed).
