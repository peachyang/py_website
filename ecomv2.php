SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `core_merchant`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Merchant ID',
    `code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Merchant code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `is_default` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is default',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_CORE_MERCHANT_CODE UNIQUE (`code`)
);

CREATE TABLE IF NOT EXISTS `core_store`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Store ID',
    `merchant_id` INTEGER UNSIGNED NOT NULL COMMENT 'Merchant ID',
    `code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Store code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `is_default` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is default',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_STORE_MERCHANT_ID_STATUS (`merchant_id`,`status`),
    CONSTRAINT UNQ_CORE_STORE_CODE UNIQUE (`code`),
    CONSTRAINT FK_CORE_STORE_MERCHANT_ID_CORE_MERCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `core_language`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Language ID',
    `store_id` INTEGER UNSIGNED NOT NULL COMMENT 'Store ID',
    `code` VARCHAR(10) NOT NULL DEFAULT '' COMMENT 'ISO 639-1 language code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `is_default` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is default',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_LANGUAGE_STORE_ID_STATUS (`store_id`,`status`),
    CONSTRAINT UNQ_CORE_LANGUAGE_STORE_ID_CODE UNIQUE (`store_id`,`code`),
    CONSTRAINT FK_CORE_LANGUAGE_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_page`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Page ID',
    `parent_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Parent page ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `uri_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'URI Key',
    `title` VARCHAR(255) DEFAULT '' COMMENT 'Page title',
    `keywords` VARCHAR(255) DEFAULT '' COMMENT 'Meta keywords',
    `description` VARCHAR(255) DEFAULT '' COMMENT 'Meta description',
    `content` BLOB COMMENT 'Page content',
    PRIMARY KEY (`id`),
    INDEX IDX_CMS_PAGE_PARENT_ID (`parent_id`),
    INDEX IDX_CMS_PAGE_URI_KEY (`uri_key`),
    CONSTRAINT FK_CMS_PAGE_PARENT_ID_CMS_PAGE_ID FOREIGN KEY (`parent_id`) REFERENCES `cms_page`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_page_language`(
    `page_id` INTEGER UNSIGNED NOT NULL COMMENT 'Page ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`page_id`) REFERENCES `cms_page`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_block`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Block ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Identify code',
    `content` BLOB COMMENT 'Page content',
    PRIMARY KEY (`id`),
    CONSTRAINT IDX_CMS_BLOCK_CODE UNIQUE (`code`)
);

CREATE TABLE IF NOT EXISTS `cms_block_language`(
    `block_id` INTEGER UNSIGNED NOT NULL COMMENT 'Block ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    CONSTRAINT FK_CMS_BLOCK_LANGUAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`block_id`) REFERENCES `cms_block`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_BLOCK_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `admin_role` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Role ID',
    `parent_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Parent role ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `name` VARCHAR(255) NOT NULL COMMENT 'Role name',
    PRIMARY KEY (`id`),
    INDEX IDX_ADMIN_ROLE_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_ADMIN_ROLE_NAME UNIQUE (`name`),
    CONSTRAINT FK_ADMIN_ROLE_PARENT_ID_ADMIN_ROLE_ID FOREIGN KEY (`parent_id`) REFERENCES `admin_role`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `admin_operation` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Operation ID',
    `name` VARCHAR(255) NOT NULL COMMENT 'Operation name',
    `description` TEXT COMMENT 'Description',
    `is_system` BOOLEAN DEFAULT '0' COMMENT 'Is generated by system',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_ADMIN_ROLE_NAME UNIQUE (`name`)
);

CREATE TABLE IF NOT EXISTS `admin_permission` (
    `role_id` INTEGER UNSIGNED NOT NULL COMMENT 'Role ID',
    `operation_id` INTEGER NOT NULL COMMENT 'Operation ID',
    `permission` BOOLEAN NOT NULL DEFAULT '1' COMMENT 'Permission',
    PRIMARY KEY (`role_id`,`operation_id`),
    CONSTRAINT FK_ADMIN_PERMISSION_ROLE_ID_ADMIN_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADMIN_PERMISSION_OPERATION_ID_ADMIN_OPERATION_ID FOREIGN KEY (`operation_id`) REFERENCES `admin_operation`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `admin_user` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Admin ID',
    `role_id` INTEGER UNSIGNED NOT NULL COMMENT 'Role ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `username` VARCHAR(255) NOT NULL COMMENT 'Username',
    `password` CHAR(60) NOT NULL COMMENT 'Password',
    `email` VARCHAR(255) NOT NULL COMMENT 'E-Mail',
    `logdate` TIMESTAMP NULL DEFAULT NULL COMMENT 'Last login time',
    `lognum` INTEGER UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Login number',
    `rp_token` CHAR(32) DEFAULT NULL COMMENT 'Reset password link token',
    `rp_token_created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Reset password link token creation date',
    PRIMARY KEY (`id`),
    INDEX IDX_ADMIN_USER_ROLE_ID (`role_id`),
    CONSTRAINT UNQ_ADMIN_USER_USERNAME UNIQUE (`username`),
    CONSTRAINT UNQ_ADMIN_USER_RP_TOKEN UNIQUE (`rp_token`),
    CONSTRAINT FK_ADMIN_USER_ROLE_ID_ADMIN_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `core_config` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Config ID',
    `merchant_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Merchant ID',
    `store_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Store ID',
    `language_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Language ID',
    `path` VARCHAR(255) NOT NULL COMMENT 'Config path',
    `value` VARCHAR(255) COMMENT 'Config value',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_CONFIG_LANGUAGE_ID_PATH (`language_id`,`path`),
    INDEX IDX_CORE_CONFIG_STORE_ID_LANGUAGE_ID_PATH (`store_id`,`language_id`,`path`),
    CONSTRAINT UNQ_CORE_CONFIG_MERCHANT_ID_STORE_ID_LANGUAGE_ID_PATH UNIQUE (`merchant_id`,`store_id`,`language_id`,`path`),
    CONSTRAINT FK_CORE_CONFIG_MERCHANT_ID_CORE_MARCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CORE_CONFIG_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CORE_CONFIG_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

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
    PRIMARY KEY (`id`),
    INDEX IDX_EMAIL_TEMPLATE_CODE (`code`)
);

CREATE TABLE IF NOT EXISTS `email_template_language`(
    `template_id` INTEGER UNSIGNED NOT NULL COMMENT 'Template ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    CONSTRAINT FK_EMAIL_TAMPLATE_LANGUAGE_TEMPLATE_ID_EMAIL_TAMPLATE_ID FOREIGN KEY (`template_id`) REFERENCES `email_template`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EMAIL_TAMPLATE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;