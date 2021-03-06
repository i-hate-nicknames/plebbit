SELECT rated.id, rated.title, rated.rating, rated.current_vote, u.name,
       rated.created_at, rated.updated_at, rated.author_id, u.name, u.email,
       -- calculate the number of comments for every post
       sum(CASE
               WHEN c.id IS NULL THEN 0
               ELSE 1
           END)
           AS comment_count
FROM (
         SELECT p.id, p.title, p.author_id, p.created_at, p.updated_at,
                -- sum all the ratings, the posts that do not have a rating
                -- will get 0 due to the following CASE
                sum(CASE
                        WHEN pv.value IS NULL THEN 0
                        ELSE pv.value
                    END) AS rating,
                -- calculate the voting status for current user
                sum(CASE
                        WHEN pv.user_id = 1 THEN pv.value
                        ELSE 0
                    END) AS current_vote
         FROM post p
                  LEFT JOIN post_vote pv ON p.id = pv.post_id
              -- filter only a single post
              -- WHERE p.id = 1
              -- filter by district
              -- WHERE p.district_id = :id
         GROUP BY p.id, p.title, p.author_id, p.created_at, p.updated_at
     ) rated
         LEFT JOIN `comment` c ON rated.id = c.post_id
         JOIN user u on rated.author_id = u.id
GROUP BY rated.id, rated.title, rated.rating, rated.current_vote, u.name,
         rated.created_at, rated.updated_at, u.id, u.name, u.email