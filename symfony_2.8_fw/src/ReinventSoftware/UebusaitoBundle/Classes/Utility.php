<?php
namespace ReinventSoftware\UebusaitoBundle\Classes;

use ReinventSoftware\UebusaitoBundle\Config;
use ReinventSoftware\UebusaitoBundle\Classes\Query;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Utility {
    // Vars
    private $config;
    private $query;
    
    private $container;
    private $entityManager;
    
    private $connection;
    private $requestStack;
    private $translator;
    private $authorizationChecker;
    
    private $settings;
    
    private $websiteName;
    
    private $pathRoot;
    private $pathRootFull;
    
    private $urlRoot;
    private $urlPublic;
    private $urlView;
    
    // Properties
    public function getQuery() {
        return $this->query;
    }
    
    public function getSettings() {
        return $this->settings;
    }
    
    public function getWebsiteName() {
        return $this->websiteName;
    }
    
    public function getPathDocumentRoot() {
        return $this->pathDocumentRoot;
    }
    
    public function getPathRoot() {
        return $this->pathRoot;
    }
    
    public function getPathRootFull() {
        return $this->pathRootFull;
    }
    
    public function getPathBundle() {
        return $this->pathBundle;
    }
    
    public function getUrlRoot() {
        return $this->urlRoot;
    }
    
    public function getUrlPublic() {
        return $this->urlPublic;
    }
    
    public function getUrlView() {
        return $this->urlView;
    }
      
    // Functions public
    public function __construct($container, $entityManager) {
        $this->config = new Config();
        $this->query = new Query($entityManager);
        
        $this->container = $container;
        $this->entityManager = $entityManager;
        
        $this->connection = $this->entityManager->getConnection();
        $this->requestStack = $this->container->get("request_stack")->getCurrentRequest();
        $this->translator = $this->container->get("translator");
        $this->authorizationChecker = $this->container->get("security.authorization_checker");
        
        $this->settings = $this->query->selectAllSettingsFromDatabase();
        
        $this->websiteName = $this->config->getName();
        
        $this->pathDocumentRoot = $_SERVER['DOCUMENT_ROOT'];
        $this->pathRoot = $this->config->getPathRoot();
        $this->pathRootFull = $this->pathDocumentRoot . $this->pathRoot;
        $this->pathBundle = "{$this->pathRootFull}/src/ReinventSoftware/UebusaitoBundle";
        
        $protocol = isset($_SERVER['HTTPS']) == true ? "https://" : "http://";
        $this->urlRoot = $protocol . $_SERVER['HTTP_HOST'] . $this->config->getUrlRoot() . $this->config->getFile();
        $this->urlPublic = $protocol . $_SERVER['HTTP_HOST'] . $this->config->getUrlRoot() . "/Resources/public";
        $this->urlView = $protocol . $_SERVER['HTTP_HOST'] . $this->config->getUrlRoot() . "/Resources/views";
        
        $this->arrayColumnFix();
    }
    
    public function generateToken() {
        $_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(21));
    }
    
    public function checkSessionOverTime($container, $requestStack) {
        $sessionMaxIdleTime = $container->getParameter("session_max_idle_time");
        
        if ($sessionMaxIdleTime > 0) {
            if ($this->requestStack->cookies->has("REMEMBERME") == false && $this->authorizationChecker->isGranted("IS_AUTHENTICATED_FULLY") == true) {
                $timeLapse = time() - $requestStack->getSession()->getMetadataBag()->getLastUsed();

                if ($timeLapse > $sessionMaxIdleTime) {
                    $this->sessionDestroy();

                    return $this->translator->trans("utility_1");
                }
            }
        }
        
        return "";
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
    
    public function searchInFile($filePath, $word, $replace = null) {
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
    
    public function removeDirRecursive($path, $parent = true) {
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
    
    public function generateRandomString($length = 20) {
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
    
    public function arrayLike($elements, $like, $flat = false) {
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
    
    public function urlParametersControl($parameters) {
        $elements = Array(3);
        
        if (count($parameters) == 0) {
            $elements[0] = isset($_SESSION['languageText']) === false ? $this->settings['language'] : $_SESSION['languageText'];
            $elements[1] = 2;
            $elements[2] = "";
        }
        else {
            $languageRows = $this->query->selectAllLanguagesFromDatabase();
            
            $urlLocale = "";
            
            foreach ($languageRows as $key => $value) {
                if ($parameters[0] == $value['code']) {
                    $urlLocale = $parameters[0];
                    
                    break;
                }
            }
            
            if ($urlLocale == "")
                $elements[0] = isset($_SESSION['languageText']) === false ? $this->settings['language'] : $_SESSION['languageText'];
            else
                $elements[0] = $urlLocale;
            
            $elements[1] = $this->requestStack->attributes->get("urlCurrentPageId");
            $elements[2] = $this->requestStack->attributes->get("urlExtra");
        }
        
        $_SESSION['languageText'] = $elements[0];
        
        return $elements;
    }
    
    // ---
    
    public function configureUserProfilePassword($user, $type, $form) {
        $userRow = $this->query->selectUserFromDatabase("id", $user->getId());
        
        if ($type == 1) {
            if (password_verify($form->get("old")->getData(), $userRow['password']) == false)
                return $this->translator->trans("utility_2");

            if ($form->get("new")->getData() != $form->get("newConfirm")->getData())
                return $this->translator->trans("utility_3");
            
            $user->setPassword($this->passwordEncoded($user, $type, $form));
        }
        else if ($type == 2) {
            if ($form->get("password")->getData() != "" || $form->get("passwordConfirm")->getData() != "") {
                if ($form->get("password")->getData() != $form->get("passwordConfirm")->getData())
                    return $this->translator->trans("utility_4");
                
                $user->setPassword($this->passwordEncoded($user, $type, $form));
            }
            else
                $user->setPassword($userRow['password']);
        }
        
        return "ok";
    }
    
    public function configureUserParameters($user) {
        $query = $this->connection->prepare("SELECT id FROM users
                                                LIMIT 1");
        
        $query->execute();
        
        $rowsCount = $query->rowCount();
        
        if ($rowsCount == 0) {
            $user->setRoleId("1,2,");
            $user->setNotLocked(1);
        }
        else {
            $user->setRoleId("1,");
            $user->setNotLocked(0);
        }
        
        $user->setCredits(0);
    }
    
    public function assignUserRole($user) {
        if ($user != null) {
            $rolesExplode = explode(",", $user->getRoleId());
            array_pop($rolesExplode);
            
            foreach($rolesExplode as $key => $value) {
                $query = $this->connection->prepare("SELECT level FROM users_roles
                                                        WHERE id = :value");

                $query->bindValue(":value", $value);
                
                $query->execute();
                
                $rows = $query->fetch();
                
                $user->setRoles(Array(
                    $rows['level']
                ));
            }
        }
    }
    
    public function createPagesSelectHtml($urlLocale, $selectId) {
        $pageRows = $this->query->selectAllPagesFromDatabase($urlLocale);
        
        $pagesList = $this->createPagesList($pageRows, true);
        
        $html = "<p class=\"margin_clear\">" . $this->translator->trans("utility_5") . "</p>
        <select id=\"$selectId\">
            <option value=\"\">Select</option>";
            foreach($pagesList as $key => $value)
                $html .= "<option value=\"$key\">$value</option>";
        $html .= "</select>";
        
        return $html;
    }
    
    public function createRolesSelectHtml($selectId, $isRequired = false) {
        $roleRows = $this->query->selectAllUserRolesFromDatabase();
        
        $required = $isRequired == true ? "required=\"required\"" : "";
        
        $html = "<select id=\"$selectId\" class=\"form-control\" $required>
            <option value=\"\">Select</option>";
            foreach($roleRows as $key => $value)
                $html .= "<option value=\"{$value['id']}\">{$value['level']}</option>";
        $html .= "</select>";
        
        return $html;
    }
    
    public function createPagesList($pagesRows, $onlyMenuName, $pagination = null) {
        $pagesListHierarchy = $this->createPagesListHierarchy($pagesRows, $pagination);
        
        if ($onlyMenuName == true) {
            $tag = "";
            $parentId = 0;
            $elements = Array();
            $count = 0;

            $pagesListOnlyMenuName = $this->createPagesListOnlyMenuName($pagesListHierarchy, $tag, $parentId, $elements, $count);
            
            return $pagesListOnlyMenuName;
        }
        
        return $pagesListHierarchy;
    }
    
    public function createTemplatesList() {
        $templatesPath = "$this->pathRootFull/src/ReinventSoftware/UebusaitoBundle/Resources/public/images/templates";
        
        $scanDirElements = @scandir($templatesPath);
        
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
    
    // ---
    
    private function passwordEncoded($user, $type, $form) {
        $encoder = $this->container->get("security.password_encoder");

        if ($type == 1)
            return $encoder->encodePassword($user, $form->get("new")->getData());
        else if ($type == 2)
            return $encoder->encodePassword($user, $form->get("password")->getData());
    }
    
    private function createPagesListHierarchy($pagesRows, $pagination) {
        $elements = array_slice($pagesRows, $pagination['offset'], $pagination['show']);
        
        $nodes = Array();
        $tree = Array();
        
        foreach ($elements as $page) {
            $nodes[$page['id']] = array_merge($page, Array(
                'children' => Array()
            ));
        }
        
        foreach ($nodes as &$node) {
            if ($node['parent'] == 0 || array_key_exists($node['parent'], $nodes) == false)
                $tree[] = &$node;
            else
                $nodes[$node['parent']]['children'][] = &$node;
        }
        
        unset($node);
        unset($nodes);
        
        return $tree;
    }
    
    private function createPagesListOnlyMenuName($pagesListHierarchy, &$tag, &$parentId, &$elements, &$count) {
        foreach ($pagesListHierarchy as $key => $value) {
            if ($value['parent'] == null) {
                $count = 0;
                
                $tag = "-";
            }
            else if ($value['parent'] == $parentId) {
                $count ++;
                
                $tag .= "-";
            }
            else {
                $count --;
                
                $tag = substr($tag, 0, $count);
            }
            
            $parentId = $value['id'];
            
            $elements[$value['id']] = "|$tag| " . $value['title'];
            
            if (count($value['children']) > 0)
                $this->createPagesListOnlyMenuName($value['children'], $tag, $parentId, $elements, $count);
        }
        
        return $elements;
    }
}