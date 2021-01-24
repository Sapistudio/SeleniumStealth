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
After initializing your desired webdriver client,run stelath command
```python
$this->client = (new \SapiStudio\SeleniumStealth\SeleniumStealth($this->client))->usePhpWebriverClient()->makeStealth();

# options.add_argument("--headless")

options.add_experimental_option("excludeSwitches", ["enable-automation"])
options.add_experimental_option('useAutomationExtension', False)
driver = webdriver.Chrome(options=options, executable_path=r"C:\Users\DIPRAJ\Programming\adclick_bot\chromedriver.exe")

stealth(driver,
        languages=["en-US", "en"],
        vendor="Google Inc.",
        platform="Win32",
        webgl_vendor="Intel Inc.",
        renderer="Intel Iris OpenGL Engine",
        fix_hairline=True,
        )

url = "https://bot.sannysoft.com/"
driver.get(url)
time.sleep(5)
driver.quit()
```

## Args

```python
stealth(
    driver: Driver,
    user_agent: str = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.53 Safari/537.36',
    languages: [str] = ["en-US", "en"],
    vendor: str = "Google Inc.",
    platform: str = "Win32",
    webgl_vendor: str = "Intel Inc.",
    renderer: str = "Intel Iris OpenGL Engine",
    fix_hairline: bool = False,
    run_on_insecure_origins: bool = False,
)
```

## Test results (red is bad)

### Selenium without <strong>selenium-stealth 😢</strong>

<table class="image">
<tr>
  <td><figure class="image"><a href="https://raw.githubusercontent.com/diprajpatra/selenium-stealth/main/stealthtests/selenium_chrome_headless_without_stealth.png"><img src="https://raw.githubusercontent.com/diprajpatra/selenium-stealth/main/stealthtests/selenium_chrome_headless_without_stealth.png"></a><figcaption>headless</figcaption></figure></td>
  <td><figure class="image"><a href="https://raw.githubusercontent.com/diprajpatra/selenium-stealth/main/stealthtests/selenium_chrome_headful_without_stealth.png"><img src="https://raw.githubusercontent.com/diprajpatra/selenium-stealth/main/stealthtests/selenium_chrome_headful_without_stealth.png"></a><figcaption>headful</figcaption></figure></td>
</tr>
</table>

### Selenium with <strong>selenium-stealth 💯</strong>

<table class="image">
<tr>
  <td><figure class="image"><a href="https://raw.githubusercontent.com/diprajpatra/selenium-stealth/main/stealthtests/selenium_chrome_headless_with_stealth.png"><img src="https://raw.githubusercontent.com/diprajpatra/selenium-stealth/main/stealthtests/selenium_chrome_headless_with_stealth.png"></a><figcaption>headless</figcaption></figure></td>
  <td><figure class="image"><a href="https://raw.githubusercontent.com/diprajpatra/selenium-stealth/main/stealthtests/selenium_chrome_headful_with_stealth.png"><img src="https://raw.githubusercontent.com/diprajpatra/selenium-stealth/main/stealthtests/selenium_chrome_headful_with_stealth.png"></a><figcaption>headful</figcaption></figure></td>
</tr>
</table>

## License

Copyright © 2020, [diprajpatra](https://github.com/diprajpatra). Released under the MIT License.
