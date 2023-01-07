<?php

namespace App\Security\Voter;

use App\Entity\Account;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountVoter extends Voter
{
    public const SHOW = 'SHOW';
    public const DELETE = 'DELETE';

    public function __construct(public Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::SHOW, self::DELETE])
            && $subject instanceof \App\Entity\Account;
    }

    /**
     * @param string $attribute
     * @param Account $account
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $account, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }


        $accessIsGranted = match ($attribute) {
            self::SHOW => $this->canShow($account, $user),  // user is account holder | user is account manager | admin
            self::DELETE => $this->security->isGranted('ROLE_ADMIN') //only by admins
        };

        return $accessIsGranted;

//        // ... (check conditions and return true to grant permission) ...
//        switch ($attribute) {
//            case self::SHOW:
//                // logic to determine if the user can EDIT
//                // return true or false
//                return true;
//                break;
//            case self::DELETE:
//                // logic to determine if the user can VIEW
//                // return true or false
//                break;
//        }
//
//        return false;
    }

    protected function canShow($account, $user): bool
    {
        return $account->getAccountHolder() === $user
            || $account->getAccountManager() === $user
            || $this->security->isGranted('ROLE_ADMIN');
    }
}
