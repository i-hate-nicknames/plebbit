SELECT post.id,
       sum(CASE WHEN c.id IS NULL THEN 0 ELSE 1 END) AS comment_count,
       1.5/(.1+TIMESTAMPDIFF(SECOND , post.created_at, NOW())) AS k
FROM post
         LEFT JOIN comment c on post.id = c.post_id
GROUP BY post.id