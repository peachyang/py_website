SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `core_merchant`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Merchant ID',
    `code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Merchant code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `is_default` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is default',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_CORE_MERCHANT_CODE UNIQUE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_CORE_MERCHANT` BEFORE UPDATE ON `core_merchant` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_merchant` VALUES (null,'default',1,1,null,null);

CREATE TABLE IF NOT EXISTS `core_store`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Store ID',
    `merchant_id` INTEGER UNSIGNED NOT NULL COMMENT 'Merchant ID',
    `code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Store code',
    `name` VARCHAR(30) NOT NULL DEFAULT '' COMMENT 'Store name',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `is_default` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is default',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_STORE_MERCHANT_ID_STATUS (`merchant_id`,`status`),
    CONSTRAINT UNQ_CORE_STORE_CODE UNIQUE (`code`),
    CONSTRAINT FK_CORE_STORE_MERCHANT_ID_CORE_MERCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CORE_STORE` BEFORE UPDATE ON `core_store` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_store` VALUES (null,1,'default','Default',1,1,null,null);

CREATE TABLE IF NOT EXISTS `core_language`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Language ID',
    `merchant_id` INTEGER UNSIGNED NOT NULL COMMENT 'Merchant ID',
    `code` VARCHAR(10) NOT NULL DEFAULT '' COMMENT 'ISO 639-1 language code',
    `name` VARCHAR(30) NOT NULL DEFAULT '' COMMENT 'Language name',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `is_default` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is default',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_LANGUAGE_MERCHANT_ID_STATUS (`merchant_id`,`status`),
    CONSTRAINT UNQ_CORE_LANGUAGE_MERCHANT_ID_CODE UNIQUE (`merchant_id`,`code`),
    CONSTRAINT FK_CORE_LANGUAGE_MERCHANT_ID_CORE_MERCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CORE_LANGUAGE` BEFORE UPDATE ON `core_language` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_language` VALUES (null,1,'en_US','English',1,1,null,null);

CREATE TABLE IF NOT EXISTS `cms_page`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Page ID',
    `parent_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Parent page ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `uri_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'URI Key',
    `title` VARCHAR(255) DEFAULT '' COMMENT 'Page title',
    `keywords` VARCHAR(255) DEFAULT '' COMMENT 'Meta keywords',
    `description` VARCHAR(255) DEFAULT '' COMMENT 'Meta description',
    `thumbnail` VARCHAR(255) DEFAULT '' COMMENT 'Thumbnail',
    `image` VARCHAR(255) DEFAULT '' COMMENT 'Image',
    `content` BLOB COMMENT 'Page content',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    INDEX IDX_CMS_PAGE_PARENT_ID (`parent_id`),
    INDEX IDX_CMS_PAGE_URI_KEY (`uri_key`),
    CONSTRAINT FK_CMS_PAGE_PARENT_ID_CMS_PAGE_ID FOREIGN KEY (`parent_id`) REFERENCES `cms_page`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CMS_PAGE` BEFORE UPDATE ON `cms_page` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `cms_page_language`(
    `page_id` INTEGER UNSIGNED NOT NULL COMMENT 'Page ID',
    `store_id` INTEGER UNSIGNED NOT NULL COMMENT 'Store ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    PRIMARY KEY (`page_id`,`store_id`,`language_id`),
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`page_id`) REFERENCES `cms_page`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_block`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Block ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Identify code',
    `content` BLOB COMMENT 'Page content',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    CONSTRAINT IDX_CMS_BLOCK_CODE UNIQUE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_CMS_BLOCK` BEFORE UPDATE ON `cms_block` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `cms_block_language`(
    `block_id` INTEGER UNSIGNED NOT NULL COMMENT 'Block ID',
    `store_id` INTEGER UNSIGNED NOT NULL COMMENT 'Store ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    PRIMARY KEY (`block_id`,`store_id`,`language_id`),
    CONSTRAINT FK_CMS_BLOCK_LANGUAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`block_id`) REFERENCES `cms_block`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_BLOCK_LANGUAGE_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_BLOCK_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `admin_operation` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Operation ID',
    `name` VARCHAR(255) NOT NULL COMMENT 'Operation name',
    `description` TEXT COMMENT 'Description',
    `is_system` BOOLEAN DEFAULT '0' COMMENT 'Is generated by system',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_ADMIN_ROLE_NAME UNIQUE (`name`)
);

CREATE TRIGGER `TGR_UPDATE_ADMIN_OPERATION` BEFORE UPDATE ON `admin_operation` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `admin_operation` VALUES(-1,'ALL','',0,null,null);

CREATE TABLE IF NOT EXISTS `admin_role` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Role ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `name` VARCHAR(255) NOT NULL COMMENT 'Role name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_ADMIN_ROLE_NAME UNIQUE (`name`)
);

CREATE TRIGGER `TGR_UPDATE_ADMIN_ROLE` BEFORE UPDATE ON `admin_role` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `admin_role` VALUES (null,1,'Administrator',null,null);

CREATE TABLE IF NOT EXISTS `admin_role_recursive`(
    `role_id` INTEGER UNSIGNED NOT NULL COMMENT 'Role ID',
    `child_id` INTEGER UNSIGNED NOT NULL COMMENT 'Child ID',
    PRIMARY KEY (`role_id`,`child_id`),
    CONSTRAINT FK_ADMIN_ROLE_ROLE_ID_ADMIN_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADMIN_ROLE_CHILD_ID_ADMIN_ROLE_ID FOREIGN KEY (`child_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `admin_permission` (
    `role_id` INTEGER UNSIGNED NOT NULL COMMENT 'Role ID',
    `operation_id` INTEGER NOT NULL COMMENT 'Operation ID',
    `permission` BOOLEAN NOT NULL DEFAULT '1' COMMENT 'Permission',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    PRIMARY KEY (`role_id`,`operation_id`),
    CONSTRAINT FK_ADMIN_PERMISSION_ROLE_ID_ADMIN_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADMIN_PERMISSION_OPERATION_ID_ADMIN_OPERATION_ID FOREIGN KEY (`operation_id`) REFERENCES `admin_operation`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `admin_permission` VALUES (1,-1,1,null);

CREATE TABLE IF NOT EXISTS `admin_user` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Admin ID',
    `role_id` INTEGER UNSIGNED NOT NULL COMMENT 'Role ID',
    `store_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Store ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `username` VARCHAR(255) NOT NULL COMMENT 'Username',
    `password` CHAR(60) NOT NULL COMMENT 'Password',
    `email` VARCHAR(255) NOT NULL COMMENT 'E-Mail',
    `logdate` TIMESTAMP NULL DEFAULT NULL COMMENT 'Last login time',
    `lognum` INTEGER UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Login number',
    `rp_token` CHAR(32) DEFAULT NULL COMMENT 'Reset password link token',
    `rp_token_created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Reset password link token creation date',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    INDEX IDX_ADMIN_USER_ROLE_ID (`role_id`),
    CONSTRAINT UNQ_ADMIN_USER_USERNAME UNIQUE (`username`),
    CONSTRAINT UNQ_ADMIN_USER_RP_TOKEN UNIQUE (`rp_token`),
    CONSTRAINT FK_ADMIN_USER_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_ADMIN_USER_ROLE_ID_ADMIN_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_ADMIN_USER` BEFORE UPDATE ON `admin_user` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `admin_user` VALUES (null,1,null,1,'admin','$2y$10$5.GIrJ/AdDHso6cx6n6/MedTlhjnPRWGMOOEJT0Cf0qoB/noLnLRS','',null,0,null,null,null,null);

CREATE TABLE IF NOT EXISTS `core_config` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Config ID',
    `merchant_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Merchant ID',
    `store_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Store ID',
    `language_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Language ID',
    `path` VARCHAR(255) NOT NULL COMMENT 'Config path',
    `value` VARCHAR(255) COMMENT 'Config value',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_CONFIG_LANGUAGE_ID_PATH (`language_id`,`path`),
    INDEX IDX_CORE_CONFIG_STORE_ID_LANGUAGE_ID_PATH (`store_id`,`language_id`,`path`),
    INDEX IDX_CORE_CONFIG_MERCHANT_ID_LANGUAGE_ID_PATH (`merchant_id`,`language_id`,`path`),
    CONSTRAINT UNQ_CORE_CONFIG_MERCHANT_ID_STORE_ID_LANGUAGE_ID_PATH UNIQUE (`merchant_id`,`store_id`,`language_id`,`path`),
    CONSTRAINT FK_CORE_CONFIG_MERCHANT_ID_CORE_MARCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CORE_CONFIG_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CORE_CONFIG_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CORE_CONFIG` BEFORE UPDATE ON `core_config` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_config` VALUES (null,1,null,null,'global/base_url','/',null,null),(null,1,NULL,NULL,'global/admin_path','admin',null,null);

CREATE TABLE IF NOT EXISTS `core_session` (
    `id` CHAR(32) NOT NULL COMMENT 'Session ID',
    `data` TEXT COMMENT 'Session data',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Session created',
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `email_template` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Template ID',
    `code` VARCHAR(255) NOT NULL COMMENT 'Template code',
    `subject` VARCHAR(255) DEFAULT '' COMMENT 'Subject',
    `content` BLOB COMMENT 'Content',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    INDEX IDX_EMAIL_TEMPLATE_CODE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_EMAIL_TEMPLATE` BEFORE UPDATE ON `email_template` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `email_template_language`(
    `template_id` INTEGER UNSIGNED NOT NULL COMMENT 'Template ID',
    `store_id` INTEGER UNSIGNED NOT NULL COMMENT 'Store ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    CONSTRAINT FK_EMAIL_TAMPLATE_LANGUAGE_TEMPLATE_ID_EMAIL_TAMPLATE_ID FOREIGN KEY (`template_id`) REFERENCES `email_template`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EMAIL_TAMPLATE_LANGUAGE_LANGUAGE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EMAIL_TAMPLATE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `core_schedule`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Schedule ID',
    `code` VARCHAR(255) NOT NULL COMMENT 'Run code',
    `status` CHAR(1) NOT NULL DEFAULT '0' COMMENT 'Is job finished',
    `messages` TEXT COMMENT 'Exception',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `scheduled_at` TIMESTAMP DEFAULT NULL COMMENT 'Scheduled time',
    `executed_at` TIMESTAMP DEFAULT NULL COMMENT 'Executed time',
    `finished_at` TIMESTAMP DEFAULT NULL COMMENT 'Finishd time',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_SCHEDULE_STATUS (`status`),
    INDEX IDX_CORE_SCHEDULE_SCHEDULED_AT (`scheduled_at`)
);

SET FOREIGN_KEY_CHECKS = 1;