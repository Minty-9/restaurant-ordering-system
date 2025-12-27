CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(20) NOT NULL UNIQUE,

    -- Customer info (nullable for walk-ins)
    customer_name VARCHAR(150) NULL,
    customer_phone VARCHAR(20) NULL,
    customer_address VARCHAR(255) NULL,

    -- Order classification
    order_type ENUM('walk-in','online') NOT NULL DEFAULT 'walk_in',
    source ENUM('walk_in','online','admin') NOT NULL DEFAULT 'online',

    -- Dine-in support
    table_number VARCHAR(10) NULL,

    -- Payment
    payment_status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
    payment_method ENUM('online','staff') NOT NULL DEFAULT 'online',

    -- Order lifecycle
    status ENUM('new','preparing','ready','completed','cancelled')
        NOT NULL DEFAULT 'new',

    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price_each DECIMAL(10,2) NOT NULL,
);

