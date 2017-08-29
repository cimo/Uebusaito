<?php
namespace ReinventSoftware\UebusaitoBundle\Classes\System;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use ReinventSoftware\UebusaitoBundle\Config;
use ReinventSoftware\UebusaitoBundle\Classes\System\Query;

class Utility {
    // Vars
    private $container;
    private $entityManager;
    
    private $connection;
    private $sessionMaxIdleTime;
    private $translator;
    private $authorizationChecker;
    private $authenticationUtils;
    private $passwordEncoder;
    private $tokenStorage;
    
    private $config;
    private $query;
    
    private $pathRoot;
    private $pathSrcBundle;
    private $pathWebBundle;
    
    private $urlRoot;
    private $urlWebBundle;
    
    private $supportSymlink;
    
    private $websiteFile;
    private $websiteName;
    
    // Properties
    public function getConnection() {
        return $this->connection;
    }
    
    public function getSessionMaxIdleTime() {
        return $this->sessionMaxIdleTime;
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
    
    public function getQuery() {
        return $this->query;
    }
    
    public function getPathRoot() {
        return $this->pathRoot;
    }
    
    public function getPathSrcBundle() {
        return $this->pathSrcBundle;
    }
    
    public function getPathWebBundle() {
        return $this->pathWebBundle;
    }
    
    public function getUrlRoot() {
        return $this->urlRoot;
    }
    
    public function getUrlWebBundle() {
        return $this->urlWebBundle;
    }
    
    public function getSupportSymlink() {
        return $this->supportSymlink;
    }
    
    public function getWebsiteFile() {
        return $this->websiteFile;
    }
    
    public function getWebsiteName() {
        return $this->websiteName;
    }
      
    // Functions public
    public function __construct($container, $entityManager) {
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->connection = $this->entityManager->getConnection();
        $this->sessionMaxIdleTime = $this->container->getParameter("session_max_idle_time");
        $this->translator = $this->container->get("translator");
        $this->authorizationChecker = $this->container->get("security.authorization_checker");
        $this->authenticationUtils = $this->container->get("security.authentication_utils");
        $this->passwordEncoder = $this->container->get("security.password_encoder");
        $this->tokenStorage = $this->container->get("security.token_storage");
        
        $this->config = new Config();
        $this->query = new Query($this->connection);
        
        $this->pathRoot = $_SERVER['DOCUMENT_ROOT'] . $this->config->getPathRoot();
        $this->pathSrcBundle = "{$this->pathRoot}/src/ReinventSoftware/UebusaitoBundle";
        $this->pathWebBundle = "{$this->pathRoot}/web/bundles/uebusaito";
        
        $this->urlRoot = $this->config->getProtocol() . $_SERVER['HTTP_HOST'] . $this->config->getUrlRoot();
        $this->urlWebBundle = "{$this->config->getUrlRoot()}/bundles/uebusaito";
        
        $this->supportSymlink = $this->config->getSupportSymlink();
        
        $this->websiteFile = $this->config->getFile();
        $this->websiteName = $this->config->getName();
        
        $this->arrayColumnFix();
    }
    
    public function generateToken() {
        if (isset($_SESSION['token']) == false)
            $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(21));
    }
    
    public function configureCookie($name, $lifeTime, $secure, $httpOnly) {
        $currentCookieParams = session_get_cookie_params();
        
        $value = isset($_COOKIE[$name]) == true ? $_COOKIE[$name] : session_id();
        
        if (isset($_COOKIE[$name]) == true)
            setcookie($name, $value, $lifeTime, $currentCookieParams['path'], $currentCookieParams['domain'], $secure, $httpOnly);
    }
    
    public function sessionUnset() {
        session_unset();
        
        $cookies = Array(
            'rememberme'
        );
        
        foreach ($cookies as $value)
            unset($_COOKIE[$value]);
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
            unlink($filePath + ".tmp");
    }
    
    public function removeDirRecursive($path, $parent) {
        if (file_exists($path) == true) {
            $rdi = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $rii = new RecursiveIteratorIterator($rdi, RecursiveIteratorIterator::CHILD_FIRST);

            foreach($rii as $file) {
                if (file_exists($file->getRealPath()) == true) {
                    if ($file->isDir() == true)
                        rmdir($file->getRealPath());
                    else
                        unlink($file->getRealPath());
                }
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
    
    public function requestParametersParse($json) {
        $parameters = Array();
        
        foreach($json as $key => $value) {
            if (is_object($value) == false)
                $parameters[$key] = $value;
            else {
                preg_match('#\[(.*?)\]#', $value->name, $match);
                
                $parameters[$match[1]] = $value->value;
            }
        }
        
        return $parameters;
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
    
    public function checkToken($request) {
        if (isset($_SESSION['token']) == true && $request->get("token") == $_SESSION['token'])
            return true;
        
        return false;
    }
    
    public function checkCaptcha($captchaEnabled, $captcha) {
        if ($captchaEnabled == false || ($captchaEnabled == true && isset($_SESSION['captcha']) == true && $_SESSION['captcha'] == $captcha))
            return true;
        
        return false;
    }
    
    public function checkSessionOverTime($request, $root = false) {
        if ($root == true) {
            if (isset($_SESSION['user_activity']) == false) {
                $_SESSION['user_activity_count'] = 0;
                $_SESSION['user_activity'] = "";
            }
        }
        
        if ($request->cookies->has("REMEMBERME") == false && $this->authorizationChecker->isGranted("IS_AUTHENTICATED_FULLY") == true) {
            if (isset($_SESSION['timestamp']) == false)
                $_SESSION['timestamp'] = time();
            else {
                $timeLapse = time() - $_SESSION['timestamp'];

                if ($timeLapse > $this->sessionMaxIdleTime) {
                    $userActivity = $this->translator->trans("class_utility_1");
                    
                    if ($request->isXmlHttpRequest() == true) {
                        echo json_encode(Array(
                            'userActivity' => $userActivity
                        ));

                        exit;
                    }
                    else
                        $this->tokenStorage->setToken(null);
                    
                    $_SESSION['user_activity'] = $userActivity;
                    
                    unset($_SESSION['timestamp']);
                }
                else
                    $_SESSION['timestamp'] = time();
            }
        }
        
        if (isset($_SESSION['user_activity']) == true) {
            if ($request->isXmlHttpRequest() == true && $_SESSION['user_activity'] != "") {
                echo json_encode(Array(
                    'userActivity' => $_SESSION['user_activity']
                ));

                exit;
            }
        }
        
        if ($root == true && $_SESSION['user_activity'] != "") {
            $_SESSION['user_activity_count'] ++;

            if ($_SESSION['user_activity_count'] > 2) {
                $_SESSION['user_activity_count'] = 0;
                $_SESSION['user_activity'] = "";
            }
        }
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