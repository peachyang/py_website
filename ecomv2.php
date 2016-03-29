SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `core_merchant`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Merchant ID',
    `code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Merchant code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_CORE_MERCHANT_CODE UNIQUE (`code`)
);

CREATE TABLE IF NOT EXISTS `core_store`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Store ID',
    `merchant_id` INTEGER NOT NULL COMMENT 'Merchant ID',
    `code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Store code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_STORE_MERCHANT_ID_STATUS (`merchant_id`,`status`),
    CONSTRAINT UNQ_CORE_STORE_CODE UNIQUE (`code`),
    CONSTRAINT FK_CORE_STORE_MERCHANT_ID_CORE_MERCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `core_language`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Language ID',
    `store_id` INTEGER NOT NULL COMMENT 'Store ID',
    `code` VARCHAR(10) NOT NULL DEFAULT '' COMMENT 'ISO 639-1 language code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_LANGUAGE_STORE_ID_STATUS (`store_id`,`status`),
    CONSTRAINT UNQ_CORE_LANGUAGE_STORE_ID_CODE UNIQUE (`store_id`,`code`),
    CONSTRAINT FK_CORE_LANGUAGE_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_page`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Page ID',
    `parent_id` INTEGER COMMENT 'Parent page ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `uri_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'URI Key',
    `title` VARCHAR(255) DEFAULT '' COMMENT 'Page title',
    `keywords` VARCHAR(255) DEFAULT '' COMMENT 'Meta keywords',
    `description` VARCHAR(255) DEFAULT '' COMMENT 'Meta description',
    `content` BLOB COMMENT 'Page content',
    PRIMARY KEY (`id`),
    INDEX IDX_CMS_PAGE_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_CMS_PAGE_URI_KEY UNIQUE (`uri_key`),
    CONSTRAINT FK_CMS_PAGE_PARENT_ID_CMS_PAGE_ID FOREIGN KEY (`parent_id`) REFERENCES `cms_page`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_page_language`(
    `page_id` INTEGER NOT NULL COMMENT 'Page ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`page_id`) REFERENCES `cms_page`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_block`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Block ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Identify code',
    `content` BLOB COMMENT 'Page content',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_CMS_BLOCK_CODE UNIQUE (`code`)
);

CREATE TABLE IF NOT EXISTS `cms_block_language`(
    `block_id` INTEGER NOT NULL COMMENT 'Block ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    CONSTRAINT FK_CMS_BLOCK_LANGUAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`block_id`) REFERENCES `cms_block`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_BLOCK_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;