<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

error_reporting(E_ALL);
ini_set('display_errors', true);

const BUFFER_SEPARATOR = ';';

$request = $_REQUEST;
$host = $_SERVER['HTTP_HOST'] . preg_replace('/\/\?{1}[a-zA-Z=&1-9]*$/', '', $_SERVER['REQUEST_URI']);
$file = __DIR__ . '/shopware.zip';

$scheme = 'http';

if (isset($_SERVER['REQUEST_SCHEME'])) {
    $scheme = $_SERVER['REQUEST_SCHEME'];
} elseif ($_SERVER['HTTPS'] !== 'off' && strlen($_SERVER['HTTPS']) > 0) {
    $scheme = 'https';
}

if (array_key_exists('checkRequirements', $request)) {

    if (!function_exists('curl_init')) {
        throw new Exception('PHP Extension "curl" is required to download a file.');
    }

    if (!class_exists('ZipArchive')) {
        throw new Exception('PHP Extension "Zip and libzip" is required to unpack a file.');
    }

    if (!is_writable(__DIR__)) {
        throw new Exception(sprintf('The directory "%s" is not writable.', __DIR__));
    }

    if (file_exists($file)) {
        unlink($file);
    }

    echo 'ready';
    exit();
}

if (array_key_exists('getVersionData', $request)) {
    $latestVersionUrl = 'http://update-api.shopware.com/v1/releases/install';
    $data = json_decode(file_get_contents($latestVersionUrl), true);

    if (!array_key_exists(0, $data)) {
        throw new Exception('Could not load latest version information from server');
    }

    $version = new Version($data[0]);
    echo json_encode($version);
    exit();
}

if (array_key_exists('download', $request)) {
    $url = $request['url'];
    $totalSize = $request['totalSize'];
    $downloader = new Downloader($url, $file, $totalSize);
    $downloader->download();
    exit();
}

if (array_key_exists('compare', $request)) {
    $sha1 = $request['sha1'];
    $localSha1 = sha1_file($file);

    if ($sha1 === $localSha1) {
        echo 'ready';
        exit();
    }

    throw new Exception('The downloaded file does not match the original');
}

if (array_key_exists('fileCount', $request)) {
    $source = new ZipArchive();
    $source->open($file);
    echo $source->numFiles;
    exit();
}

if (array_key_exists('unzip', $request)) {
    $step = $request['step'];
    $unpack = new Unpack($file, __DIR__, $step);
    $index = $unpack->unpack();

    if ($index === 'ready') {
        $filePermissionChanger = new FilePermissionChanger([
            ['chmod' => 0775, 'filePath' => __DIR__ . '/bin/console'],
            ['chmod' => 0775, 'filePath' => __DIR__ . '/var/cache/clear_cache.sh'],
        ]);
        $filePermissionChanger->changePermissions();
    }

    echo $index;
    exit();
}

class Version
{
    /** @var  string */
    public $version;

    /** @var  string */
    public $uri;

    /** @var  string */
    public $size;

    /** @var  string */
    public $sha1;

    /**
     * @param array $versionData
     * @throws Exception
     */
    public function __construct(array $versionData)
    {
        if (!array_key_exists('version', $versionData)) {
            throw new Exception('Could not get "version" from version data');
        }
        if (!array_key_exists('uri', $versionData)) {
            throw new Exception('Could not get "uri" from version data');
        }
        if (!array_key_exists('size', $versionData)) {
            throw new Exception('Could not get "size" from version data');
        }
        if (!array_key_exists('sha1', $versionData)) {
            throw new Exception('Could not get "sha1" from version data');
        }

        $this->version = $versionData['version'];
        $this->uri = $versionData['uri'];
        $this->size = $versionData['size'];
        $this->sha1 = $versionData['sha1'];
    }
}

class Downloader
{
    /** @var string */
    private $url;

    /** @var string */
    private $file;

    /** @var integer */
    private $totalSize;

    /** @var int */
    private $stepSize = 1000000;

    /**
     * @param string $url
     * @param string $file
     * @param $totalSize
     */
    public function __construct($url, $file, $totalSize)
    {
        $this->url = $url;
        $this->file = $file;
        $this->totalSize = $totalSize;
    }

    /**
     * Downloads the shopware.zip
     */
    public function download()
    {
        if (!$fileStream = fopen($this->file, 'a+')) {
            throw new Exception('Could not open ' . $this->file);
        }

        if (filesize($this->file) >= $this->totalSize) {
            fclose($fileStream);
            echo 'ready';
            return;
        }

        $range = filesize($this->file) . '-' . (filesize($this->file) + $this->stepSize);

        $resource = curl_init();
        curl_setopt($resource, CURLOPT_URL, $this->url);
        curl_setopt($resource, CURLOPT_RANGE, $range);
        curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($resource, CURLOPT_NOPROGRESS, false);
        curl_setopt($resource, CURLOPT_HEADER, 0);
        curl_setopt($resource, CURLOPT_FILE, $fileStream);
        curl_setopt($resource, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_exec($resource);
        curl_close($resource);

        fclose($fileStream);

        echo filesize($this->file);
    }
}

class Unpack
{
    /** @var string */
    private $directory;

    /** @var string */
    private $file;

    /** @var int */
    private $index = 0;

    /** @var int */
    private $stepSize = 500;

    /**
     * @param string $file
     * @param string $directory
     * @param int $currentIndex
     * @throws Exception
     */
    public function __construct($file, $directory, $currentIndex)
    {
        $this->file = $file;
        $this->directory = $directory . '/';
        $this->index = $currentIndex;

        if (!file_exists($this->file)) {
            throw new Exception(sprintf('The file: "%s" does not exists.'), $this->file);
        }
    }

    /**
     * Unpacks the shopware.zip file
     *
     * @return int|string
     */
    public function unpack()
    {
        $zipFile = new ZipArchive();
        $zipFile->open($this->file);

        $next = $this->index + $this->stepSize;

        while ($this->index < $next) {
            if ($this->index >= $zipFile->numFiles) {
                $zipFile->close();
                $this->deleteFile($this->file);
                $this->deleteFile($this->directory . '/index.php');
                return 'ready';
            }

            $zipFile->extractTo($this->directory, $zipFile->getNameIndex($this->index));
            $this->index++;
        }

        $zipFile->close();

        return $this->index;
    }

    /**
     * @param string $file
     * @throws Exception
     */
    private function deleteFile($file)
    {
        if (file_exists($file)) {
            unlink($file);
        }

        if (file_exists($file)) {
            throw new Exception(sprintf('The file "%s" could not deleted. ', $file));
        }
    }
}

class FilePermissionChanger
{
    /**
     * Format:
     * [
     *      ['chmod' => 0755, 'filePath' => '/path/to/some/file'],
     * ]
     *
     * @var array
     */
    private $filePermissions = [];

    /**
     * @param array
     */
    public function __construct(array $filePermissions)
    {
        $this->filePermissions = $filePermissions;
    }

    /**
     * Performs the chmod command on all permission arrays previously provided.
     */
    public function changePermissions()
    {
        foreach ($this->filePermissions as $filePermission) {
            if (array_key_exists('filePath', $filePermission) &&
                array_key_exists('chmod', $filePermission) &&
                is_writable($filePermission['filePath'])) {
                chmod($filePermission['filePath'], $filePermission['chmod']);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="robots" content="noindex, nofollow"/>
    <title>Shopware 1-File Installer</title>

    <style>
*{margin:0;padding:0}html{box-sizing:border-box}.fallbackLogo{display:block;margin:0 auto}.fallbackProgressbar{margin:0 auto;margin-top:-80px;height:26px;padding:2px;background:#fff}.fallbackIndicator{height:100%;width:0;background:#189eff}html,body{width:100%;height:100%;background:#fff;animation:fade-background 1s ease-in-out .5s forwards}.container{position:absolute;top:50%;left:50%;padding:5px;width:390px;height:390px;transform:translate(-50%,-50%);text-align:center}.is--hidden{display:none}.error--message-container{width:100%}.error--message{margin:20px;padding:20px;background:#FAECEB;color:#E74C3C;word-wrap:break-word;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px}.group--logo{stroke-dasharray:1400;stroke-dashoffset:1400}.logo--left{animation:draw-outline 1.5s linear 1.5s forwards}.logo--right{animation:draw-outline 1.5s linear 2.5s forwards}.background-ring{cx:180;cy:180;r:110;fill:#fff;stroke:#fff;stroke-width:3;fill-opacity:0;stroke-opacity:.3;stroke-dasharray:700;stroke-dashoffset:700}.loader-ring{cx:180;cy:180;r:110;stroke:#fff;fill-opacity:0;stroke-width:8;stroke-dasharray:700;stroke-dashoffset:700;transition:stroke-dashoffset .8s ease-out}.progress-indicator{r:120;cx:180;cy:180;stroke:#fff;stroke-width:4;stroke-opacity:.3;stroke-dasharray:7;fill-opacity:0}.tick{fill:none;stroke:#189eff;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;transition:stroke-dashoffset .8s .8s ease-out;stroke-dasharray:50;stroke-dashoffset:50}.finished--draw-outline{stroke-dashoffset:0}.expand-loader .background-ring{animation:expand-loader 1s linear}.finished--expand-loader .background-ring{stroke-width:8;stroke-opacity:.25;stroke-dashoffset:0}.fadein-fill,.fadein-fill .background-ring{animation:fadein-fill .8s ease-in-out forwards}.finished--fadein-fill{fill-opacity:1}@keyframes draw-outline{to{stroke-dashoffset:0}}@keyframes fadein-fill{to{fill-opacity:1}}@keyframes expand-loader{50%{stroke-dashoffset:0;stroke-width:3}to{stroke-dashoffset:0;stroke-width:8;stroke-opacity:.25}}@keyframes fade-background{to{background:#189eff}}
</style>
</head>
<body>
<div class="error--message-container">
    <div class="error--message is--hidden"></div>
</div>
<div class="container">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 390 390" width="390" height="390">
        <g fill="#f1f1f1" stroke-width="3" stroke="#fff" fill-opacity="0" transform="scale(0.5), translate(180, 180)"
           class="group--logo">
            <path class="logo--left"
                  d="M291.41 325.43c-15.88-11.96-39.283-21.035-64.067-30.646-29.494-11.437-62.923-24.4-88.023-44.373-28.436-22.63-42.26-51.185-42.26-87.304 0-32.396 13.45-60.105 38.895-80.132C164.487 60.516 207.7 48.647 260.92 48.647c14.704 0 28.76.905 41.777 2.693 1.15.153 2.256-.47 2.73-1.496.49-1.052.238-2.28-.624-3.057C271.25 16.62 227.9.004 182.736.004c-48.812 0-94.703 19.007-129.217 53.52C19.003 88.034 0 133.918 0 182.724c0 48.812 19.007 94.7 53.52 129.206 34.51 34.506 80.4 53.51 129.216 53.51 39.437 0 77.01-12.38 108.656-35.803.663-.49 1.06-1.276 1.063-2.1.004-.824-.388-1.612-1.046-2.106"/>
            <path class="logo--right"
                  d="M364.672 165.84c-.06-.696-.4-1.35-.94-1.795-38.132-31.65-68.972-44.558-106.447-44.558-19.998 0-35.33 4.01-45.57 11.92C202.848 138.26 198.16 147.8 198.16 159c0 31.384 38.357 45.688 82.77 62.25 22.888 8.537 46.556 17.363 68.284 29.417.388.217.828.33 1.272.33.306 0 .606-.053.89-.155.714-.257 1.28-.81 1.557-1.516 8.297-21.26 12.504-43.67 12.504-66.603 0-5.387-.257-11.068-.764-16.883"/>
        </g>

        <g class="group--loading" transform="rotate(270), translate(-360, 0)">
            <circle cx="180" cy="180" r="110" class="background-ring"></circle>
            <circle cx="180" cy="180" r="110" class="loader-ring"></circle>
        </g>

        <polyline transform="translate(55, 50), scale(7)" class="tick" points="11.6,20 15.9,24.2 26.4,13.8 "/>
    </svg>
</div>

<script>
    uri = '<?php echo $scheme; ?>://<?php echo $host; ?>';
    bufferSeparator = '<?php echo BUFFER_SEPARATOR; ?>';
</script>
<script>
function $(e,t){return t=t||document,t.querySelector(e)}function ready(e){var t=new Fallback(".container","fallBackMainContainer");return t.isFallbackRequired()?(t.createFallback(),new App(uri,bufferSeparator,!0),void 0):(document.addEventListener("DOMContentLoaded",e,!1),void 0)}function prefixedEvent(e,t,r){for(var a=["webkit","moz","MS","o",""],o=0;o<a.length;o++)a[o]||(t=t.toLowerCase()),e.addEventListener(a[o]+t,r,!1)}function Fallback(e,t){var r=this;r.fallbackClassName=t,r.element=$(e)}function App(e,t,r){var a=this;a.hostUrl=e,a.bufferSeperator=t,a.requireFallback=r||!1,a.init()}function Progressbar(e){var t=this;t.elementSelector=e.elementSelector,t.baseValue=e.baseValue,t.groupLoadingSelector=e.groupLoadingSelector,t.tickSelector=e.tickSelector,t.fadeClass=e.fadeClass,t.useFallback=e.isFallback,t.init()}function Ajax(e){var t=this;t.messageBox=e,t.loadEventName="load",t.progressEventName="progress",t.requestMethod="POST"}function MessageBox(e){var t=this;t.elementSelector=e,t.isHiddenClass="is--hidden",t.init()}Fallback.prototype.createFallback=function(){var e=this;e.element.removeChild($("svg")),e.element.appendChild(e.createFallbackElement())},Fallback.prototype.isFallbackRequired=function(){var e=!1||!!document.documentMode,t=!e&&!!window.StyleMedia;return e||t},Fallback.prototype.createFallbackElement=function(){var e=this,t=document.createElement("div");return t.className=e.fallbackClassName,t.innerHTML=e.getTemplate(),t},Fallback.prototype.getTemplate=function(){return'<div class="fallbackLogo">   <svg xmlns="http://www.w3.org/2000/svg" viewBox="-20 0 390 390" width="390" height="390" style="margin: 0 auto;">      <g fill="#f1f1f1" stroke-width="3" stroke="#fff" fill-opacity="1" transform="scale(0.5), translate(180, 180)">          <path d="M291.41 325.43c-15.88-11.96-39.283-21.035-64.067-30.646-29.494-11.437-62.923-24.4-88.023-44.373-28.436-22.63-42.26-51.185-42.26-87.304 0-32.396 13.45-60.105 38.895-80.132C164.487 60.516 207.7 48.647 260.92 48.647c14.704 0 28.76.905 41.777 2.693 1.15.153 2.256-.47 2.73-1.496.49-1.052.238-2.28-.624-3.057C271.25 16.62 227.9.004 182.736.004c-48.812 0-94.703 19.007-129.217 53.52C19.003 88.034 0 133.918 0 182.724c0 48.812 19.007 94.7 53.52 129.206 34.51 34.506 80.4 53.51 129.216 53.51 39.437 0 77.01-12.38 108.656-35.803.663-.49 1.06-1.276 1.063-2.1.004-.824-.388-1.612-1.046-2.106"/>          <path d="M364.672 165.84c-.06-.696-.4-1.35-.94-1.795-38.132-31.65-68.972-44.558-106.447-44.558-19.998 0-35.33 4.01-45.57 11.92C202.848 138.26 198.16 147.8 198.16 159c0 31.384 38.357 45.688 82.77 62.25 22.888 8.537 46.556 17.363 68.284 29.417.388.217.828.33 1.272.33.306 0 .606-.053.89-.155.714-.257 1.28-.81 1.557-1.516 8.297-21.26 12.504-43.67 12.504-66.603 0-5.387-.257-11.068-.764-16.883"/>      </g>   </svg></div><div class="fallbackProgressbar"><div class="fallbackIndicator"></div></div>'},App.prototype.init=function(){var e=this;e.readyResponse="ready",e.checkRequirementsUrl=e.hostUrl+"?checkRequirements",e.getVersionUrl=e.hostUrl+"?getVersionData=1",e.downloadUrl=e.hostUrl+"?download=1",e.compareUrl=e.hostUrl+"?compare",e.fileCountUrl=e.hostUrl+"?fileCount",e.unzipUrl=e.hostUrl+"?unzip",e.installUrl=e.hostUrl+"recovery/install",e.downloadProgressRange=50,e.unpackProgressRange=40,e.compareResultStep=55,e.fileCountStep=60,e.redierectTimeout=2e3,e.messageBox=new MessageBox(".error--message"),e.ajaxHelper=new Ajax(e.messageBox),e.progressbar=e.requireFallback?new Progressbar(e.getFallbackProgressbarConfig()):new Progressbar(e.getProgressbarConfig()),e.checkRequirements()},App.prototype.getProgressbarConfig=function(){return{elementSelector:".loader-ring",baseValue:700,groupLoadingSelector:".group--loading",tickSelector:".tick",fadeClass:"fadein-fill",isFallback:!1}},App.prototype.getFallbackProgressbarConfig=function(){return{elementSelector:".fallbackIndicator",baseValue:0,groupLoadingSelector:"",tickSelector:"",fadeClass:"",isFallback:!0}},App.prototype.checkRequirements=function(){var e=this;e.ajaxHelper.createRequest(e.checkRequirementsUrl,e.getVersion,e),e.ajaxHelper.startRequest()},App.prototype.getVersion=function(e){var t=this;return e!==t.readyResponse?(t.messageBox.show(e),void 0):(t.ajaxHelper.createRequest(t.getVersionUrl,t.onGetVersion,t),t.ajaxHelper.startRequest(),void 0)},App.prototype.onGetVersion=function(e){var t=this;try{t.versionData=JSON.parse(e)}catch(r){t.messageBox.show(e)}t.startDownLoad()},App.prototype.startDownLoad=function(){var e=this,t=e.downloadUrl+"&url="+e.versionData.uri+"&totalSize="+e.versionData.size;e.ajaxHelper.createRequest(t,e.onDownloadProcess,e),e.ajaxHelper.startRequest()},App.prototype.onDownloadProcess=function(e){var t,r=this;return e!==r.readyResponse?(t=r.downloadProgressRange/r.versionData.size*e,r.progressbar.update(t),r.startDownLoad(),void 0):(r.onDownloadReady(e),void 0)},App.prototype.onDownloadReady=function(e){var t=this,r=e.split(t.bufferSeperator);return r[r.length-1]===t.readyResponse?(t.compareFileSha1(),void 0):(t.messageBox.show(e),void 0)},App.prototype.compareFileSha1=function(){var e=this;e.ajaxHelper.createRequest(e.compareUrl+"&sha1="+e.versionData.sha1,e.onGetCompareResult,e),e.ajaxHelper.startRequest()},App.prototype.onGetCompareResult=function(e){var t=this;return e===t.readyResponse?(t.progressbar.update(t.compareResultStep),t.getFileCount(),void 0):(t.messageBox.show(e),void 0)},App.prototype.getFileCount=function(){var e=this;e.ajaxHelper.createRequest(e.fileCountUrl,e.onGetFileCount,e),e.ajaxHelper.startRequest()},App.prototype.onGetFileCount=function(e){var t=this;return isNaN(parseInt(e))?(t.messageBox.show(e),void 0):(t.fileCount=e,t.unzipStep=t.unpackProgressRange/t.fileCount,t.progressbar.update(t.fileCountStep),t.unpackZipFile(0),void 0)},App.prototype.unpackZipFile=function(e){var t=this;t.ajaxHelper.createRequest(t.unzipUrl+"&step="+e,t.onUnpackProgress,t),t.ajaxHelper.startRequest()},App.prototype.onUnpackProgress=function(e){var t,r=this;return e!==r.readyResponse?(t=r.unzipStep*e,isNaN(e)?(r.messageBox.show(e),void 0):(r.progressbar.update(t+r.fileCountStep),r.unpackZipFile(e),void 0)):(r.onUnpackReady(e),void 0)},App.prototype.onUnpackReady=function(e){var t=this;return e.split(t.bufferSeperator),e!==t.readyResponse?(t.messageBox.show(e),void 0):(t.progressbar.update(100),window.setTimeout(function(){window.location.href=t.installUrl},t.redierectTimeout),void 0)},Progressbar.prototype.init=function(){var e=this;e.progressBarIndicator=document.querySelector(e.elementSelector)},Progressbar.prototype.update=function(e){if(e){var t=this;if(t.useFallback)return t.progressBarIndicator.style.width=e+"%",void 0;var r=Math.floor(t.baseValue-t.baseValue*(e/100));t.progressBarIndicator.style.strokeDashoffset=r,100===e&&($(t.groupLoadingSelector).classList.add(t.fadeClass),$(t.tickSelector).style.strokeDashoffset=0)}},Ajax.prototype.emptyFunction=function(){},Ajax.prototype.init=function(){var e=this;e.request=new XMLHttpRequest,e.requireProgress&&e.request.addEventListener(e.progressEventName,e.onProgress.bind(e)),e.request.addEventListener(e.loadEventName,e.onLoad.bind(e)),e.request.open(e.requestMethod,e.url)},Ajax.prototype.onLoad=function(e){var t=this;return t.request.status>=200&&t.request.status<300?(t.loadCallback.call(t.scope,e.target.responseText),void 0):(t.messageBox.show(e.srcElement.responseText),void 0)},Ajax.prototype.onProgress=function(e){var t=this;return t.request.status>=200&&t.request.status<300?(t.progressCallback.call(t.scope,e.target.responseText),void 0):(t.messageBox.show(e.srcElement.responseText),void 0)},Ajax.prototype.createRequest=function(e,t,r,a){var o=this;o.url=e,o.loadCallback=t||o.emptyFunction,o.scope=r||o,o.progressCallback=a||!1,"function"==typeof o.progressCallback?(o.requireProgress=!0,o.progressCallback=a):(o.requireProgress=!1,o.progressCallback=o.emptyFunction),o.init()},Ajax.prototype.startRequest=function(){var e=this;e.request.send()},MessageBox.prototype.init=function(){var e=this;e.element=document.querySelector(e.elementSelector)},MessageBox.prototype.show=function(e){var t=this;throw t.element.innerHTML=e.trim().replace(/^<br.+?>/,""),t.element.classList.remove(t.isHiddenClass),e},ready(function(){var e=$(".group--logo"),t=$(".group--loading");prefixedEvent(e,"animationend",function(r){var a=r.animationName;switch(e.classList.add("finished--"+a),a){case"draw-outline":e.classList.add("fadein-fill");break;case"fadein-fill":t.classList.add("expand-loader")}}),prefixedEvent(t,"animationend",function(e){var r=e.animationName;switch(t.classList.add("finished--"+r),r){case"expand-loader":new App(uri,bufferSeparator,!1)}})});
</script>
</body>
</html>
