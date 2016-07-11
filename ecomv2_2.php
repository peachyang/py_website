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

SET FOREIGN_KEY_CHECKS=1;