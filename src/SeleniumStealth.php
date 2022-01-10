<?php
namespace SapiStudio\SeleniumStealth;

class SeleniumStealth
{
    const PANTHER_CLIENT_TYPE       = 'panther';
    const PHPWEBDRIVER_CLIENT_TYPE  = 'php_webdriver';

    protected static $mainArgsNames = [
        'driver'                    => null,
        'user_agent'                => null,
        'languages'                 => ["en-US", "en"],
        'vendor'                    => "Google Inc.",
        'platform'                  => "Win32",
        'webgl_vendor'              => "Intel Inc.",
        'renderer'                  => "Intel Iris OpenGL Engine",
        'fix_hairline'              => false,
        'run_on_insecure_origins'   => false
    ];

    /**
     * SeleniumStealth::loadFileData()
     *
     * @return
     */
    public static function loadFileData($filepath = null){
        return (!$filepath || !is_file($filepath)) ? false : file_get_contents($filepath);
    }

    /**
     * SeleniumStealth::__set()
     *
     * @return
     */
    public function __set($name, $value)
    {
        self::$mainArgsNames[$name] = $value;
        return $this;
    }

    /**
     * SeleniumStealth::__get()
     *
     * @return
     */
    public function __get($name)
    {
        return (isset(self::$mainArgsNames[$name])) ? self::$mainArgsNames[$name] : false;
    }

    /**
     * SeleniumStealth::__construct()
     *
     * @return
     */
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
        $this->ua_languages = implode(',',$this->languages);
        $this->jsPath = dirname(__DIR__).DIRECTORY_SEPARATOR .'js'.DIRECTORY_SEPARATOR;
    }

    /**
     * SeleniumStealth::usePantherClient()
     *
     * @return
     */
    public function usePantherClient(){
        $this->currentClientType = self::PANTHER_CLIENT_TYPE;
        if (!$this->driver instanceof \Symfony\Component\Panther\Client)
            throw new \Exception('This is not a panther client');
        return $this;
    }

    /**
     * SeleniumStealth::usePhpWebriverClient()
     *
     * @return
     */
    public function usePhpWebriverClient(){
        $this->currentClientType = self::PHPWEBDRIVER_CLIENT_TYPE;
        if (!$this->driver instanceof \Facebook\WebDriver\Remote\RemoteWebDriver)
            throw new \Exception('This is not a php webdriver client');
        return $this;
    }

    /**
     * SeleniumStealth::makeStealth()
     *
     * @return
     */
    public function makeStealth(){
        if(!$this->currentClientType)
            $this->usePantherClient();
        $this->with_utils();

        $this->chrome_app();
        $this->chrome_runtime();
        $this->iframe_content_window();
        $this->media_codecs();
        //$this->navigator_languages();
        $this->navigator_permissions();
        $this->navigator_plugins();
        $this->navigator_vendor();
        $this->navigator_webdriver();
        $this->user_agent_override();
        $this->webgl_vendor_override();
        $this->window_outerdimensions();
        $this->additionalEvades();
        if($this->fix_hairline)
            $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."hairline.fix.js"));
        /** */
        return $this->driver;
    }

    /**
     * SeleniumStealth::with_utils()
     *
     * @return
     */
    protected function with_utils()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."utils.js"));
    }

    /**
     * SeleniumStealth::chrome_app()
     *
     * @return
     */
    protected function chrome_app()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."chrome.app.js"));
    }

    /**
     * SeleniumStealth::chrome_runtime()
     *
     * @return
     */
    protected function chrome_runtime()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."chrome.runtime.js"),$this->run_on_insecure_origins);
    }

    /**
     * SeleniumStealth::fixHairline()
     *
     * @return
     */
    protected function fixHairline()
    {
        $this->fix_hairline = true;
        return $this;
    }

    /**
     * SeleniumStealth::iframe_content_window()
     *
     * @return
     */
    protected function iframe_content_window()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."iframe.contentWindow.js"));
    }

    /**
     * SeleniumStealth::media_codecs()
     *
     * @return
     */
    protected function media_codecs()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."media.codecs.js"));
    }

    /**
     * SeleniumStealth::navigator_languages()
     *
     * @return
     */
    protected function navigator_languages()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."navigator.languages.js"),$this->languages);
    }

    /**
     * SeleniumStealth::navigator_permissions()
     *
     * @return
     */
    protected function navigator_permissions()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."navigator.permissions.js"));
    }

    /**
     * SeleniumStealth::navigator_plugins()
     *
     * @return
     */
    protected function navigator_plugins()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."navigator.plugins.js"));
    }

    /**
     * SeleniumStealth::navigator_vendor()
     *
     * @return
     */
    protected function navigator_vendor()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."navigator.vendor.js"),$this->vendor);
    }

    /**
     * SeleniumStealth::navigator_webdriver()
     *
     * @return
     */
    protected function navigator_webdriver()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."navigator.webdriver.js"));
    }

    /**
     * SeleniumStealth::user_agent_override()
     *
     * @return
     */
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

    /**
     * SeleniumStealth::webgl_vendor_override()
     *
     * @return
     */
    protected function webgl_vendor_override()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."webgl.vendor.js"),$this->webgl_vendor, $this->renderer);
    }

    /**
     * SeleniumStealth::window_outerdimensions()
     *
     * @return
     */
    protected function window_outerdimensions()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."window.outerdimensions.js"));
    }

    /**
     * SeleniumStealth::hairline_fix()
     *
     * @return
     */
    protected function hairline_fix()
    {
        return $this->evaluateOnNewDocument(self::loadFileData($this->jsPath."hairline.fix.js"));
    }

    /**
     * SeleniumStealth::evaluateOnNewDocument()
     *
     * @return
     */
    protected function evaluateOnNewDocument($pagefunction)
    {
        $args = func_get_args();
        array_shift($args);
        $jsCode = $this->evaluationString($pagefunction, $args);
        $this->getDriver()->executeCustomCommand('/session/:sessionId/goog/cdp/execute','POST',['cmd' => 'Page.addScriptToEvaluateOnNewDocument', 'params' => (object)['source' => $jsCode]]);
    }

    /**
     * SeleniumStealth::evaluationString()
     *
     * @return
     */
    protected function evaluationString($pagefunction, $args = [])
    {
        $args = array_map(function ($a) {return var_export($a, true); }, $args);
        return '(' . $pagefunction . ')(' . implode(',', $args) . ')';
    }

    /**
     * SeleniumStealth::getDriver()
     *
     * @return
     */
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

    /**
     * SeleniumStealth::getUa()
     *
     * @return
     */
    protected function getUa(){
        //$this->getDriver()->executeCustomCommand('/session/:sessionId/goog/cdp/execute','POST',['cmd' => 'Browser.getVersion', 'params' => (object)[]])
        return $this->getDriver()->executeScript("return navigator.userAgent;");
    }

    /**
     * @return void
     */
    protected function additionalEvades()
    {
        $this->evaluateOnNewDocument(self::loadFileData($this->jsPath . '/hook_remove_cdc_props.js'));
        $this->evaluateOnNewDocument(self::loadFileData($this->jsPath  . '/max_touch_points.js'));
        $this->evaluateOnNewDocument(self::loadFileData($this->jsPath . '/navigator.brave.js'));
    }
}
