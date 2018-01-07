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
                else if (is_link($file->getPathName()) == true)
                    unlink($file->getPathName());
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
            $bytes = "$bytes bytes";
        else if ($bytes == 1)
            $bytes = "$bytes byte";
        else
            $bytes = "0 bytes";

        return $bytes;
    }
    
    public function arrayLike($elements, $like, $flat) {
        $result = Array();
        
        if ($flat == true) {
            foreach($elements as $key => $value) {
                $pregGrep = preg_grep("~$like~i", $value);

                if (empty($pregGrep) == false)
                    $result[] = $elements[$key];
            }
        }
        else
            $result = preg_grep("~$like~i", $elements);
        
        return $result;
    }
    
    public function arrayMoveElement(&$array, $a, $b) {
        $out = array_splice($array, $a, 1);
        array_splice($array, $b, 0, $out);
    }
    
    public function arrayValueInArray($elements, $subElements) {
        $result = false;
        
        foreach($elements as $key => $value) {
            if (in_array($value, $subElements) == true) {
                $result = true;
                
                break;
            }
        }
        
        return $result;
    }
    
    public function valueInExplodeArray($elementsFirst, $elementsSecond) {
        $elementsFirstExplode = explode(",", $elementsFirst);
        array_pop($elementsFirstExplode);

        $elementsSecondExplode =  explode(",", $elementsSecond);
        array_pop($elementsSecondExplode);
        
        if ($this->arrayValueInArray($elementsFirstExplode, $elementsSecondExplode) == true)
            return true;
        
        return false;
    }
    
    public function urlParameters($completeUrl, $baseUrl) {
        $lastPath = substr($completeUrl, strpos($completeUrl, $baseUrl) + strlen($baseUrl));
        $lastPathExplode = explode("/", $lastPath);
        array_shift($lastPathExplode);
        
        return $lastPathExplode;
    }
    
    public function requestParametersParse($json) {
        $parameters = Array();
        $match = Array();
        
        foreach($json as $key => $value) {
            if (is_object($value) == false)
                $parameters[$key] = $value;
            else {
                preg_match("#\[(.*?)\]#", $value->name, $match);
                
                $keyTmp = "";
                
                if (count($match) == 0)
                    $keyTmp = $value->name;
                else
                    $keyTmp = $match[1];
                    
                $parameters[$keyTmp] = $value->value;
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
    
    public function dateFormat($date) {
        $newData = Array("", "");
        
        $dateExplode = explode(" ", $date);
        
        if (count($dateExplode) == 0)
            $dateExplode = $newData;
        else {
            $languageDate = isset($_SESSION['language_date']) == false ? "Y-m-d" : $_SESSION['language_date'];
            
            if (strpos($dateExplode[0], "0000") === false)
                $dateExplode[0] = date($languageDate, strtotime($dateExplode[0]));
        }
        
        return $dateExplode;
    }
    
    public function configureUserParameter($user) {
        $query = $this->connection->prepare("SELECT id FROM users
                                                LIMIT 1");
        
        $query->execute();
        
        $rowsCount = $query->rowCount();
        
        if ($rowsCount == 0) {
            $user->setRoleUserId("1,2,");
            $user->setNotLocked(1);
        }
        else {
            $user->setRoleUserId("1,");
            $user->setNotLocked(0);
        }
    }
    
    public function assignUserRoleLevel($user) {
        if ($user != null) {
            $row = $this->query->selectRoleUserDatabase($user->getRoleUserId());
            
            foreach($row as $key => $value) {
                $user->setRoles(Array(
                    $value
                ));
            }
        }
    }
    
    public function assignUserPassword($type, $user, $form) {
        $row = $this->query->selectUserDatabase($user->getId());
        
        if ($type == "withOld") {
            if (password_verify($form->get("old")->getData(), $row['password']) == false)
                return $this->translator->trans("class_utility_2");
            else if ($form->get("new")->getData() != $form->get("newConfirm")->getData())
                return $this->translator->trans("class_utility_3");
            
            $user->setPassword($this->createPasswordEncoder($type, $user, $form));
        }
        else if ($type == "withoutOld") {
            if ($form->get("password")->getData() != "" || $form->get("passwordConfirm")->getData() != "") {
                if ($form->get("password")->getData() != $form->get("passwordConfirm")->getData())
                    return $this->translator->trans("class_utility_4");
                
                $user->setPassword($this->createPasswordEncoder($type, $user, $form));
            }
            else
                $user->setPassword($row['password']);
        }
        
        return "ok";
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
            if (isset($_SESSION['user_activity_count']) == false || isset($_SESSION['user_activity']) == false) {
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
            if ($_SESSION['user_activity_count'] > 1) {
                $_SESSION['user_activity_count'] = 0;
                $_SESSION['user_activity'] = "";
            }
            
            $_SESSION['user_activity_count'] ++;
        }
    }
    
    public function checkAttemptLogin($type, $userValue, $settingRow) {
        $row = $this->query->selectUserDatabase($userValue);
        
        $dateTimeCurrentLogin = new \DateTime($row['date_current_login']);
        $dateTimeCurrent = new \DateTime();
        
        $interval = intval($dateTimeCurrentLogin->diff($dateTimeCurrent)->format("%i"));
        $total = $settingRow['login_attempt_time'] - $interval;
        
        if ($total < 0)
            $total = 0;
        
        $dateCurrent = date("Y-m-d H:i:s");
        $dateLastLogin = strpos($row['date_last_login'], "0000") !== false ? $dateCurrent : $row['date_current_login'];
        
        $result = Array("", "");
        
        if (isset($row['id']) == true && $settingRow['login_attempt_time'] > 0) {
            $count = $row['attempt_login'] + 1;
            
            $query = $this->connection->prepare("UPDATE users
                                                    SET date_current_login = :dateCurrentLogin,
                                                        date_last_login = :dateLastLogin,
                                                        ip = :ip,
                                                        attempt_login = :attemptLogin
                                                    WHERE id = :id");
            
            if ($type == "success") {
                if ($count > $settingRow['login_attempt_count'] && $total > 0) {
                    $result[0] = "lock";
                    $result[1] = $total;
                    
                    return Array(false, $result[0], $result[1]);
                }
                else {
                    $query->bindValue(":dateCurrentLogin", $dateCurrent);
                    $query->bindValue(":dateLastLogin", $dateLastLogin);
                    $query->bindValue(":ip", $this->clientIp());
                    $query->bindValue(":attemptLogin", 0);
                    $query->bindValue(":id", $row['id']);

                    $query->execute();
                }
            }
            else if ($type == "failure") {
                if ($count > $settingRow['login_attempt_count'] && $total > 0) {
                    $result[0] = "lock";
                    $result[1] = $total;
                }
                else {
                    if ($count > $settingRow['login_attempt_count'])
                        $count = 1;
                    
                    $query->bindValue(":dateCurrentLogin", $dateCurrent);
                    $query->bindValue(":dateLastLogin", $row['date_last_login']);
                    $query->bindValue(":ip", $this->clientIp());
                    $query->bindValue(":attemptLogin", $count);
                    $query->bindValue(":id", $row['id']);
                    
                    $query->execute();
                    
                    $result[0] = "try";
                    $result[1] = $count;
                }
                
                return Array(false, $result[0], $result[1]);
            }
        }
        
        return Array(true, $result[0], $result[1]);
    }
    
    public function checkLanguage($request) {
        if ($request->request->get("form_language")['codeText'] != null)
            $_SESSION['form_language_codeText'] = $request->request->get("form_language")['codeText'];
        
        if (isset($_SESSION['form_language_codeText']) == false) {
            $row = $this->query->selectSettingDatabase();
            
            $_SESSION['form_language_codeText'] = $row['language'];
        }
        
        $request->setLocale($_SESSION['form_language_codeText']);
        
        return $_SESSION['form_language_codeText'];
    }
    
    public function checkUserNotLocked($username) {
        $row = $this->query->selectUserDatabase($username);
        
        if ($row == false)
            return true;
        else
            return $row['not_locked'];
    }
    
    public function checkUserRole($roleName, $roleId) {
        $row = $this->query->selectRoleUserDatabase($roleId);
        
        foreach ($roleName as $key => $value) {
            if (in_array($value, $row) == true) {
                return true;

                break;
            }
        }
        
        return false;
    }
    
    public function checkMobile() {
        $isMobile = false;
        
        if (preg_match("/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i", $_SERVER['HTTP_USER_AGENT'])
            || preg_match("/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i", substr($_SERVER['HTTP_USER_AGENT'], 0, 4)))
            $isMobile = true;
        
        return $isMobile;
    }
    
    public function createPageHtml($urlLocale, $selectId) {
        $rows = $this->query->selectAllPageDatabase($urlLocale);
        
        $pagesList = $this->createPageList($rows, true);
        
        $html = "<p class=\"margin_clear\">" . $this->translator->trans("class_utility_5") . "</p>
        <select id=\"$selectId\">
            <option value=\"\">Select</option>";
            foreach($pagesList as $key => $value)
                $html .= "<option value=\"$key\">$value</option>";
        $html .= "</select>";
        
        return $html;
    }
    
    public function createUserRoleHtml($selectId, $isRequired = false) {
        $rows = $this->query->selectAllRoleUserDatabase();
        
        $required = $isRequired == true ? "required=\"required\"" : "";
        
        $html = "<select id=\"$selectId\" class=\"form-control\" $required>
            <option value=\"\">" . $this->translator->trans("class_utility_6") . "</option>";
            foreach($rows as $key => $value)
                $html .= "<option value=\"{$value['id']}\">{$value['level']}</option>";
        $html .= "</select>";
        
        return $html;
    }
    
    public function createLanguageOptionsHtml($code) {
        $row = $this->query->selectLanguageDatabase($code);
        $rows = $this->query->selectAllLanguageDatabase();
        
        $key = array_search($row, $rows);
        unset($rows[$key]);
        array_unshift($rows, $row);
        
        $html = "";
        
        foreach($rows as $key => $value) {
            $html .= "<option value=\"{$value['code']}\">{$value['code']}</option>";
            
            if ($key == 0)
                $html .= "<option disabled=\"disabled\">-------------------</option>";
        }
        
        return $html;
    }
    
    public function createPageList($pagesRows, $onlyMenuName, $pagination = null) {
        $pagesListHierarchy = $this->createPageListHierarchy($pagesRows, $pagination);
        
        if ($onlyMenuName == true) {
            $tag = "";
            $parentId = 0;
            $elements = Array();
            $count = 0;

            $pagesListOnlyMenuName = $this->createPageListOnlyMenuName($pagesListHierarchy, $tag, $parentId, $elements, $count);
            
            return $pagesListOnlyMenuName;
        }
        
        return $pagesListHierarchy;
    }
    
    public function createTemplateList() {
        $templatesPath = "{$this->pathSrcBundle}/Resources/public/images/templates";
        
        $scanDirElements = scandir($templatesPath);
        
        $list = Array();
        
        if ($scanDirElements != false) {
            foreach ($scanDirElements as $key => $value) {
                if ($value != "." && $value != ".." && $value != ".htaccess" && is_dir("$templatesPath/$value") == true)
                    $list[$value] = $value;
            }
        }
        
        return $list;
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
                        $paramsIndexKey = (int)$params[2];
                    else
                        $paramsIndexKey = (string)$params[2];
                }
                
                $resultArray = array();
                
                foreach ($paramsInput as $row) {
                    $key = null;
                    $value = null;
                    
                    $keySet = false;
                    $valueSet = false;
                    
                    if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                        $keySet = true;
                        $key = (string)$row[$paramsIndexKey];
                    }
                    
                    if ($paramsColumnKey == null) {
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
    
    private function createPasswordEncoder($type, $user, $form) {
        if ($type == "withOld")
            return $this->passwordEncoder->encodePassword($user, $form->get("new")->getData());
        else if ($type == "withoutOld")
            return $this->passwordEncoder->encodePassword($user, $form->get("password")->getData());
    }
    
    private function createPageListHierarchy($pagesRows, $pagination) {
        $elements = array_slice($pagesRows, $pagination['offset'], $pagination['show']);
        
        $nodes = Array();
        $tree = Array();
        
        foreach ($elements as $page) {
            $nodes[$page['id']] = array_merge($page, Array(
                'children' => Array()
            ));
        }
        
        foreach ($nodes as &$node) {
            if ($node['parent'] == null || array_key_exists($node['parent'], $nodes) == false)
                $tree[] = &$node;
            else
                $nodes[$node['parent']]['children'][] = &$node;
        }
        
        unset($node);
        unset($nodes);
        
        return $tree;
    }
    
    private function createPageListOnlyMenuName($pagesListHierarchy, &$tag, &$parentId, &$elements, &$count) {
        foreach ($pagesListHierarchy as $key => $value) {
            if ($value['parent'] == null) {
                $count = 0;
                
                $tag = "-";
            }
            else if ($value['parent'] != null && $parentId != null && $value['parent'] < $parentId) {
                $count --;
                
                if ($count == 1)
                    $tag = substr($tag, 0, 2);
                else
                    $tag = substr($tag, 0, $count);
            }
            else if ($value['parent'] != null && $value['parent'] != $parentId) {
                $count ++;
                
                $tag .= "-";
            }
            
            $parentId = $value['parent'];
            
            $elements[$value['id']] = "|$tag| " . $value['alias'];
            
            if (count($value['children']) > 0)
                $this->createPageListOnlyMenuName($value['children'], $tag, $parentId, $elements, $count);
        }
        
        return $elements;
    }
}