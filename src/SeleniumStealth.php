<?php
namespace SapiStudio\SeleniumStealth;
use Illuminate\Filesystem\Filesystem;

class SeleniumStealth
{
    const PANTHER_CLIENT_TYPE       = 'panther';
    const PHPWEBDRIVER_CLIENT_TYPE  = 'php_webdriver';
    
    protected static $mainArgsNames = [
        'driver'                    => null,
        'user_agent'                => null,
        'languages'                 => ["en-US", "en"],
        'vendor'                    => "Google Inc.",
        'platform'                  => null,
        'webgl_vendor'              => "Intel Inc.",
        'renderer'                  => "Intel Iris OpenGL Engine",
        'fix_hairline'              => false,
        'run_on_insecure_origins'   => false
    ];

    public function __set($name, $value)
    {
        self::$mainArgsNames[$name] = $value;
        return $this;
    }

    public function __get($name)
    {
        return (isset(self::$mainArgsNames[$name])) ? self::$mainArgsNames[$name] : false;
    }
    
    public function __construct()
    {
        $arguments  = func_get_args();
        $customArgs = [];
        if ($arguments) {
            $keys = array_keys(self::$mainArgsNames);
            $values = array_values(self::$mainArgsNames);
            foreach ($arguments as $indexId => $arg) {
                if (array_key_exists($indexId, $values))
                    $values[$indexId] = $arg;
                else
                    $customArgs[] = $arg;
            }
            self::$mainArgsNames = array_combine($keys, $values);
        }
        if ($customArgs)
            self::$mainArgsNames['kWargs'] = $customArgs;
        $this->filesystem   = new Filesystem();
        $this->ua_languages = implode(',',$this->languages);
        $this->jsPath = dirname(__DIR__).DIRECTORY_SEPARATOR .'js'.DIRECTORY_SEPARATOR;
    }
    
    public function usePantherClient(){
        $this->currentClientType = self::PANTHER_CLIENT_TYPE;
        if (!$this->driver instanceof \Symfony\Component\Panther\Client)
            throw new \Exception('This is not a panther client');
        return $this;
    }
    
    public function usePhpWebriverClient(){
        $this->currentClientType = self::PHPWEBDRIVER_CLIENT_TYPE;
        if (!$this->driver instanceof \Facebook\WebDriver\Remote\RemoteWebDriver)
            throw new \Exception('This is not a php webdriver client');
        return $this;
    }
    
    public function makeStealth(){
        if(!$this->currentClientType)
            $this->usePantherClient();
        $this->with_utils();
        $this->chrome_app();
        $this->chrome_runtime();
        $this->iframe_content_window();
        $this->media_codecs();
        $this->navigator_languages();
        $this->navigator_permissions();
        $this->navigator_plugins();
        $this->navigator_vendor();
        $this->navigator_webdriver();
        $this->user_agent_override();
        $this->webgl_vendor_override();
        $this->window_outerdimensions();
        return $this->driver;
    }
    
    protected function with_utils()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get($this->jsPath."utils.js"));
    }

    protected function chrome_app()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get($this->jsPath."chrome.app.js"));
    }

    protected function chrome_runtime()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get($this->jsPath."chrome.runtime.js"),$this->run_on_insecure_origins);
    }

    protected function iframe_content_window()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get($this->jsPath."iframe.contentWindow.js"));
    }

    protected function media_codecs()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get($this->jsPath."media.codecs.js"));
    }

    protected function navigator_languages()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get($this->jsPath."navigator.languages.js"),$this->languages);
    }

    protected function navigator_permissions()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get($this->jsPath."navigator.permissions.js"));
    }

    protected function navigator_plugins()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get($this->jsPath."navigator.plugins.js"));
    }

    protected function navigator_vendor()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get($this->jsPath."navigator.vendor.js"),$this->vendor);
    }

    protected function navigator_webdriver()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get($this->jsPath."navigator.webdriver.js"));
    }

    protected function user_agent_override()
    {
        $ua = (!$this->user_agent) ? $this->getUa() : $this->user_agent;
        $ua = str_replace("HeadlessChrome", "Chrome",$ua); # hide headless nature
        $override = new \stdClass();
        $override->userAgent = $ua;
        if($this->ua_languages)
            $override->acceptLanguage = $this->ua_languages;
        if($this->platform)
            $override->platform = $this->platform;
        $this->getDriver()->executeCustomCommand('/session/:sessionId/goog/cdp/execute','POST',['cmd' => 'Network.setUserAgentOverride', 'params' => $override]);
        return $this;
    }

    protected function webgl_vendor_override()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get("/home/admin/web/cacamaca.ro/public_html/js/webgl.vendor.js"),$this->webgl_vendor, $this->renderer);
    }

    protected function window_outerdimensions()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get("/home/admin/web/cacamaca.ro/public_html/js/window.outerdimensions.js"));
    }

    protected function hairline_fix()
    {
        return $this->evaluateOnNewDocument($this->filesystem->get("/home/admin/web/cacamaca.ro/public_html/js/hairline.fix.js"));
    }

    protected function evaluateOnNewDocument($pagefunction)
    {
        $args = func_get_args();
        array_shift($args);
        $jsCode = $this->evaluationString($pagefunction, $args);
        $this->getDriver()->executeCustomCommand('/session/:sessionId/goog/cdp/execute','POST',['cmd' => 'Page.addScriptToEvaluateOnNewDocument', 'params' => (object)['source' => $jsCode]]);
    }

    protected function evaluationString($pagefunction, $args = [])
    {
        $args = array_map(function ($a) {return var_export($a, true); }, $args);
        return '(' . $pagefunction . ')(' . implode(',', $args) . ')';
    }
    
    protected function getDriver(){
        switch($this->currentClientType){
            case self::PANTHER_CLIENT_TYPE:
                return $this->driver->getWebDriver();
                break;
            case self::PHPWEBDRIVER_CLIENT_TYPE:
                return $this->driver;
                break;
            default:
               throw new \Exception('Invalid driver client'); 
        }
    }
    
    protected function getUa(){
        //$this->getDriver()->executeCustomCommand('/session/:sessionId/goog/cdp/execute','POST',['cmd' => 'Browser.getVersion', 'params' => (object)[]])
        return $this->getDriver()->executeScript("return navigator.userAgent;");
    }
}
