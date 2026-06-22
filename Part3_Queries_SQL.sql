USE pubs;
-- Part 3: Item 1
SELECT DISTINCT
    a.au_fname,
    a.au_lname,
    a.city AS author_city,
    a.state AS author_state,
    p.pub_name,
    p.city AS pub_city,
    p.state AS pub_state
FROM authors a
INNER JOIN titleauthor ta ON a.au_id = ta.au_id
INNER JOIN titles t ON ta.title_id = t.title_id
INNER JOIN publishers p ON t.pub_id = p.pub_id
WHERE a.city <> p.city 
  AND a.state <> p.state;


-- Part 3: Item 2
SELECT *
FROM authors
WHERE au_id NOT IN (
    SELECT DISTINCT au_id 
    FROM titleauthor
);


-- Part 3: Item 3
SELECT a.*
FROM authors a
LEFT JOIN titleauthor ta ON a.au_id = ta.au_id
WHERE ta.title_id IS NULL;


-- Part 3: Item 4
SELECT 
    a.au_fname, 
    a.au_lname
FROM authors a
LEFT JOIN titleauthor ta ON a.au_id = ta.au_id
GROUP BY a.au_id, a.au_fname, a.au_lname
HAVING COUNT(ta.title_id) = 0;


-- Part 3: Item 5
SELECT
    CONCAT(a.au_fname, ' ', a.au_lname) AS Author,
    t.title AS Title,
    t.price AS Price
FROM authors a
INNER JOIN titleauthor ta ON a.au_id = ta.au_id
INNER JOIN titles t ON ta.title_id = t.title_id
WHERE t.title_id = (
    SELECT MIN(t2.title_id)
    FROM titleauthor ta2
    INNER JOIN titles t2 ON ta2.title_id = t2.title_id
    WHERE ta2.au_id = a.au_id
      AND t2.price = (
          SELECT MAX(t3.price)
          FROM titleauthor ta3
          INNER JOIN titles t3 ON ta3.title_id = t3.title_id
          WHERE ta3.au_id = a.au_id
      )
)
ORDER BY Author;