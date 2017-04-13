<?php
namespace ReinventSoftware\UebusaitoBundle\Classes;

use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use ReinventSoftware\UebusaitoBundle\Config;
use ReinventSoftware\UebusaitoBundle\Classes\Query;

class Utility {
    // Vars
    private $container;
    private $entityManager;
    
    private $connection;
    private $sessionMaxIdleTime;
    private $requestStack;
    private $translator;
    private $authorizationChecker;
    private $authenticationUtils;
    private $passwordEncoder;
    private $tokenStorage;
    
    private $config;
    private $query;
    
    private $pathDocumentRoot;
    private $pathRoot;
    private $pathRootFull;
    private $pathBundleFull;
    
    private $urlRoot;
    private $urlBundle;
    private $urlRootFull;
    
    private $websiteName;
    
    private $settings;
    
    // Properties
    public function getConnection() {
        return $this->connection;
    }
    
    public function getSessionMaxIdleTime() {
        return $this->sessionMaxIdleTime;
    }
    
    public function getRequestStack() {
        return $this->requestStack;
    }
    
    public function getTranslator() {
        return $this->translator;
    }
    
    public function getAuthorizationChecker() {
        return $this->authorizationChecker;
    }
    
    public function getAuthenticationUtils() {
        return $this->authenticationUtils;
    }
    
    public function getPasswordEncoder() {
        return $this->passwordEncoder;
    }
    
    public function getTokenStorage() {
        return $this->tokenStorage;
    }
    
    public function getPathRoot() {
        return $this->pathRoot;
    }
    
    public function getPathDocumentRoot() {
        return $this->pathDocumentRoot;
    }
    
    public function getPathRootFull() {
        return $this->pathRootFull;
    }
    
    public function getPathBundleFull() {
        return $this->pathBundleFull;
    }
    
    public function getUrlRoot() {
        return $this->urlRoot;
    }
    
    public function getUrlBundle() {
        return $this->urlBundle;
    }
    
    public function getUrlRootFull() {
        return $this->urlRootFull;
    }
    
    public function getWebsiteName() {
        return $this->websiteName;
    }
    
    public function getSettings() {
        return $this->settings;
    }
      
    // Functions public
    public function __construct($container, $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->connection = $this->entityManager->getConnection();
        $this->sessionMaxIdleTime = $this->container->getParameter("session_max_idle_time");
        $this->requestStack = $this->container->get("request_stack")->getCurrentRequest();
        $this->translator = $this->container->get("translator");
        $this->authorizationChecker = $this->container->get("security.authorization_checker");
        $this->authenticationUtils = $this->container->get("security.authentication_utils");
        $this->passwordEncoder = $this->container->get("security.password_encoder");
        $this->tokenStorage = $this->container->get("security.token_storage");
        
        $this->config = new Config();
        $this->query = new Query($this->connection);
        
        $protocol = isset($_SERVER['HTTPS']) == true ? "https://" : "http://";
        
        $this->pathRoot = $this->config->getPathRoot();
        $this->pathDocumentRoot = $_SERVER['DOCUMENT_ROOT'];
        $this->pathRootFull = $this->pathDocumentRoot . $this->pathRoot;
        $this->pathBundleFull = "{$this->pathRootFull}/src/ReinventSoftware/UebusaitoBundle";
        
        $this->urlRoot = $this->config->getUrlRoot();
        $this->urlBundle = "{$this->config->getUrlRoot()}/web/bundles/uebusaito";
        $this->urlRootFull = $protocol . $_SERVER['HTTP_HOST'] . $this->config->getUrlRoot() . $this->config->getFile();
        
        $this->websiteName = $this->config->getName();
        
        $this->settings = $this->query->selectAllSettingsDatabase();
        
        $this->arrayColumnFix();
    }
    
    public function generateToken() {
        $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(21));
    }
    
    public function sessionDestroy() {
        session_destroy();
        session_unset();
        
        $cookies = Array(
            'rememberme'
        );
        
        foreach ($cookies as $value)
            unset($_COOKIE[$value]);
    }
    
    public function configureCookie($name, $lifeTime, $secure, $httpOnly) {
        $currentCookieParams = session_get_cookie_params();
        
        $value = isset($_COOKIE[$name]) == true ? $_COOKIE[$name] : session_id();
        
        if (isset($_COOKIE[$name]) == true)
            setcookie($name, $value, $lifeTime, $currentCookieParams['path'], $currentCookieParams['domain'], $secure, $httpOnly);
    }
    
    public function searchInFile($filePath, $word, $replace) {
        $reading = fopen($filePath, "r");
        $writing = fopen($filePath + ".tmp", "w");
        
        $checked = false;
        
        while (feof($reading) == false) {
            $line = fgets($reading);
            
            if (stristr($line, $word) != false) {
                $line = $replace;
                
                $checked = true;
            }
            
            if (feof($reading) == true && $replace == null) {
                $line = "$word\n";

                $checked = true;
            }
            
            fwrite($writing, $line);
        }
        
        fclose($reading);
        fclose($writing);
        
        if ($checked == true) 
            @rename($filePath + ".tmp", $filePath);
        else
            @unlink($filePath + ".tmp");
    }
    
    public function removeDirRecursive($path, $parent) {
        if (file_exists($path) == true) {
            $rdi = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST);

            foreach($rii as $file) {
                if ($file->isDir() == true)
                    rmdir($file->getRealPath());
                else
                    @unlink($file->getRealPath());
            }

            if ($parent == true)
                rmdir($path);
        }
    }
    
    public function generateRandomString($length) {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $charactersLength = strlen($characters);
        $randomString = "";
        
        for ($i = 0; $i < $length; $i++)
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        
        return $randomString;
    }
    
    public function sendEmail($to, $subject, $message, $from) {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: $from\r\n" .
            "Reply-To: $from \r\n" .
            "X-Mailer: PHP/" . phpversion();

        mail($to, $subject, $message, $headers);
    }
    
    public function sizeUnits($bytes) {
        if ($bytes >= 1073741824)
            $bytes = number_format($bytes / 1073741824, 2) . " GB";
        else if ($bytes >= 1048576)
            $bytes = number_format($bytes / 1048576, 2) . " MB";
        else if ($bytes >= 1024)
            $bytes = number_format($bytes / 1024, 2) . " KB";
        else if ($bytes > 1)
            $bytes = $bytes . " bytes";
        else if ($bytes == 1)
            $bytes = $bytes . " byte";
        else
            $bytes = "0 bytes";

        return $bytes;
    }
    
    public function arrayLike($elements, $like, $flat) {
        $result = Array();
        
        if ($flat == true) {
            foreach($elements as $key => $value) {
                $pregGrep = preg_grep("~$like~i", $value);

                if (empty($pregGrep) === false)
                    $result[] = $elements[$key];
            }
        }
        else
            $result = preg_grep("~$like~i", $elements);
        
        return $result;
    }
    
    public function valueInSubArray($elements, $subElements) {
        $result = false;
        
        foreach($elements as $key => $value) {
            if (in_array($value, $subElements) == true) {
                $result = true;
                
                break;
            }
        }
        
        return $result;
    }
    
    public function urlParameters($completeUrl, $baseUrl) {
        $lastPath = substr($completeUrl, strpos($completeUrl, $baseUrl) + strlen($baseUrl));
        $lastPathExplode = explode("/", $lastPath);
        array_shift($lastPathExplode);
        
        return $lastPathExplode;
    }
    
    public function clientIp() {
        $ip = "";
        
        if (getenv("HTTP_CLIENT_IP"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if(getenv("HTTP_X_FORWARDED_FOR"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if(getenv("HTTP_X_FORWARDED"))
            $ip = getenv("HTTP_X_FORWARDED");
        else if(getenv("HTTP_FORWARDED_FOR"))
            $ip = getenv("HTTP_FORWARDED_FOR");
        else if(getenv("HTTP_FORWARDED"))
           $ip = getenv("HTTP_FORWARDED");
        else if(getenv("REMOTE_ADDR"))
            $ip = getenv("REMOTE_ADDR");
        else
            $ip = "UNKNOWN";
        
        return $ip;
    }
    
    // Functions private
    private function arrayColumnFix() {
        if (function_exists("array_column") == false) {
            function array_column($input = null, $columnKey = null, $indexKey = null) {
                $argc = func_num_args();
                $params = func_get_args();
                
                if ($argc < 2) {
                    trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
                    return null;
                }
                
                if (!is_array($params[0])) {
                    trigger_error("array_column() expects parameter 1 to be array, " . gettype($params[0]) . " given", E_USER_WARNING);
                    return null;
                }
                
                if (!is_int($params[1]) && !is_float($params[1]) && !is_string($params[1]) && $params[1] !== null && !(is_object($params[1]) && method_exists($params[1], "__toString"))) {
                    trigger_error("array_column(): The column key should be either a string or an integer", E_USER_WARNING);
                    return false;
                }
                
                if (isset($params[2]) && !is_int($params[2]) && !is_float($params[2]) && !is_string($params[2]) && !(is_object($params[2]) && method_exists($params[2], "__toString"))) {
                    trigger_error("array_column(): The index key should be either a string or an integer", E_USER_WARNING);
                    return false;
                }
                
                $paramsInput = $params[0];
                $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;
                $paramsIndexKey = null;
                
                if (isset($params[2])) {
                    if (is_float($params[2]) || is_int($params[2]))
                        $paramsIndexKey = (int) $params[2];
                    else
                        $paramsIndexKey = (string) $params[2];
                }
                
                $resultArray = array();
                
                foreach ($paramsInput as $row) {
                    $key = $value = null;
                    $keySet = $valueSet = false;
                    
                    if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                        $keySet = true;
                        $key = (string) $row[$paramsIndexKey];
                    }
                    
                    if ($paramsColumnKey === null) {
                        $valueSet = true;
                        $value = $row;
                    }
                    else if (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                        $valueSet = true;
                        $value = $row[$paramsColumnKey];
                    }
                    
                    if ($valueSet) {
                        if ($keySet)
                            $resultArray[$key] = $value;
                        else
                            $resultArray[] = $value;
                    }
                }
                
                return $resultArray;
            }
        }
    }
}