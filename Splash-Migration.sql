ALTER TABLE guests ADD event_id INT NOT NULL AFTER id;
ALTER TABLE guests ADD CONSTRAINT fk_event_id FOREIGN KEY (event_id) REFERENCES events(id);
