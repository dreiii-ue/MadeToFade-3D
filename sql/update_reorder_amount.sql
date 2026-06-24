-- Reorder amount / stock auto-add update
-- If a line says duplicate column, that column already exists and you may ignore it.

ALTER TABLE reorder_requests
ADD COLUMN reorder_amount INT(11) NOT NULL DEFAULT 1 AFTER supplier_email;

ALTER TABLE reorder_requests
ADD COLUMN stock_added VARCHAR(5) NOT NULL DEFAULT 'No' AFTER status;

ALTER TABLE reorder_requests
ADD COLUMN completed_at DATETIME DEFAULT NULL AFTER created_at;
