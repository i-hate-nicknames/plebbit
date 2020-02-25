<?php

namespace App\Query;

use Symfony\Component\Intl\Exception\NotImplementedException;

/**
 * Class PostQueryBuilder allows building an SQL query for fetching post(s)
 * with additional info (number of comments, rating).
 * Builder fluent interface allows readable configuration of the query, instead
 * of direct SQL string manipulation
 * Builtin Doctrine query builder is not used due to the complex nature of the query
 * @package App\Query
 */
class PostQueryBuilder
{


    private $currentUserId;

    private $districtIds;

    /**
     * @param $currentUserId
     * @return $this
     */
    public function setCurrentUserId($currentUserId): self
    {
        $this->currentUserId = $currentUserId;
    }

    /**
     * @param $districtIds
     * @return $this
     */
    public function setDistrictIds($districtIds): self
    {
        $this->districtIds = $districtIds;
    }

    public function build(): string
    {
        throw new NotImplementedException('sorry');
    }
}
