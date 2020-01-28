<?php

namespace App\Entity;

interface OwnedResource
{
    public function getOwner(): User;
}
