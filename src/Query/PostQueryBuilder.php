<?php

namespace App\Query;

use Symfony\Component\Intl\Exception\NotImplementedException;
use function join;

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
    private const QUERY = <<<SQL
        SELECT rated.id, rated.title, rated.rating, rated.current_vote, u.name,
       rated.created_at, rated.updated_at, rated.author_id, u.name, u.email,
       rated.district_id, rated.district_name,
       -- calculate the number of comments for every post
       sum(CASE
               WHEN c.id IS NULL THEN 0
               ELSE 1
           END)
           AS comment_count
FROM (
         SELECT p.id, p.title, p.author_id, p.created_at, p.updated_at, p.district_id,
                d.name AS district_name,
                (p.total_upvotes - p.total_downvotes) AS RATING,
                -- calculate the voting status for current user
                sum(CASE
                        WHEN pv.user_id = {USER_ID} THEN pv.value
                        ELSE 0
                    END) AS current_vote
         FROM post p
         LEFT JOIN post_vote pv ON p.id = pv.post_id
         JOIN district d on p.district_id = d.id
         {INNER_WHERE}
         GROUP BY p.id, p.title, p.author_id, p.created_at, p.updated_at, p.district_id, district_name
     ) rated
         LEFT JOIN `comment` c ON rated.id = c.post_id
         JOIN user u on rated.author_id = u.id
GROUP BY rated.id, rated.title, rated.rating, rated.current_vote, u.name,
         rated.created_at, rated.updated_at, u.id, u.name, u.email,
         rated.district_id, rated.district_name
SQL;

    private $currentUserId = 0;

    /** @var array */
    private $districts;

    private $postId;

    /**
     * @param $currentUserId
     * @return $this
     */
    public function setCurrentUserId($currentUserId): self
    {
        $this->currentUserId = $currentUserId;
        return $this;
    }

    /**
     * @param $districts
     * @return $this
     */
    public function setDistricts(array $districts): self
    {
        $this->districts = $districts;
        return $this;
    }

    /**
     * @param mixed $postId
     * @return PostQueryBuilder
     */
    public function setPostId($postId): self
    {
        $this->postId = $postId;
        return $this;
    }

    public function build(): string
    {
        $result = self::QUERY;
        $result = $this->replacePlaceholder($result, 'USER_ID', $this->currentUserId);
        $conditions = [];
        $innerWhere = '';
        if ($this->districts !== null) {
            $conditions[] = 'p.district_id IN ( ' . join(',', $this->districts) . ')';
        }
        if ($this->postId !== null) {
            $conditions[] = 'p.id = ' . $this->postId;
        }
        if (!empty($conditions)) {
            $innerWhere = 'WHERE ' . join(' AND ', $conditions);
        }
        $result = $this->replacePlaceholder($result, 'INNER_WHERE', $innerWhere);
        return $result;
    }

    /**
     * Replace placeholder in string $sql in form {placeholder_name} with $value
     * @param string $sql
     * @param string $placeholderName
     * @param string $value
     * @return string
     */
    private function replacePlaceholder(string $sql, string $placeholderName, string $value): string
    {
        return str_replace('{' . $placeholderName . '}', $value, $sql);
    }
}
