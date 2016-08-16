SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `sales_order_phase` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Phase ID',
    `code` VARCHAR(20) NOT NULL COMMENT 'Phase code',
    `name` VARCHAR(20) DEFAULT '' COMMENT 'Phase name',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_SALES_ORDER_PHASE_CODE UNIQUE (`code`)
);

CREATE TABLE IF NOT EXISTS `sales_order_status` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Status ID',
    `phase_id` INTEGER NOT NULL COMMENT 'Phase ID',
    `name` VARCHAR(20) DEFAULT '' COMMENT 'Status name',
    `is_default` BOOLEAN DEFAULT 1 COMMENT 'Is default',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_STATUS_PHASE_ID (`phase_id`),
    CONSTRAINT FK_SALES_ORDER_STATUS_PHASE_ID_SALES_ORDER_PHASE_ID FOREIGN KEY (`phase_id`) REFERENCES `sales_order_phase`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `sales_order_phase` VALUES 
(NULL,'pending','Pending'),
(NULL,'pending_payment','Pending Payment'),
(NULL,'processing','Processing'),
(NULL,'complete','Complete'),
(NULL,'canceled','Canceled'),
(NULL,'closed','Close'),
(NULL,'holded','On Hold');

INSERT INTO `sales_order_status` VALUES 
(NULL,1,'Pending',1),
(NULL,2,'Pending Payment',1),
(NULL,3,'Processing',1),
(NULL,4,'Complete',1),
(NULL,5,'Canceled',1),
(NULL,6,'Close',1),
(NULL,7,'On Hold',1);

CREATE TABLE IF NOT EXISTS `sales_cart` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Order ID',
    `customer_id` INTEGER DEFAULT NULL COMMENT 'Customer ID',
    `billing_address_id` INTEGER DEFAULT NULL COMMENT 'Billing address ID',
    `shipping_address_id` INTEGER DEFAULT NULL COMMENT 'Shipping address ID',
    `billing_address` TEXT COMMENT 'Billing address',
    `shipping_address` TEXT COMMENT 'Billing address',
    `is_virtual` BOOLEAN DEFAULT 0 COMMENT 'Is order virtual',
    `free_shipping` BOOLEAN DEFAULT 0 COMMENT 'Is order shipping free',
    `base_currency` CHAR(3) NOT NULL COMMENT 'Base currency code',
    `currency` CHAR(3) NOT NULL COMMENT 'Currency code',
    `shipping_method` VARCHAR(255) DEFAULT NULL COMMENT 'Shipping method',
    `payment_method` VARCHAR(255) DEFAULT NULL COMMENT 'Payment method',
    `base_subtotal` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base subtotal',
    `subtotal` DECIMAL(12,4) DEFAULT 0 COMMENT 'Subtotal',
    `base_shipping` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base shipping fee',
    `shipping` DECIMAL(12,4) DEFAULT 0 COMMENT 'Shipping fee',
    `base_discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base discount',
    `discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Discount',
    `discount_detail` VARCHAR(255) DEFAULT NULL COMMENT 'Discount detail',
    `base_tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base tax',
    `tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Tax',
    `base_total` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base total',
    `total` DECIMAL(12,4) DEFAULT 0 COMMENT 'Total',
    `additional` TEXT COMMENT 'Additional info',
    `customer_note` TEXT COMMENT 'Customer note',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Is active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_CART_STATUS (`status`),
    INDEX IDX_SALES_CART_CUSTOMER_ID (`customer_id`),
    INDEX IDX_SALES_CART_BILLING_ADDRESS_ID (`billing_address_id`),
    INDEX IDX_SALES_CART_SHIPPING_ADDRESS_ID (`shipping_address_id`),
    CONSTRAINT FK_SALES_CART_CUSTOMER_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_CART_BILLING_ADDR_ID_ADDR_ENTITY_ID FOREIGN KEY (`billing_address_id`) REFERENCES `address_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_CART_SHIPPING_ADDR_ID_ADDR_ENTITY_ID FOREIGN KEY (`shipping_address_id`) REFERENCES `address_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_SALES_CART` BEFORE UPDATE ON `sales_cart` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `sales_cart_item` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Order item ID',
    `cart_id` INTEGER NOT NULL COMMENT 'Cart ID',
    `store_id` INTEGER DEFAULT NULL COMMENT 'Store ID',
    `warehouse_id` INTEGER DEFAULT NULL COMMENT 'Warehouse ID',
    `product_id` INTEGER COMMENT 'Product ID',
    `product_name` VARCHAR(255) COMMENT 'Product Name',
    `options` VARCHAR(255) NULL COMMENT 'Options',
    `qty` DECIMAL(12,4) NOT NULL COMMENT 'Quentity',
    `sku` VARCHAR(255) NOT NULL COMMENT 'Sku',
    `is_virtual` BOOLEAN DEFAULT 0 COMMENT 'Is item virtual',
    `free_shipping` BOOLEAN DEFAULT 0 COMMENT 'Is item shipping free',
    `base_price` DECIMAL(12,4) NOT NULL COMMENT 'Base price',
    `price` DECIMAL(12,4) NOT NULL COMMENT 'Price',
    `base_discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base discount',
    `discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Discount',
    `base_tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base tax',
    `tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Tax',
    `base_total` DECIMAL(12,4) NOT NULL COMMENT 'Base total',
    `total` DECIMAL(12,4) NOT NULL COMMENT 'Total',
    `weight` DECIMAL(12,4) NOT NULL COMMENT 'Total weight',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_CART_ITEM_WAREHOUSE_ID (`warehouse_id`),
    INDEX IDX_SALES_CART_ITEM_STORE_ID (`store_id`),
    INDEX IDX_SALES_CART_ITEM_CART_ID (`cart_id`),
    INDEX IDX_SALES_CART_ITEM_PRODUCT_ID (`product_id`),
    INDEX IDX_SALES_CART_ITEM_STATUS (`status`),
    CONSTRAINT FK_SALES_CART_ITEM_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_CART_ITEM_WAREHOUSE_ID_WAREHOUSE_ID FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_CART_ITEM_ORDER_ID_SALES_CART_ID FOREIGN KEY (`cart_id`) REFERENCES `sales_cart`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_CART_ITEM_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_SALES_CART_ITEM` BEFORE UPDATE ON `sales_cart_item` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `sales_order` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Order ID',
    `status_id` INTEGER NOT NULL COMMENT 'Status ID',
    `increment_id` VARCHAR(255) NOT NULL COMMENT 'Increment ID',
    `customer_id` INTEGER DEFAULT NULL COMMENT 'Customer ID',
    `billing_address_id` INTEGER DEFAULT NULL COMMENT 'Billing address ID',
    `shipping_address_id` INTEGER DEFAULT NULL COMMENT 'Shipping address ID',
    `warehouse_id` INTEGER DEFAULT NULL COMMENT 'Warehouse ID',
    `store_id` INTEGER DEFAULT NULL COMMENT 'Store ID',
    `language_id` INTEGER DEFAULT NULL COMMENT 'Language ID',
    `billing_address` TEXT COMMENT 'Billing address',
    `shipping_address` TEXT COMMENT 'Billing address',
    `is_virtual` BOOLEAN DEFAULT 0 COMMENT 'Is order virtual',
    `free_shipping` BOOLEAN DEFAULT 0 COMMENT 'Is order shipping free',
    `base_currency` CHAR(3) NOT NULL COMMENT 'Base currency code',
    `currency` CHAR(3) NOT NULL COMMENT 'Currency code',
    `shipping_method` VARCHAR(255) DEFAULT NULL COMMENT 'Shipping method',
    `payment_method` VARCHAR(255) DEFAULT NULL COMMENT 'Payment method',
    `base_subtotal` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base subtotal',
    `subtotal` DECIMAL(12,4) DEFAULT 0 COMMENT 'Subtotal',
    `base_shipping` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base shipping fee',
    `shipping` DECIMAL(12,4) DEFAULT 0 COMMENT 'Shipping fee',
    `base_discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base discount',
    `discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Discount',
    `discount_detail` VARCHAR(255) DEFAULT NULL COMMENT 'Discount detail',
    `base_tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base tax',
    `tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Tax',
    `base_total` DECIMAL(12,4) NOT NULL COMMENT 'Base total',
    `total` DECIMAL(12,4) NOT NULL COMMENT 'Total',
    `base_total_paid` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base paid total',
    `total_paid` DECIMAL(12,4) DEFAULT 0 COMMENT 'Paid total',
    `base_total_refunded` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base refunded total',
    `total_refunded` DECIMAL(12,4) DEFAULT 0 COMMENT 'Refunded total',
    `additional` TEXT COMMENT 'Additional info',
    `customer_note` TEXT COMMENT 'Customer note',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_STATUS_ID (`status_id`),
    INDEX IDX_SALES_ORDER_INCREMENT_ID (`increment_id`),
    INDEX IDX_SALES_ORDER_CUSTOMER_ID (`customer_id`),
    INDEX IDX_SALES_ORDER_STORE_ID (`store_id`),
    INDEX IDX_SALES_ORDER_LANGUAGE_ID (`language_id`),
    INDEX IDX_SALES_ORDER_WAREHOUSE_ID (`warehouse_id`),
    INDEX IDX_SALES_ORDER_BILLING_ADDRESS_ID (`billing_address_id`),
    INDEX IDX_SALES_ORDER_SHIPPING_ADDRESS_ID (`shipping_address_id`),
    CONSTRAINT FK_SALES_ORDER_STATUS_ID_SALES_ORDER_STATUS_ID FOREIGN KEY (`status_id`) REFERENCES `sales_order_status`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_WAREHOUSE_ID_WAREHOUSE_ID FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_CUSTOMER_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_STORE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_BILLING_ADDR_ID_ADDR_ENTITY_ID FOREIGN KEY (`billing_address_id`) REFERENCES `address_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_SHIPPING_ADDR_ID_ADDR_ENTITY_ID FOREIGN KEY (`shipping_address_id`) REFERENCES `address_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_SALES_ORDER` BEFORE UPDATE ON `sales_order` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `sales_order_item` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Order item ID',
    `order_id` INTEGER NOT NULL COMMENT 'Order ID',
    `product_id` INTEGER COMMENT 'Product ID',
    `product_name` VARCHAR(255) COMMENT 'Product Name',
    `options` VARCHAR(255) NULL COMMENT 'Options',
    `qty` DECIMAL(12,4) NOT NULL COMMENT 'Quentity',
    `sku` VARCHAR(255) NOT NULL COMMENT 'Sku',
    `is_virtual` BOOLEAN DEFAULT 0 COMMENT 'Is item virtual',
    `free_shipping` BOOLEAN DEFAULT 0 COMMENT 'Is item shipping free',
    `base_price` DECIMAL(12,4) NOT NULL COMMENT 'Base price',
    `price` DECIMAL(12,4) NOT NULL COMMENT 'Price',
    `base_discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base discount',
    `discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Discount',
    `base_tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base tax',
    `tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Tax',
    `base_total` DECIMAL(12,4) NOT NULL COMMENT 'Base total',
    `total` DECIMAL(12,4) NOT NULL COMMENT 'Total',
    `weight` DECIMAL(12,4) NOT NULL COMMENT 'Total weight',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_ITEM_ORDER_ID (`order_id`),
    INDEX IDX_SALES_ORDER_ITEM_PRODUCT_ID (`product_id`),
    CONSTRAINT FK_SALES_ORDER_ITEM_ORDER_ID_SALES_ORDER_ID FOREIGN KEY (`order_id`) REFERENCES `sales_order`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_ITEM_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_SALES_ORDER_ITEM` BEFORE UPDATE ON `sales_order_item` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `sales_order_invoice` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Invoice ID',
    `order_id` INTEGER NOT NULL COMMENT 'Order ID',
    `increment_id` VARCHAR(255) NOT NULL COMMENT 'Increment ID',
    `store_id` INTEGER DEFAULT NULL COMMENT 'Store ID',
    `base_currency` CHAR(3) NOT NULL COMMENT 'Base currency code',
    `currency` CHAR(3) NOT NULL COMMENT 'Currency code',
    `base_subtotal` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base subtotal',
    `subtotal` DECIMAL(12,4) DEFAULT 0 COMMENT 'Subtotal',
    `base_shipping` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base shipping fee',
    `shipping` DECIMAL(12,4) DEFAULT 0 COMMENT 'Shipping fee',
    `base_discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base discount',
    `discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Discount',
    `discount_detail` VARCHAR(255) DEFAULT NULL COMMENT 'Discount detail',
    `base_tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base tax',
    `tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Tax',
    `base_total` DECIMAL(12,4) NOT NULL COMMENT 'Base total',
    `total` DECIMAL(12,4) NOT NULL COMMENT 'Total',
    `comment` TEXT COMMENT 'Comment',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_INVOICE_INCREMENT_ID (`increment_id`),
    INDEX IDX_SALES_ORDER_INVOICE_STORE_ID (`store_id`),
    INDEX IDX_SALES_ORDER_INVOICE_ORDER_ID (`order_id`),
    CONSTRAINT FK_SALES_ORDER_INVOICE_ORDER_ID_SALES_ORDER_ID FOREIGN KEY (`order_id`) REFERENCES `sales_order`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_INVOICE_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_SALES_ORDER_INVOICE` BEFORE UPDATE ON `sales_order_invoice` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `sales_order_invoice_item` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Item ID',
    `item_id` INTEGER COMMENT 'Order item ID',
    `invoice_id` INTEGER NOT NULL COMMENT 'Invoice ID',
    `product_id` INTEGER COMMENT 'Product ID',
    `product_name` VARCHAR(255) COMMENT 'Product Name',
    `options` VARCHAR(255) NULL COMMENT 'Options',
    `qty` DECIMAL(12,4) NOT NULL COMMENT 'Quentity',
    `sku` VARCHAR(255) NOT NULL COMMENT 'Sku',
    `base_price` DECIMAL(12,4) NOT NULL COMMENT 'Base price',
    `price` DECIMAL(12,4) NOT NULL COMMENT 'Price',
    `base_discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base discount',
    `discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Discount',
    `base_tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base tax',
    `tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Tax',
    `base_total` DECIMAL(12,4) NOT NULL COMMENT 'Base total',
    `total` DECIMAL(12,4) NOT NULL COMMENT 'Total',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_INVOICE_ITEM_ITEM_ID (`item_id`),
    INDEX IDX_SALES_ORDER_INVOICE_ITEM_INVOICE_ID (`invoice_id`),
    INDEX IDX_SALES_ORDER_INVOICE_ITEM_PRODUCT_ID (`product_id`),
    CONSTRAINT FK_SALES_ORDER_INVOICE_ITEM_ITEM_ID_SALES_ORDER_ITEM_ID FOREIGN KEY (`item_id`) REFERENCES `sales_order_item`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_INVOICE_ITEM_INVOICE_ID_SALES_ORDER_INVOICE_ID FOREIGN KEY (`invoice_id`) REFERENCES `sales_order_invoice`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_INVOICE_ITEM_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_SALES_ORDER_INVOICE_ITEM` BEFORE UPDATE ON `sales_order_invoice_item` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `sales_order_shipment` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Shipment ID',
    `order_id` INTEGER NOT NULL COMMENT 'Order ID',
    `increment_id` VARCHAR(255) NOT NULL COMMENT 'Increment ID',
    `customer_id` INTEGER DEFAULT NULL COMMENT 'Customer ID',
    `shipping_method` VARCHAR(255) DEFAULT NULL COMMENT 'Shipping method',
    `billing_address_id` INTEGER DEFAULT NULL COMMENT 'Billing address ID',
    `shipping_address_id` INTEGER DEFAULT NULL COMMENT 'Shipping address ID',
    `warehouse_id` INTEGER DEFAULT NULL COMMENT 'Warehouse ID',
    `store_id` INTEGER DEFAULT NULL COMMENT 'Store ID',
    `billing_address` TEXT COMMENT 'Billing address',
    `shipping_address` TEXT COMMENT 'Shipping address',
    `comment` TEXT COMMENT 'Comment',
    `status` BOOLEAN DEFAULT 0 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_SHIPMENT_INCREMENT_ID (`increment_id`),
    INDEX IDX_SALES_ORDER_SHIPMENT_STORE_ID (`store_id`),
    INDEX IDX_SALES_ORDER_SHIPMENT_ORDER_ID (`order_id`),
    INDEX IDX_SALES_ORDER_SHIPMENT_CUSTOMER_ID (`customer_id`),
    INDEX IDX_SALES_ORDER_SHIPMENT_WAREHOUSE_ID (`warehouse_id`),
    INDEX IDX_SALES_ORDER_SHIPMENT_BILLING_ADDRESS_ID (`billing_address_id`),
    INDEX IDX_SALES_ORDER_SHIPMENT_SHIPPING_ADDRESS_ID (`shipping_address_id`),
    CONSTRAINT FK_SALES_ORDER_SHIPMENT_WAREHOUSE_ID_WAREHOUSE_ID FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_SHIPMENT_ORDER_ID_SALES_ORDER_ID FOREIGN KEY (`order_id`) REFERENCES `sales_order`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_SHIPMENT_CUSTOMER_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_SHIPMENT_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_SHIPMENT_BILLING_ADDR_ID_ADDR_ENTITY_ID FOREIGN KEY (`billing_address_id`) REFERENCES `address_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_SHIPMENT_SHIPPING_ADDR_ID_ADDR_ENTITY_ID FOREIGN KEY (`shipping_address_id`) REFERENCES `address_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_SALES_ORDER_SHIPMENT` BEFORE UPDATE ON `sales_order_shipment` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `sales_order_shipment_item` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Item ID',
    `item_id` INTEGER COMMENT 'Order item ID',
    `shipment_id` INTEGER NOT NULL COMMENT 'Shipment ID',
    `product_id` INTEGER COMMENT 'Product ID',
    `product_name` VARCHAR(255) COMMENT 'Product Name',
    `options` VARCHAR(255) NULL COMMENT 'Options',
    `qty` DECIMAL(12,4) NOT NULL COMMENT 'Quentity',
    `sku` VARCHAR(255) NOT NULL COMMENT 'Sku',
    `weight` DECIMAL(12,4) NOT NULL COMMENT 'Weight',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_SHIPMENT_ITEM_ITEM_ID (`item_id`),
    INDEX IDX_SALES_ORDER_SHIPMENT_ITEM_SHIPMENT_ID (`shipment_id`),
    INDEX IDX_SALES_ORDER_SHIPMENT_ITEM_PRODUCT_ID (`product_id`),
    CONSTRAINT FK_SALES_ORDER_SHIPMENT_ITEM_ITEM_ID_SALES_ORDER_ITEM_ID FOREIGN KEY (`item_id`) REFERENCES `sales_order_item`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_SHIPMENT_ITEM_SHIPMENT_ID_SALES_ORDER_SHIPMENT_ID FOREIGN KEY (`shipment_id`) REFERENCES `sales_order_shipment`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_SHIPMENT_ITEM_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_SALES_ORDER_SHIPMENT_ITEM` BEFORE UPDATE ON `sales_order_shipment_item` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `sales_order_shipment_track` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Track ID',
    `shipment_id` INTEGER NOT NULL COMMENT 'Shipment ID',
    `order_id` INTEGER NOT NULL COMMENT 'Order ID',
    `carrier` VARCHAR(255) NOT NULL COMMENT 'Carrier',
    `carrier_code` VARCHAR(32) DEFAULT '' COMMENT 'Carrier code',
    `track_number` VARCHAR(255) NOT NULL COMMENT 'Track number',
    `description` TEXT COMMENT 'Description',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_TRACK_ORDER_ID (`order_id`),
    INDEX IDX_SALES_ORDER_TRACK_SHIPMENT_ID (`shipment_id`),
    CONSTRAINT FK_SALES_ORDER_TRACK_ORDER_ID_SALES_ORDER_ID FOREIGN KEY (`order_id`) REFERENCES `sales_order`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_TRACK_SHIPMENT_ID_SALES_ORDER_SHIPMENT_ID FOREIGN KEY (`shipment_id`) REFERENCES `sales_order_shipment`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `sales_order_creditmemo` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Credit memo ID',
    `order_id` INTEGER NOT NULL COMMENT 'Order ID',
    `increment_id` VARCHAR(255) NOT NULL COMMENT 'Increment ID',
    `warehouse_id` INTEGER DEFAULT NULL COMMENT 'Warehouse ID',
    `store_id` INTEGER DEFAULT NULL COMMENT 'Store ID',
    `base_currency` CHAR(3) NOT NULL COMMENT 'Base currency code',
    `currency` CHAR(3) NOT NULL COMMENT 'Currency code',
    `base_subtotal` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base subtotal',
    `subtotal` DECIMAL(12,4) DEFAULT 0 COMMENT 'Subtotal',
    `base_shipping` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base shipping fee',
    `shipping` DECIMAL(12,4) DEFAULT 0 COMMENT 'Shipping fee',
    `base_discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base discount',
    `discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Discount',
    `base_tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base tax',
    `tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Tax',
    `base_total` DECIMAL(12,4) NOT NULL COMMENT 'Base total',
    `total` DECIMAL(12,4) NOT NULL COMMENT 'Total',
    `comment` TEXT COMMENT 'Comment',
    `status` BOOLEAN DEFAULT 0 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_MEMO_INCREMENT_ID (`increment_id`),
    INDEX IDX_SALES_ORDER_MEMO_STORE_ID (`store_id`),
    INDEX IDX_SALES_ORDER_MEMO_ORDER_ID (`order_id`),
    INDEX IDX_SALES_ORDER_MEMO_WAREHOUSE_ID (`warehouse_id`),
    CONSTRAINT FK_SALES_ORDER_MEMO_WAREHOUSE_ID_WAREHOUSE_ID FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_MEMO_ORDER_ID_SALES_ORDER_ID FOREIGN KEY (`order_id`) REFERENCES `sales_order`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_MEMO_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_SALES_ORDER_CREDITMEMO` BEFORE UPDATE ON `sales_order_creditmemo` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `sales_order_creditmemo_item` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Item ID',
    `item_id` INTEGER COMMENT 'Order item ID',
    `creditmemo_id` INTEGER NOT NULL COMMENT 'Credit memo ID',
    `product_id` INTEGER COMMENT 'Product ID',
    `product_name` VARCHAR(255) COMMENT 'Product Name',
    `options` VARCHAR(255) NULL COMMENT 'Options',
    `qty` DECIMAL(12,4) NOT NULL COMMENT 'Quentity',
    `sku` VARCHAR(255) NOT NULL COMMENT 'Sku',
    `base_price` DECIMAL(12,4) NOT NULL COMMENT 'Base price',
    `price` DECIMAL(12,4) NOT NULL COMMENT 'Price',
    `base_discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base discount',
    `discount` DECIMAL(12,4) DEFAULT 0 COMMENT 'Discount',
    `base_tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Base tax',
    `tax` DECIMAL(12,4) DEFAULT 0 COMMENT 'Tax',
    `base_total` DECIMAL(12,4) NOT NULL COMMENT 'Base total',
    `total` DECIMAL(12,4) NOT NULL COMMENT 'Total',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_MEMO_ITEM_ITEM_ID (`item_id`),
    INDEX IDX_SALES_ORDER_MEMO_ITEM_MEMO_ID (`creditmemo_id`),
    INDEX IDX_SALES_ORDER_MEMO_ITEM_PRODUCT_ID (`product_id`),
    CONSTRAINT FK_SALES_ORDER_MEMO_ITEM_ITEM_ID_SALES_ORDER_ITEM_ID FOREIGN KEY (`item_id`) REFERENCES `sales_order_item`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_MEMO_ITEM_MEMO_ID_SALES_ORDER_MEMO_ID FOREIGN KEY (`creditmemo_id`) REFERENCES `sales_order_creditmemo`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_MEMO_ITEM_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_SALES_ORDER_CREDITMEMO_ITEM` BEFORE UPDATE ON `sales_order_creditmemo_item` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `sales_order_status_history` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'History ID',
    `admin_id` INTEGER COMMENT 'Admin ID',
    `order_id` INTEGER NOT NULL COMMENT 'Order ID',
    `status_id` INTEGER COMMENT 'Status ID',
    `status` VARCHAR(255) COMMENT 'Status',
    `is_customer_notified` BOOLEAN DEFAULT 0 COMMENT 'Is Customer Notified',
    `is_visible_on_front` BOOLEAN  DEFAULT 0 COMMENT 'Is Visible On Front',
    `comment` TEXT COMMENT 'Comment',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    PRIMARY KEY (`id`),
    INDEX IDX_SALES_ORDER_STATUS_HISTORY_ADMIN_ID (`admin_id`),
    INDEX IDX_SALES_ORDER_STATUS_HISTORY_ORDER_ID (`order_id`),
    INDEX IDX_SALES_ORDER_STATUS_HISTORY_STATUS_ID (`status_id`),
    INDEX IDX_SALES_ORDER_STATUS_HISTORY_CREATED_AT (`created_at`),
    CONSTRAINT FK_SALES_ORDER_STATUS_ADMIN_ID_ADMIN_USER_ID FOREIGN KEY (`admin_id`) REFERENCES `admin_user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_STATUS_ORDER_ID_SALES_ORDER_ID FOREIGN KEY (`order_id`) REFERENCES `sales_order`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SALES_ORDER_STATUS_STATUS_ID_SALES_ORDER_STATUS_ID FOREIGN KEY (`status_id`) REFERENCES `sales_order_status`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `wishlist` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Wishlist ID',
    `customer_id` INTEGER NOT NULL COMMENT 'Customer ID',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_WISHLIST_CUSTOMER_ID UNIQUE (`customer_id`),
    CONSTRAINT FK_WISHLIST_CUSTOMER_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `wishlist_item` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Wishlist item ID',
    `wishlist_id` INTEGER NOT NULL COMMENT 'Wishlist ID',
    `product_id` INTEGER COMMENT 'Product ID',
    `product_name` VARCHAR(255) COMMENT 'Product Name',
    `store_id` INTEGER DEFAULT NULL COMMENT 'Store ID',
    `description` TEXT COMMENT 'Description',
    `qty` DECIMAL(12,4) NOT NULL COMMENT 'Quentity',
    `options` VARCHAR(255) NULL COMMENT 'Options',
    `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Added at',
    PRIMARY KEY (`id`),
    INDEX IDX_WISHLIST_ITEM_STORE_ID (`store_id`),
    INDEX IDX_WISHLIST_ITEM_PRODUCT_ID (`product_id`),
    CONSTRAINT FK_WISHLIST_ITEM_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_WISHLIST_ITEM_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `social_media` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Social Media ID',
    `label` VARCHAR(255) NOT NULL COMMENT 'Social Media Name',
    `link` VARCHAR(255) NOT NULL COMMENT 'Social Media Link',
    `icon` VARCHAR(20) NOT NULL COMMENT 'Social Media Icon Name',
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `social_media_share` (
    `media_id` INTEGER NOT NULL COMMENT 'Social Media ID',
    `customer_id` INTEGER NOT NULL COMMENT 'Customer ID',
    `product_id` INTEGER NOT NULL COMMENT 'Product ID',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    PRIMARY KEY (`media_id`,`customer_id`,`product_id`),
    INDEX IDX_SOCIAL_MEDIA_SHARE_CUSTOMER_ID (`customer_id`),
    INDEX IDX_SOCIAL_MEDIA_SHARE_PRODUCT_ID (`product_id`),
    CONSTRAINT FK_SOCIAL_MEDIA_SHARE_MEDIA_ID_SOCIAL_MEDIA_ID FOREIGN KEY (`media_id`) REFERENCES `social_media` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SOCIAL_MEDIA_SHARE_CUSTOMER_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_SOCIAL_MEDIA_SHARE_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `log_view` (
    `customer_id` INTEGER NOT NULL COMMENT 'Customer ID',
    `product_id` INTEGER NOT NULL COMMENT 'Product ID',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    `updated_at` DATETIME NULL DEFAULT NULL COMMENT 'Updated at',
    PRIMARY KEY (`customer_id`,`product_id`),
    INDEX IDX_LOG_VIEW_CUSTOMER_ID (`customer_id`),
    INDEX IDX_LOG_VIEW_PRODUCT_ID (`product_id`),
    CONSTRAINT FK_LOG_VIEW_CUSTOMER_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_LOG_VIEW_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_LOG_VIEW` BEFORE UPDATE ON `log_view` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `retailer`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Retailer ID',
    `customer_id` INTEGER NOT NULL COMMENT 'Customer ID',
    `store_id` INTEGER NULL DEFAULT NULL COMMENT 'Store ID',
    `name` VARCHAR(50) NOT NULL COMMENT 'Retailer Name',
    `address` VARCHAR(255) NOT NULL COMMENT 'Retailer Address',
    `account` VARCHAR(50) NOT NULL COMMENT 'Retailer Account',
    `photo` INTEGER NOT NULL COMMENT 'Retailer Photo',
    `credentials` INTEGER NOT NULL COMMENT 'Credentials',
    `status` BOOLEAN DEFAULT 0 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created at',
    PRIMARY KEY (`id`),
    CONSTRAINT CHK_RETAILER_STORE_ID CHECK ((`store_id` IS NULL AND `status`=0) OR (`store_id` IS NOT NULL AND `status`=1)),
    CONSTRAINT UNQ_RETAILER_CUSTOMER_ID UNIQUE (`customer_id`),
    CONSTRAINT UNQ_RETAILER_STORE_ID UNIQUE (`store_id`),
    CONSTRAINT FK_RETAILER_CUSTOMER_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_RETAILER_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_RETAILER_PHOTO_RESOURCE_ID FOREIGN KEY (`photo`) REFERENCES `resource` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_RETAILER_CREDENTIALS_RESOURCE_ID FOREIGN KEY (`credentials`) REFERENCES `resource` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

SET FOREIGN_KEY_CHECKS=1;