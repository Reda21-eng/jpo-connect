USE jpo_connect;
ALTER TABLE jpos 
ADD COLUMN author_id INT,
ADD FOREIGN KEY (author_id) REFERENCES users(id);
