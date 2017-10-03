<?php
namespace ReinventSoftware\UebusaitoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserRepository extends EntityRepository implements UserProviderInterface {
    // Vars
    
    // Properties
    
    // Functions public
    public function loadUserByUsername($username) {
        $user = $this->findByUsernameOrEmail($username);
        
        return $user;
    }

    public function refreshUser(UserInterface $user) {
        $class = get_class($user);
        
        if ($this->supportsClass($class) == false)
            throw new UnsupportedUserException(sprintf("Instances of %s are not supported.", $class));
        
        $refreshedUser = $this->find($user->getId());
        
        if ($refreshedUser == false)
            throw new UsernameNotFoundException(sprintf("User with id %s not found", json_encode($user->getId())));

        return $refreshedUser;
    }

    public function supportsClass($class) {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }
    
    // Functions private
    private function findByUsernameOrEmail($value) {
        return $this->createQueryBuilder("user")
            ->where("user.username = :username OR user.email = :email")
            ->setParameter("username", $value)
            ->setParameter("email", $value)
            ->getQuery()
            ->getOneOrNullResult();
    }
}