<?php
namespace ReinventSoftware\UebusaitoBundle\Classes;

class Query {
    // Vars
    private $entityManager;
    
    private $connection;
    
    // Properties
      
    // Functions public
    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
        
        $this->connection = $this->entityManager->getConnection();
    }
    
    public function selectUserIdWithHelpCodeFromDatabase($helpCode) {
        $query = $this->connection->prepare("SELECT id FROM users
                                                WHERE help_code IS NOT NULL
                                                AND help_code = :helpCode");
        
        $query->bindValue(":helpCode", $helpCode);
        
        $query->execute();
        
        $rows = $query->fetch();
        
        $id = $rows['id'];
        
        return $id;
    }
    
    public function selectUserRoleLevelFromDatabase($roleId, $modify = false) {
        $rolesExplode = explode(",", $roleId);
        array_pop($rolesExplode);
        
        $level = Array();
        
        foreach($rolesExplode as $key => $value) {
            $query = $this->connection->prepare("SELECT level FROM users_roles
                                                    WHERE id = :value");
            
            $query->bindValue(":value", $value);
            
            $query->execute();
            
            $rows = $query->fetch();
            
            if ($modify == true)
                array_push($level, ucfirst(strtolower(str_replace("ROLE_", "", $rows['level']))));
            else
                array_push($level, $rows['level']);
        }
        
        return $level;
    }
    
    public function selectAllUserRolesFromDatabase($change = false) {
        $query = $this->connection->prepare("SELECT * FROM users_roles");
        
        $query->execute();
        
        $rows = $query->fetchAll();
        
        if ($change == true) {
            foreach ($rows as &$value) {
                $value = str_replace("ROLE_", "", $value);
                $value = array_map("strtolower", $value);
                $value = array_map("ucfirst", $value);
            }
        }
        
        return $rows;
    }
    
    public function selectAllSettingsFromDatabase() {
        $query = $this->connection->prepare("SELECT * FROM settings");
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectLanguageFromDatabase($value) {
        if (is_numeric($value) == true)
            $query = $this->connection->prepare("SELECT * FROM languages
                                                    WHERE id = :value");
        else
            $query = $this->connection->prepare("SELECT * FROM languages
                                                    WHERE code = :value");
        
        $query->bindValue(":value", $value);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllLanguagesFromDatabase() {
        $query = $this->connection->prepare("SELECT * FROM languages");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectPageFromDatabase($language, $id) {
        $query = $this->connection->prepare("SELECT pages.*,
                                                    pages_titles.$language AS title,
                                                    pages_arguments.$language AS argument,
                                                    pages_menu_names.$language AS menu_name
                                                    FROM pages, pages_titles, pages_arguments, pages_menu_names
                                                WHERE pages.id = :id
                                                AND pages_titles.id = pages.id
                                                AND pages_arguments.id = pages.id
                                                AND pages_menu_names.id = pages.id
                                                ORDER BY pages.parent");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllPagesFromDatabase($language, $search = null) {
        if ($search == null) {
            $query = $this->connection->prepare("SELECT pages.*,
                                                        pages_titles.$language AS title,
                                                        pages_arguments.$language AS argument,
                                                        pages_menu_names.$language AS menu_name
                                                        FROM pages, pages_titles, pages_arguments, pages_menu_names
                                                    WHERE pages_titles.id = pages.id
                                                    AND pages_arguments.id = pages.id
                                                    AND pages_menu_names.id = pages.id");
        }
        else {
            $query = $this->connection->prepare("SELECT pages.*,
                                                        pages_titles.$language AS title,
                                                        pages_arguments.$language AS argument
                                                    FROM pages, pages_titles, pages_arguments
                                                    WHERE pages_titles.id = pages.id
                                                    AND pages_arguments.id = pages.id
                                                    AND pages.only_link = :onlyLink
                                                    AND pages.id NOT IN (SELECT parent FROM pages WHERE parent is NOT NULL)
                                                    AND (pages_titles.$language LIKE :search
                                                    OR pages_arguments.$language LIKE :search)");
            
            $query->bindValue(":onlyLink", 0);
            $query->bindValue(":search", "%$search%");
        }
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectAllPageChildrenIdFromDatabase($page) {
        $query = $this->connection->prepare("SELECT id FROM pages
                                                WHERE parent = :id");
        
        $query->bindValue(":id", $page->getId());
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectUserFromDatabase($type, $value) {
        if ($type == "id") {
            $query = $this->connection->prepare("SELECT * FROM users
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $value);
        }
        else if ($type == "email") {
            $query = $this->connection->prepare("SELECT * FROM users
                                                    WHERE email = :email");
            
            $query->bindValue(":email", $value);
        }
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllUsersFromDatabase($idExcluded = 0) {
        $query = $this->connection->prepare("SELECT * FROM users
                                                WHERE id != :idExcluded");
        
        $query->bindValue(":idExcluded", $idExcluded);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectModuleFromDatabase($id) {
        $query = $this->connection->prepare("SELECT * FROM modules
                                                WHERE id = :id");

        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllModulesFromDatabase($id = null, $position = null) {
        if ($id == null && $position != null) {
            $query = $this->connection->prepare("SELECT * FROM modules
                                                    WHERE position = :position
                                                    ORDER BY position, sort");
            
            $query->bindValue(":position", $position);
        }
        else if ($id != null && $position != null) {
            $query = $this->connection->prepare("SELECT * FROM modules
                                                    WHERE position = :position
                                                    UNION
                                                SELECT * FROM modules
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $id);
            $query->bindValue(":position", $position);
        }
        else if ($id == null && $position == null)
            $query = $this->connection->prepare("SELECT * FROM modules");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectPaymentWithTransactionFromDatabase($transaction) {
        $query = $this->connection->prepare("SELECT * FROM payments
                                                WHERE transaction = :transaction");
        
        $query->bindValue(":transaction", $transaction);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllPaymentsFromDatabase($userId) {
        $query = $this->connection->prepare("SELECT * FROM payments
                                                WHERE user_id = :userId");
        
        $query->bindValue(":userId", $userId);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectAllPaymentsUserFromDatabase($userId) {
        $query = $this->connection->prepare("SELECT * FROM payments
                                                WHERE user_id != :userId
                                                AND user_id > 1");
        
        $query->bindValue(":userId", $userId);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    // Functions private
}