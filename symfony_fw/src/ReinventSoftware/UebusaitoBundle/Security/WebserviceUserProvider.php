<?php
namespace ReinventSoftware\UebusaitoBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

use ReinventSoftware\UebusaitoBundle\Entity\User;

class WebserviceUserProvider implements UserProviderInterface {
    // Vars
    private $entityManager;
    
    // Properties
    
    // Functions public
    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }
    
    public function loadUserByUsername($username) {
        $user = $this->entityManager->getRepository("UebusaitoBundle:User")->loadUserByUsername($username);

        if ($user !== null)
            return $user;
        else
            throw new UsernameNotFoundException(sprintf("Username '%s' does not exist.", $username));
    }

    public function refreshUser(UserInterface $user) {
        if (!$user instanceof User)
            throw new UnsupportedUserException(sprintf("Instances of '%s' are not supported.", get_class($user)));
        
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class) {
        return User::class === $class;
    }
    
    // Functions private
}