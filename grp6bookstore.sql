-- Create the database and select it
CREATE DATABASE IF NOT EXISTS pubs;
USE pubs;

-- ==========================================
-- 1. PARENT TABLES (Must be created first)
-- ==========================================

DROP TABLE IF EXISTS authors;
CREATE TABLE authors (
    au_id       VARCHAR(11) NOT NULL,
    au_lname    VARCHAR(40) NOT NULL,
    au_fname    VARCHAR(20) NOT NULL,
    phone       CHAR(12)    DEFAULT NULL,
    address     VARCHAR(40) DEFAULT NULL,
    city        VARCHAR(20) DEFAULT NULL,
    state       CHAR(2)     DEFAULT NULL,
    zip         CHAR(5)     DEFAULT NULL,
    contract    TINYINT(1)  NOT NULL, 
    PRIMARY KEY (au_id)
);

DROP TABLE IF EXISTS publishers;
CREATE TABLE publishers (
    pub_id      CHAR(4)     NOT NULL,
    pub_name    VARCHAR(40) DEFAULT NULL,
    city        VARCHAR(20) DEFAULT NULL,
    state       CHAR(2)     DEFAULT NULL,
    country     VARCHAR(30) DEFAULT NULL,
    PRIMARY KEY (pub_id)
);

DROP TABLE IF EXISTS jobs;
CREATE TABLE jobs (
    job_id      SMALLINT    NOT NULL AUTO_INCREMENT,
    job_desc    VARCHAR(50) NOT NULL,
    min_lvl     TINYINT     NOT NULL,
    max_lvl     TINYINT     NOT NULL,
    PRIMARY KEY (job_id)
);

-- ==========================================
-- 2. CHILD TABLES (Depend on tables above)
-- ==========================================

-- Depends on: publishers
DROP TABLE IF EXISTS titles;
CREATE TABLE titles (
    title_id    VARCHAR(6)      NOT NULL,
    title       VARCHAR(80)     NOT NULL,
    type        CHAR(12)        DEFAULT NULL,
    pub_id      CHAR(4)         DEFAULT NULL,
    price       DECIMAL(10, 2)  DEFAULT NULL,
    advance     DECIMAL(10, 2)  DEFAULT NULL,
    royalty     INT             DEFAULT NULL,
    ytd_sales   INT             DEFAULT NULL,
    notes       VARCHAR(200)    DEFAULT NULL,
    pubdate     DATETIME        DEFAULT NULL,
    PRIMARY KEY (title_id),
    CONSTRAINT fk_titles_pub FOREIGN KEY (pub_id) 
        REFERENCES publishers(pub_id) 
        ON DELETE SET NULL 
        ON UPDATE CASCADE
);

-- Depends on: jobs, publishers
DROP TABLE IF EXISTS employee;
CREATE TABLE employee (
    emp_id      CHAR(9)         NOT NULL,
    fname       VARCHAR(20)     NOT NULL,
    minit       CHAR(1)         DEFAULT NULL,
    lname       VARCHAR(30)     NOT NULL,
    job_id      SMALLINT        NOT NULL,
    job_lvl     TINYINT         DEFAULT NULL,
    pub_id      CHAR(4)         NOT NULL,
    hire_date   DATETIME        DEFAULT NULL,
    PRIMARY KEY (emp_id),
    CONSTRAINT fk_emp_job FOREIGN KEY (job_id) 
        REFERENCES jobs(job_id) 
        ON UPDATE CASCADE,
    CONSTRAINT fk_emp_pub FOREIGN KEY (pub_id) 
        REFERENCES publishers(pub_id) 
        ON UPDATE CASCADE
);

-- Depends on: authors, titles
DROP TABLE IF EXISTS titleauthor;
CREATE TABLE titleauthor (
    au_id       VARCHAR(11)     NOT NULL,
    title_id    VARCHAR(6)      NOT NULL,
    au_ord      TINYINT         DEFAULT NULL,
    royaltyper  INT             DEFAULT NULL,
    PRIMARY KEY (au_id, title_id),
    CONSTRAINT fk_ta_author FOREIGN KEY (au_id) 
        REFERENCES authors(au_id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_ta_title FOREIGN KEY (title_id) 
        REFERENCES titles(title_id) 
        ON DELETE CASCADE
);

-- ==========================================
-- 3. SAMPLE DATA (Required for Part 3 Queries)
-- ==========================================

-- 1. Insert Publishers
INSERT INTO publishers VALUES 
('P001', 'New Moon Books', 'Boston', 'MA', 'USA'),
('P002', 'Binnet & Hardley', 'Washington', 'DC', 'USA'),
('P003', 'Algodata Infosystems', 'Berkeley', 'CA', 'USA');

-- 2. Insert Authors
-- Note: 'A004' is the "Lazy Author" with no books (for Query #2 and #3)
INSERT INTO authors VALUES 
('A001', 'White', 'Johnson', '408-496-7223', '10932 Bigge Rd.', 'Menlo Park', 'CA', '94025', 1),
('A002', 'Green', 'Marjorie', '415-986-7020', '309 63rd St. #411', 'Oakland', 'CA', '94618', 1),
('A003', 'Carson', 'Cheryl', '415-548-7723', '589 Darwin Ln.', 'Berkeley', 'CA', '94705', 1),
('A004', 'Lazy', 'Larry', '555-555-5555', '123 Nowhere St', 'New York', 'NY', '10001', 1); 

-- 3. Insert Titles
INSERT INTO titles VALUES 
('T001', 'The Busy Executive', 'business', 'P003', 19.99, 5000.00, 10, 4095, 'Helpful notes', NOW()),
('T002', 'Cooking with Computers', 'business', 'P003', 11.95, 5000.00, 10, 3876, 'Surprising recipes', NOW()),
('T003', 'You Can Combat Computer Stress', 'psychology', 'P001', 2.99, 10125.00, 24, 18722, 'Stress relief', NOW()),
('T004', 'Silicon Valley Gastronomic Treats', 'mod_cook', 'P002', 19.99, 0.00, 12, 2032, 'Food notes', NOW());

-- 4. Insert TitleAuthor (Linking authors to books)
INSERT INTO titleauthor VALUES 
('A001', 'T001', 1, 100), -- Johnson White wrote "Busy Executive"
('A002', 'T001', 2, 50),  -- Marjorie Green co-wrote "Busy Executive"
('A002', 'T004', 1, 100), -- Marjorie Green wrote "Gastronomic Treats"
('A003', 'T003', 1, 100); -- Cheryl Carson wrote "Computer Stress"
-- Note: A004 (Larry Lazy) is intentionally left out here.

-- 5. Insert Jobs
INSERT INTO jobs VALUES 
(1, 'Editor', 5, 10),
(2, 'Sales Manager', 10, 15);

-- 6. Insert Employees
INSERT INTO employee VALUES 
('EMP001', 'Paolo', 'M', 'Accorti', 1, 9, 'P001', NOW()),
('EMP002', 'Pedro', 'J', 'Afonso', 2, 14, 'P003', NOW());