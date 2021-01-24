# selenium-stealth 

A php package **selenium-stealth** to prevent detection.

As of now selenium-stealth **only support Selenium Chrome/Chromium**.

After using selenium-stealth you can prevent almost all selenium detections. There is a lot of guides on stackoverflow on How to prevent selenium detection but I can not find a single python package for it so I am just creating one after all we can't let the cats win.
It can be seen as a re-implementation of JavaScript [puppeteer-extra-plugin-stealth](https://github.com/berstend/puppeteer-extra/tree/master/packages/puppeteer-extra-plugin-stealth) developed by [@berstend](https://github.com/berstend>).


## Install
```
$ composer require sapistudio/seleniumstealth
```

## Usage
For now , it can run with php-webdriver or laravel-panther client
for php-webdriver
```php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use SapiStudio\SeleniumStealth\SeleniumStealth;

// Chrome
$driver = RemoteWebDriver::create($serverUrl, DesiredCapabilities::chrome());
$driver = (new SeleniumStealth(driver))->usePhpWebriverClient()->makeStealth();

```
for laravel panther
```php
use Symfony\Component\Panther\Client;
use SapiStudio\SeleniumStealth\SeleniumStealth;

// Chrome
$driver = Client::createChromeClient();
$driver = (new SeleniumStealth(driver))->makeStealth();

```
After this you run your usual commands with the driver
