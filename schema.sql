CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,           -- bcrypt hash via password_hash()
    role ENUM('retailer', 'dispatcher', 'rider') NOT NULL,
    phone VARCHAR(20) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,  -- lets dispatcher "retire" a rider without deleting history
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_users_role (role)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: deliveries
-- ------------------------------------------------------------
CREATE TABLE deliveries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    retailer_id INT UNSIGNED NOT NULL,
    rider_id INT UNSIGNED DEFAULT NULL,       -- NULL until a dispatcher assigns a rider

    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    delivery_location VARCHAR(255) NOT NULL,
    package_description VARCHAR(255) NOT NULL,
    notes TEXT DEFAULT NULL,

    status ENUM('PENDING', 'ASSIGNED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED')
        NOT NULL DEFAULT 'PENDING',

    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_deliveries_retailer
        FOREIGN KEY (retailer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_deliveries_rider
        FOREIGN KEY (rider_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_deliveries_status (status),
    INDEX idx_deliveries_retailer (retailer_id),
    INDEX idx_deliveries_rider (rider_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: delivery_status_history
-- Full audit trail of every status change (RULE 6).
-- ------------------------------------------------------------
CREATE TABLE delivery_status_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    delivery_id INT UNSIGNED NOT NULL,
    status ENUM('PENDING', 'ASSIGNED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED') NOT NULL,
    changed_by INT UNSIGNED NOT NULL,          -- users.id of whoever triggered the change
    note VARCHAR(255) DEFAULT NULL,            -- e.g. "Assigned to rider X"
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_history_delivery
        FOREIGN KEY (delivery_id) REFERENCES deliveries(id) ON DELETE CASCADE,
    CONSTRAINT fk_history_user
        FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_history_delivery (delivery_id)
) ENGINE=InnoDB;
