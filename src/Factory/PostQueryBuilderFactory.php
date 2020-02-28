<?php

namespace App\Factory;

use App\Query\PostQueryBuilder;

class PostQueryBuilderFactory
{
    public function makePostQueryBuilder(): PostQueryBuilder
    {
        return new PostQueryBuilder();
    }
}
