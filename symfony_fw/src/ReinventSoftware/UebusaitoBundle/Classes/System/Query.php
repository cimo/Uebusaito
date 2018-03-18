<?php
namespace ReinventSoftware\UebusaitoBundle\Classes\System;

class Query {
    // Vars
    private $connection;
    
    // Properties
      
    // Functions public
    public function __construct($connection) {
        $this->connection = $connection;
    }
    
    public function selectUserWithHelpCodeDatabase($helpCode) {
        $query = $this->connection->prepare("SELECT * FROM users
                                                WHERE help_code IS NOT NULL
                                                AND help_code = :helpCode");
        
        $query->bindValue(":helpCode", $helpCode);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectRoleUserDatabase($roleId, $modify = false) {
        $roleIdExplode = explode(",", $roleId);
        array_pop($roleIdExplode);
        
        $level = Array();
        
        foreach($roleIdExplode as $key => $value) {
            $query = $this->connection->prepare("SELECT level FROM roles_users
                                                    WHERE id = :value");
            
            $query->bindValue(":value", $value);
            
            $query->execute();
            
            $row = $query->fetch();
            
            if ($modify == true)
                array_push($level, ucfirst(strtolower(str_replace("ROLE_", "", $row['level']))));
            else
                array_push($level, $row['level']);
        }
        
        return $level;
    }
    
    public function selectAllRoleUserDatabase($change = false) {
        $query = $this->connection->prepare("SELECT * FROM roles_users");
        
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
    
    public function selectSettingDatabase() {
        $query = $this->connection->prepare("SELECT * FROM settings
                                                WHERE id = :id");
        
        $query->bindValue(":id", 1);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectLanguageDatabase($code) {
        $query = $this->connection->prepare("SELECT * FROM languages
                                                WHERE code = :code");
        
        $query->bindValue(":code", $code);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllLanguageDatabase() {
        $query = $this->connection->prepare("SELECT * FROM languages");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectPageDatabase($language, $id) {
        $query = $this->connection->prepare("SELECT pages.*,
                                                pages_titles.$language AS title,
                                                pages_arguments.$language AS argument,
                                                pages_menu_names.$language AS menu_name
                                                FROM pages, pages_titles, pages_arguments, pages_menu_names
                                            WHERE pages.id = :id
                                            AND pages_titles.id = pages.id
                                            AND pages_arguments.id = pages.id
                                            AND pages_menu_names.id = pages.id
                                            ORDER BY COALESCE(parent, position_in_menu), position_in_menu");
        
        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllPageDatabase($language, $search = null) {
        if ($search == null) {
            $query = $this->connection->prepare("SELECT pages.*,
                                                    pages_titles.$language AS title,
                                                    pages_arguments.$language AS argument,
                                                    pages_menu_names.$language AS menu_name
                                                FROM pages, pages_titles, pages_arguments, pages_menu_names
                                                WHERE pages_titles.id = pages.id
                                                AND pages_arguments.id = pages.id
                                                AND pages_menu_names.id = pages.id
                                                ORDER BY COALESCE(parent, position_in_menu), position_in_menu");
        }
        else {
            $query = $this->connection->prepare("SELECT pages.*,
                                                    pages_titles.$language AS title,
                                                    pages_arguments.$language AS argument,
                                                    pages_menu_names.$language AS menu_name
                                                FROM pages, pages_titles, pages_arguments, pages_menu_names
                                                WHERE pages_titles.id = pages.id
                                                AND pages_arguments.id = pages.id
                                                AND pages_menu_names.id = pages.id
                                                AND pages.only_link = :onlyLink
                                                AND pages.id > :idStart
                                                AND (pages_titles.$language LIKE :search
                                                    OR pages_arguments.$language LIKE :search
                                                    OR pages_menu_names.$language LIKE :search)");
            
            $query->bindValue(":onlyLink", 0);
            $query->bindValue(":idStart", 5);
            $query->bindValue(":search", "%$search%");
        }
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectAllPageChildrenDatabase($parent) {
        $query = $this->connection->prepare("SELECT * FROM pages
                                                WHERE parent = :parent");

        $query->bindValue(":parent", $parent);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectAllPageParentDatabase($parent) {
        if ($parent != null) {
            $query = $this->connection->prepare("SELECT * FROM pages
                                                    WHERE parent = :parent
                                                    ORDER BY COALESCE(parent, position_in_menu), position_in_menu");

            $query->bindValue(":parent", $parent);
        }
        else
            $query = $this->connection->prepare("SELECT * FROM pages
                                                    WHERE parent is NULL
                                                    ORDER BY COALESCE(parent, position_in_menu), position_in_menu");
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectPageCommentDatabase($id) {
        $query = $this->connection->prepare("SELECT * FROM pages_comments
                                                WHERE id = :id");

        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
        
    public function selectAllPageCommentDatabase($pageId) {
        $query = $this->connection->prepare("SELECT * FROM pages_comments
                                                WHERE page_id = :pageId");

        $query->bindValue(":pageId", $pageId);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectUserDatabase($value) {
        if (is_numeric($value) == true) {
            $query = $this->connection->prepare("SELECT * FROM users
                                                    WHERE id = :id");
            
            $query->bindValue(":id", $value);
        }
        else if (filter_var($value, FILTER_VALIDATE_EMAIL) !== false) {
            $query = $this->connection->prepare("SELECT * FROM users
                                                    WHERE email = :email");
            
            $query->bindValue(":email", $value);
        }
        else {
            $query = $this->connection->prepare("SELECT * FROM users
                                                    WHERE username = :username");
            
            $query->bindValue(":username", $value);
        }
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllUserDatabase($idExclude = 0) {
        $query = $this->connection->prepare("SELECT * FROM users
                                                WHERE id != :idExclude");
        
        $query->bindValue(":idExclude", $idExclude);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    public function selectModuleDatabase($id) {
        $query = $this->connection->prepare("SELECT * FROM modules
                                                WHERE id = :id");

        $query->bindValue(":id", $id);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllModuleDatabase($id = null, $position = null) {
        if ($id == null && $position != null) {
            $query = $this->connection->prepare("SELECT * FROM modules
                                                    WHERE position = :position
                                                    ORDER BY COALESCE(position, rank_in_column), rank_in_column");
            
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
    
    public function selectPaymentWithTransactionDatabase($transaction) {
        $query = $this->connection->prepare("SELECT * FROM payments
                                                WHERE transaction = :transaction");
        
        $query->bindValue(":transaction", $transaction);
        
        $query->execute();
        
        return $query->fetch();
    }
    
    public function selectAllPaymentDatabase($userId) {
        $query = $this->connection->prepare("SELECT * FROM payments
                                                WHERE user_id = :userId");
        
        $query->bindValue(":userId", $userId);
        
        $query->execute();
        
        return $query->fetchAll();
    }
    
    // Functions private
}