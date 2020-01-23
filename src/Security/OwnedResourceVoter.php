<?php

namespace App\Security;

use App\Entity\OwnedResource;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OwnedResourceVoter extends Voter
{
    private const DELETE = 'delete';
    private const EDIT = 'edit';

    protected function supports(string $attribute, $subject)
    {
        if (!in_array($attribute, [self::DELETE, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof OwnedResource) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to supports
        /** @var OwnedResource $resource */
        $resource = $subject;

        switch ($attribute) {
            case self::DELETE:
            case self::EDIT:
                return $this->isOwner($user, $resource);
            default:
                throw new \LogicException('This code should not be reached!');
        }
    }

    // todo: once moderation is implemented check for that here too

    private function isOwner(User $user, OwnedResource $resource)
    {
        return $user === $resource->getOwner();
    }
}
