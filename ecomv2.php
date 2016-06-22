SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `core_merchant`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Merchant ID',
    `code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Merchant code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `is_default` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is default',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_CORE_MERCHANT_CODE UNIQUE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_CORE_MERCHANT` BEFORE UPDATE ON `core_merchant` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_merchant` VALUES (null,'default',1,1,null,null);

CREATE TABLE IF NOT EXISTS `core_store`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Store ID',
    `merchant_id` INTEGER NOT NULL COMMENT 'Merchant ID',
    `code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Store code',
    `name` VARCHAR(30) NOT NULL DEFAULT '' COMMENT 'Store name',
    `is_default` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is default',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_STORE_MERCHANT_ID (`merchant_id`),
    CONSTRAINT UNQ_CORE_STORE_CODE UNIQUE (`code`),
    CONSTRAINT FK_CORE_STORE_MERCHANT_ID_CORE_MERCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CORE_STORE` BEFORE UPDATE ON `core_store` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_store` VALUES (null,1,'default','Default',1,1,null,null);

CREATE TABLE IF NOT EXISTS `core_language`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Language ID',
    `merchant_id` INTEGER NOT NULL COMMENT 'Merchant ID',
    `code` VARCHAR(10) NOT NULL DEFAULT '' COMMENT 'RFC 5646 language code',
    `name` VARCHAR(30) NOT NULL DEFAULT '' COMMENT 'Language name',
    `is_default` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is default',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_LANGUAGE_MERCHANT_ID (`merchant_id`),
    CONSTRAINT UNQ_CORE_LANGUAGE_MERCHANT_ID_CODE UNIQUE (`merchant_id`,`code`),
    CONSTRAINT FK_CORE_LANGUAGE_MERCHANT_ID_CORE_MERCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CORE_LANGUAGE` BEFORE UPDATE ON `core_language` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_language` VALUES (null,1,'en-US','English',1,1,null,null);

CREATE TABLE IF NOT EXISTS `cms_page`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Page ID',
    `store_id` INTEGER NULL DEFAULT NULL COMMENT 'Store ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `uri_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'URI Key',
    `title` VARCHAR(255) DEFAULT '' COMMENT 'Page title',
    `keywords` VARCHAR(255) DEFAULT '' COMMENT 'Meta keywords',
    `description` VARCHAR(255) DEFAULT '' COMMENT 'Meta description',
    `thumbnail` VARCHAR(255) DEFAULT '' COMMENT 'Thumbnail',
    `image` VARCHAR(255) DEFAULT '' COMMENT 'Image',
    `content` BLOB COMMENT 'Page content',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CMS_PAGE_URI_KEY (`uri_key`),
    INDEX IDX_CMS_PAGE_STORE_ID (`store_id`),
    CONSTRAINT FK_CMS_PAGE_STORE_ID_CMS_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CMS_PAGE` BEFORE UPDATE ON `cms_page` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `cms_page_language`(
    `page_id` INTEGER NOT NULL COMMENT 'Page ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    PRIMARY KEY (`page_id`,`language_id`),
    INDEX IDX_CMS_PAGE_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`page_id`) REFERENCES `cms_page`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_category`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Category ID',
    `uri_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'URI Key',
    `parent_id` INTEGER NULL DEFAULT NULL COMMENT 'Parent Category ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CMS_CATEGORY_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_CMS_CATEGORY_URI_KEY UNIQUE (`uri_key`),
    CONSTRAINT FK_CMS_CATEGORY_PARENT_ID_CMS_CATEGORY_ID FOREIGN KEY (`parent_id`) REFERENCES `cms_category`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CMS_CATEGORY` BEFORE UPDATE ON `cms_category` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `cms_category_language`(
    `category_id` INTEGER NOT NULL COMMENT 'Category ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Category name',
    PRIMARY KEY (`category_id`,`language_id`),
    INDEX IDX_CMS_CATEGORY_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_CMS_CATEGORY_LANGUAGE_CATEGORY_ID_CMS_CATEGORY_ID FOREIGN KEY (`category_id`) REFERENCES `cms_category`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_CATEGORY_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_category_page`(
    `category_id` INTEGER NOT NULL COMMENT 'Category ID',
    `page_id` INTEGER NOT NULL COMMENT 'Page ID',
    PRIMARY KEY (`category_id`,`page_id`),
    INDEX IDX_CMS_CATEGORY_PAGE_PAGE_ID (`page_id`),
    CONSTRAINT FK_CMS_CATEGORY_PAGE_CATEGORY_ID_CMS_CATEGORY_ID FOREIGN KEY (`category_id`) REFERENCES `cms_category`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_CATEGORY_PAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`page_id`) REFERENCES `cms_page`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_block`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Block ID',
    `store_id` INTEGER NULL DEFAULT NULL COMMENT 'Store ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Identify code',
    `content` BLOB COMMENT 'Page content',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CMS_BLOCK_STORE_ID (`store_id`),
    CONSTRAINT IDX_CMS_BLOCK_CODE UNIQUE (`code`),
    CONSTRAINT FK_CMS_BLOCK_STORE_ID_CMS_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CMS_BLOCK` BEFORE UPDATE ON `cms_block` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `cms_block_language`(
    `block_id` INTEGER NOT NULL COMMENT 'Block ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    PRIMARY KEY (`block_id`,`language_id`),
    INDEX IDX_CMS_BLOCK_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_CMS_BLOCK_LANGUAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`block_id`) REFERENCES `cms_block`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_BLOCK_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `admin_operation` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Operation ID',
    `name` VARCHAR(255) NOT NULL COMMENT 'Operation name',
    `description` TEXT COMMENT 'Description',
    `is_system` BOOLEAN DEFAULT '0' COMMENT 'Is generated by system',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_ADMIN_ROLE_NAME UNIQUE (`name`)
);

CREATE TRIGGER `TGR_UPDATE_ADMIN_OPERATION` BEFORE UPDATE ON `admin_operation` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `admin_operation` VALUES(-1,'ALL','',0,null,null);

CREATE TABLE IF NOT EXISTS `admin_role` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Role ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `name` VARCHAR(255) NOT NULL COMMENT 'Role name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_ADMIN_ROLE_NAME UNIQUE (`name`)
);

CREATE TRIGGER `TGR_UPDATE_ADMIN_ROLE` BEFORE UPDATE ON `admin_role` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `admin_role` VALUES (null,1,'Administrator',null,null);

CREATE TABLE IF NOT EXISTS `admin_role_recursive`(
    `role_id` INTEGER NOT NULL COMMENT 'Role ID',
    `child_id` INTEGER NOT NULL COMMENT 'Child ID',
    PRIMARY KEY (`role_id`,`child_id`),
    INDEX IDX_ADMIN_ROLE_RECURSIVE_CHILD_ID (`child_id`),
    CONSTRAINT FK_ADMIN_ROLE_ROLE_ID_ADMIN_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADMIN_ROLE_CHILD_ID_ADMIN_ROLE_ID FOREIGN KEY (`child_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `admin_permission` (
    `role_id` INTEGER NOT NULL COMMENT 'Role ID',
    `operation_id` INTEGER NOT NULL COMMENT 'Operation ID',
    `permission` BOOLEAN NOT NULL DEFAULT '1' COMMENT 'Permission',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    PRIMARY KEY (`role_id`,`operation_id`),
    INDEX IDX_ADMIN_PERMISSION_OPERATION_ID (`operation_id`),
    CONSTRAINT FK_ADMIN_PERMISSION_ROLE_ID_ADMIN_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADMIN_PERMISSION_OPERATION_ID_ADMIN_OPERATION_ID FOREIGN KEY (`operation_id`) REFERENCES `admin_operation`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `admin_permission` VALUES (1,-1,1,null);

CREATE TABLE IF NOT EXISTS `admin_user` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Admin ID',
    `role_id` INTEGER NOT NULL COMMENT 'Role ID',
    `store_id` INTEGER NULL DEFAULT NULL COMMENT 'Store ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `username` VARCHAR(255) NOT NULL COMMENT 'Username',
    `password` CHAR(60) NOT NULL COMMENT 'Password',
    `email` VARCHAR(255) NOT NULL COMMENT 'E-Mail',
    `logdate` TIMESTAMP NULL DEFAULT NULL COMMENT 'Last login time',
    `lognum` INTEGER NOT NULL DEFAULT '0' COMMENT 'Login number',
    `rp_token` CHAR(32) NULL DEFAULT NULL COMMENT 'Reset password link token',
    `rp_token_created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Reset password link token creation date',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_ADMIN_USER_STORE_ID (`store_id`),
    INDEX IDX_ADMIN_USER_ROLE_ID (`role_id`),
    CONSTRAINT UNQ_ADMIN_USER_USERNAME UNIQUE (`username`),
    CONSTRAINT UNQ_ADMIN_USER_RP_TOKEN UNIQUE (`rp_token`),
    CONSTRAINT FK_ADMIN_USER_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_ADMIN_USER_ROLE_ID_ADMIN_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_ADMIN_USER` BEFORE UPDATE ON `admin_user` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `admin_user` VALUES (null,1,null,1,'admin','$2y$10$5.GIrJ/AdDHso6cx6n6/MedTlhjnPRWGMOOEJT0Cf0qoB/noLnLRS','',null,0,null,null,null,null);

CREATE TABLE IF NOT EXISTS `core_config` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Config ID',
    `merchant_id` INTEGER NULL DEFAULT NULL COMMENT 'Merchant ID',
    `store_id` INTEGER NULL DEFAULT NULL COMMENT 'Store ID',
    `path` VARCHAR(255) NOT NULL COMMENT 'Config path',
    `value` VARCHAR(255) COMMENT 'Config value',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_CONFIG_STORE_ID (`store_id`),
    INDEX IDX_CORE_CONFIG_MERCHANT_ID (`merchant_id`),
    CONSTRAINT CHK_CORE_CONFIG_MERCHANT_ID_STORE_ID CHECK ((`merchant_id` IS NOT NULL AND `store_id` IS NULL) OR (`store_id` IS NOT NULL)),
    CONSTRAINT UNQ_CORE_CONFIG_MERCHANT_ID_STORE_ID_PATH UNIQUE (`merchant_id`,`store_id`,`path`),
    CONSTRAINT FK_CORE_CONFIG_MERCHANT_ID_CORE_MARCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CORE_CONFIG_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CORE_CONFIG` BEFORE UPDATE ON `core_config` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_config` VALUES (null,1,null,'global/url/base_url','/',null,null),(null,1,NULL,'global/url/admin_path','admin',null,null);

CREATE TABLE IF NOT EXISTS `core_session` (
    `id` CHAR(32) NOT NULL COMMENT 'Session ID',
    `data` TEXT COMMENT 'Session data',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Session created',
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `email_template` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Template ID',
    `code` VARCHAR(255) NOT NULL COMMENT 'Template code',
    `subject` VARCHAR(255) DEFAULT '' COMMENT 'Subject',
    `content` BLOB COMMENT 'Content',
    `css` BLOB COMMENT 'CSS',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_EMAIL_TEMPLATE_CODE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_EMAIL_TEMPLATE` BEFORE UPDATE ON `email_template` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `email_template_language`(
    `template_id` INTEGER NOT NULL COMMENT 'Template ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    PRIMARY KEY (`template_id`,`language_id`),
    INDEX IDX_EMAIL_TEMPLATE_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_EMAIL_TAMPLATE_LANGUAGE_TEMPLATE_ID_EMAIL_TAMPLATE_ID FOREIGN KEY (`template_id`) REFERENCES `email_template`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EMAIL_TAMPLATE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `message_template` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Template ID',
    `code` VARCHAR(255) NOT NULL COMMENT 'Template code',
    `content` BLOB COMMENT 'Content',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_MESSAGE_TEMPLATE_CODE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_MESSAGE_TEMPLATE` BEFORE UPDATE ON `message_template` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `message_template_language`(
    `template_id` INTEGER NOT NULL COMMENT 'Template ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    PRIMARY KEY (`template_id`,`language_id`),
    INDEX IDX_MESSAGE_TEMPLATE_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_MESSAGE_TAMPLATE_LANGUAGE_TEMPLATE_ID_MESSAGE_TAMPLATE_ID FOREIGN KEY (`template_id`) REFERENCES `message_template`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_MESSAGE_TAMPLATE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `core_schedule`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Schedule ID',
    `code` VARCHAR(255) NOT NULL COMMENT 'Run code',
    `status` CHAR(1) NOT NULL DEFAULT '0' COMMENT 'Is job finished',
    `messages` TEXT COMMENT 'Exception',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `scheduled_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Scheduled time',
    `executed_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Executed time',
    `finished_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Finishd time',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_SCHEDULE_STATUS (`status`),
    INDEX IDX_CORE_SCHEDULE_SCHEDULED_AT (`scheduled_at`)
);

CREATE TABLE IF NOT EXISTS `i18n_translation`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Translation ID',
    `string` VARCHAR(255) NOT NULL COMMENT 'Translation string',
    `translate` VARCHAR(255) NOT NULL COMMENT 'Translate',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'Locale',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_I18N_TRANSLATION_LOCALE_STATUS (`locale`,`status`),
    INDEX IDX_I18N_TRANSLATION_STRING_LOCALE_STATUS (`string`,`locale`,`status`)
);

CREATE TRIGGER `TGR_UPDATE_I18N_TRANSLATION` BEFORE UPDATE ON `i18n_translation` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `i18n_country`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Country ID',
    `iso2_code` CHAR(2) NOT NULL COMMENT 'Country iso2 code',
    `iso3_code` CHAR(3) NOT NULL COMMENT 'Country iso3 code',
    `default_name` VARCHAR(50) NOT NULL COMMENT 'Country name',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_I18N_COUNTRY_ISO2_CODE UNIQUE (`iso2_code`),
    CONSTRAINT UNQ_I18N_COUNTRY_ISO3_CODE UNIQUE (`iso3_code`)
);

CREATE TABLE IF NOT EXISTS `i18n_country_name`(
    `country_id` INTEGER NOT NULL COMMENT 'Country ID',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'Locale',
    `name` VARCHAR(255) NOT NULL COMMENT 'Region name',
    PRIMARY KEY (`country_id`,`locale`),
    CONSTRAINT FK_I18N_COUNTRY_NAME_COUNTRY_ID FOREIGN KEY (`country_id`) REFERENCES `i18n_country`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_region`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Region ID',
    `parent_id` INTEGER NOT NULL COMMENT 'Country ID',
    `code` VARCHAR(10) NOT NULL COMMENT 'Region code',
    `default_name` VARCHAR(255) NOT NULL COMMENT 'Region default name',
    PRIMARY KEY (`id`),
    INDEX IDX_I18N_REGION_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_I18N_REGION_PARENT_ID_CODE UNIQUE (`parent_id`,`code`),
    CONSTRAINT FK_I18N_REGION_PARENT_ID FOREIGN KEY (`parent_id`) REFERENCES `i18n_country`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_region_name`(
    `region_id` INTEGER NOT NULL COMMENT 'Region ID',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'Locale',
    `name` VARCHAR(255) NOT NULL COMMENT 'Region name',
    PRIMARY KEY (`region_id`,`locale`),
    INDEX IDX_I18N_REGION_NAME_REGION_ID (`region_id`),
    CONSTRAINT FK_I18N_REGION_NAME_REGION_ID FOREIGN KEY (`region_id`) REFERENCES `i18n_region`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_city`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'City ID',
    `parent_id` INTEGER NOT NULL COMMENT 'Region ID',
    `code` VARCHAR(10) NOT NULL COMMENT 'City code',
    `default_name` VARCHAR(255) NOT NULL COMMENT 'City default name',
    PRIMARY KEY (`id`),
    INDEX IDX_I18N_CITY_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_I18N_CITY_PARENT_ID_CODE UNIQUE (`parent_id`,`code`),
    CONSTRAINT FK_I18N_CITY_PARENT_ID FOREIGN KEY (`parent_id`) REFERENCES `i18n_region`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_city_name`(
    `city_id` INTEGER NOT NULL COMMENT 'City ID',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'Locale',
    `name` VARCHAR(255) NOT NULL COMMENT 'City name',
    PRIMARY KEY (`city_id`,`locale`),
    INDEX IDX_I18N_CITY_NAME_CITY_ID (`city_id`),
    CONSTRAINT FK_I18N_CITY_NAME_CITY_ID FOREIGN KEY (`city_id`) REFERENCES `i18n_city`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_county`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'County ID',
    `parent_id` INTEGER NOT NULL COMMENT 'City ID',
    `code` VARCHAR(10) COMMENT 'County code',
    `default_name` VARCHAR(255) NOT NULL COMMENT 'County default name',
    PRIMARY KEY (`id`),
    INDEX IDX_I18N_COUNTY_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_I18N_COUNTY_PARENT_ID_CODE UNIQUE (`parent_id`,`code`),
    CONSTRAINT FK_I18N_COUNTY_PARENT_ID FOREIGN KEY (`parent_id`) REFERENCES `i18n_city`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_county_name`(
    `county_id` INTEGER NOT NULL COMMENT 'County ID',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'Locale',
    `name` VARCHAR(255) NOT NULL COMMENT 'County name',
    PRIMARY KEY (`county_id`,`locale`),
    INDEX IDX_I18N_COUNTY_NAME_COUNTY_ID (`county_id`),
    CONSTRAINT FK_I18N_COUNTY_NAME_COUNTY_ID FOREIGN KEY (`county_id`) REFERENCES `i18n_county`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_currency`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Currency ID',
    `code` CHAR(3) NOT NULL COMMENT 'ISO 4217 currency code',
    `symbol` VARCHAR(10) NOT NULL DEFAULT '$' COMMENT 'Currency symbol',
    `rate` DECIMAL(12,6) NOT NULL DEFAULT 1 COMMENT 'Currency rate',
    `format` VARCHAR(30) NOT NULL DEFAULT '%s%.2f' COMMENT 'Price format',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_I18N_CURRENCY_CODE UNIQUE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_I18N_CURRENCY` BEFORE UPDATE ON `i18n_currency` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `newsletter_subscriber`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Subscriber ID',
    `email` VARCHAR(255) NOT NULL COMMENT 'Subscriber email',
    `name` VARCHAR(255) DEFAULT '' COMMENT 'Subscriber name',
    `language_id` INTEGER COMMENT 'Language ID',
    `code` CHAR(32) NOT NULL DEFAULT '' COMMENT 'Confirm code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_NEWSLETTER_SUBSCRIBER_LANGUAGE_ID (`language_id`),
    CONSTRAINT UNQ_NEWSLETTER_SUBSCRIBER_EMAIL UNIQUE (`email`),
    CONSTRAINT FK_NEWSLETTER_SUBSCRIBER_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_NEWSLETTER_SUBSCRIBER` BEFORE UPDATE ON `newsletter_subscriber` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `email_queue`(
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Queue ID',
    `template_id` INTEGER COMMENT 'Template ID',
    `from` VARCHAR(255) NOT NULL COMMENT 'Mail from',
    `to` VARCHAR(255) NOT NULL COMMENT 'Rcpt to',
    `status` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `scheduled_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Scheduled time',
    `finished_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Finished time',
    PRIMARY KEY (`id`),
    INDEX IDX_EMAIL_QUEUE_TEMPLATE_ID (`template_id`),
    CONSTRAINT FK_EMAIL_QUEUE_TEMPLATE_ID_EMAIL_TAMPLATE_ID FOREIGN KEY (`template_id`) REFERENCES `email_template`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `resource_category` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Category ID',
    `store_id` INTEGER NULL DEFAULT NULL COMMENT 'Store ID',
    `parent_id` INTEGER NULL DEFAULT NULL COMMENT 'Parent category ID',
    `code` VARCHAR(45) NULL DEFAULT NULL COMMENT 'Category code',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_RESOURCE_CATEGORY_STORE_ID (`store_id`),
    INDEX IDX_RESOURCE_CATEGORY_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_RESOURCE_CATEGORY_CODE UNIQUE (`code`),
    CONSTRAINT FK_RESOURCE_CATEGORY_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_RESOURCE_CATEGORY_PARENT_ID_RESOURCE_CATEGORY_ID FOREIGN KEY (`parent_id`) REFERENCES `resource_category`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_RESOURCE_CATEGORY` BEFORE UPDATE ON `resource_category` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `resource_category_language` (
    `category_id` INTEGER NOT NULL COMMENT 'Category ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Category name',
    PRIMARY KEY (`category_id`,`language_id`),
    INDEX IDX_RESOURCE_CATEGORY_LANGUAGE_CATEGORY_ID (`category_id`),
    INDEX IDX_RESOURCE_CATEGORY_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_RESOURCE_CATEGORY_LANGUAGE_CATEGORY_ID_RESOURCE_CATEGORY_ID FOREIGN KEY (`category_id`) REFERENCES `resource_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_RESOURCE_CATEGORY_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `resource` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Resource ID',
    `store_id` INTEGER NULL DEFAULT NULL COMMENT 'Store ID',
    `category_id` INTEGER NULL DEFAULT NULL COMMENT 'Category ID',
    `real_name` VARCHAR(120) NOT NULL COMMENT 'Real files name',
    `uploaded_name` VARCHAR(120) DEFAULT '' COMMENT 'Uploaded files name',
    `file_type` VARCHAR(20) DEFAULT '' COMMENT 'File MIME',
    `md5` CHAR(32) NULL DEFAULT NULL COMMENT 'Resrouce md5 hash value',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_RESOURCE_STORE_ID (`store_id`),
    INDEX IDX_RESOURCE_CATEGORY_ID (`category_id`),
    INDEX IDX_RESOURCE_MD5 (`md5`),
    CONSTRAINT FK_RESOURCE_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_RESOURCE_CATEGORY_ID_RESOURCE_CATEGORY_ID FOREIGN KEY (`category_id`) REFERENCES `resource_category`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_RESOURCE` BEFORE UPDATE ON `resource` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `eav_entity_type` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'EAV entity type ID',
    `code` VARCHAR(50) NOT NULL COMMENT 'EAV entity type code',
    `entity_table` VARCHAR(30) DEFAULT 'eav_entity' COMMENT 'EAV entity table name',
    `value_table_prefix` VARCHAR(20) DEFAULT 'eav_value' COMMENT 'EAV entity value table name prefix',
    `is_form` BOOLEAN DEFAULT 0 COMMENT 'Is form entity',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_EAV_ENTITY_TYPE_IS_FORM (`is_form`),
    CONSTRAINT UNQ_EAV_ENTITY_TYPE_CODE UNIQUE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_EAV_ENTITY_TYPE` BEFORE UPDATE ON `eav_entity_type` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `eav_attribute_set` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'EAV attribute set ID',
    `type_id` INTEGER NOT NULL COMMENT 'EAV entity type ID',
    `name` VARCHAR(255) DEFAULT '' COMMENT 'EAV attribute set name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_EAV_ATTR_SET_TYPE_ID (`type_id`),
    CONSTRAINT FK_EAV_ATTR_SET_TYPE_ID_EAV_ENTITY_TYPE_ID FOREIGN KEY (`type_id`) REFERENCES `eav_entity_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_EAV_ATTRIBUTE_SET` BEFORE UPDATE ON `eav_attribute_set` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `eav_attribute_group` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'EAV attribute group ID',
    `type_id` INTEGER NOT NULL COMMENT 'EAV entity type ID',
    `name` VARCHAR(255) DEFAULT '' COMMENT 'EAV attribute set name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_EAV_ATTR_GROUP_TYPE_ID (`type_id`),
    CONSTRAINT FK_EAV_ATTR_GROUP_TYPE_ID_EAV_ENTITY_TYPE_ID FOREIGN KEY (`type_id`) REFERENCES `eav_entity_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_EAV_ATTRIBUTE_GROUP` BEFORE UPDATE ON `eav_attribute_group` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `eav_attribute` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'EAV attribute ID',
    `type_id` INTEGER NOT NULL COMMENT 'EAV entity type ID',
    `code` VARCHAR(255) NOT NULL COMMENT 'EAV attribute code',
    `type` VARCHAR(10) NOT NULL COMMENT 'EAV attribute type',
    `input` VARCHAR(10) NOT NULL COMMENT 'EAV attribute form element',
    `validation` VARCHAR(255) DEFAULT '' COMMENT 'EAV attribute form validation',
    `is_required` BOOLEAN DEFAULT 0 COMMENT 'Is attribute required',
    `default_value` VARCHAR(255) DEFAULT '' COMMENT 'Default value',
    `is_unique` BOOLEAN DEFAULT 0 COMMENT 'Is attribute unique',
    `searchable` BOOLEAN DEFAULT 1 COMMENT 'Is attribute use 4 searching',
    `sortable` BOOLEAN DEFAULT 1 COMMENT 'Is attribute use 4 sorting',
    `filterable` BOOLEAN DEFAULT 1 COMMENT 'Is attribute use 4 filter',
    `comparable` BOOLEAN DEFAULT 0 COMMENT 'Is attribute use 4 comparison',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_EAV_ATTR_TYPE_ID (`type_id`),
    INDEX IDX_EAV_ATTR_SEARCHABLE (`searchable`),
    INDEX IDX_EAV_ATTR_SORTABLE (`sortable`),
    INDEX IDX_EAV_ATTR_FILTERABLE (`filterable`),
    INDEX IDX_EAV_ATTR_COMPARABLE (`comparable`),
    CONSTRAINT UNQ_EAV_ATTR_TYPE_ID_CODE UNIQUE (`type_id`,`code`),
    CONSTRAINT CHK_EAV_ATTR_TYPE CHECK (`type` IN ('varchar','int','decimal','text','datetime')),
    CONSTRAINT FK_EAV_ATTR_TYPE_ID_EAV_ENTITY_TYPE_ID FOREIGN KEY (`type_id`) REFERENCES `eav_entity_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_EAV_ATTRIBUTE` BEFORE UPDATE ON `eav_attribute` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `eav_entity_attribute` (
    `attribute_set_id` INTEGER NOT NULL COMMENT 'EAV attribute set ID',
    `attribute_group_id` INTEGER NOT NULL COMMENT 'EAV attribute group ID',
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `sort_order` INTEGER DEFAULT 0 COMMENT 'Sort order',
    PRIMARY KEY (`attribute_set_id`,`attribute_group_id`,`attribute_id`),
    INDEX IDX_EAV_ENTITY_ATTR_ATTR_GROUP_ID (`attribute_group_id`),
    INDEX IDX_EAV_ENTITY_ATTR_ATTR_ID (`attribute_id`),
    INDEX IDX_EAV_ENTITY_ATTR_SORT_ORDER (`sort_order`),
    CONSTRAINT FK_EAV_ENTITY_ATTR_ATTR_SET_ID_EAV_ATTR_SET_ID FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_ENTITY_ATTR_ATTR_GROUP_ID_EAV_ATTR_GROUP_ID FOREIGN KEY (`attribute_group_id`) REFERENCES `eav_attribute_group`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_ENTITY_ATTR_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `eav_attribute_label` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `label` VARCHAR(255) DEFAULT '' COMMENT 'EAV attribute label',
    PRIMARY KEY (`attribute_id`,`language_id`),
    INDEX IDX_EAV_ATTR_LABEL_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_EAV_ATTR_LABEL_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_ATTR_LABEL_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `eav_attribute_option` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'EAV attribute option ID',
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `sort_order` INTEGER DEFAULT 0 COMMENT 'Sort order',
    PRIMARY KEY (`id`),
    INDEX IDX_EAV_ATTR_ATTR_ID (`attribute_id`),
    CONSTRAINT FK_EAV_ATTR_OPTION_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `eav_attribute_option_label` (
    `option_id` INTEGER NOT NULL COMMENT 'EAV attribute option ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `label` VARCHAR(255) DEFAULT '' COMMENT 'EAV attribute option label',
    PRIMARY KEY (`option_id`,`language_id`),
    INDEX IDX_EAV_ATTR_OPTION_LABEL_ATTR_ID (`option_id`),
    INDEX IDX_EAV_ATTR_OPTION_LABEL_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_EAV_ATTR_OPTION_LABEL_OPTION_ID_EAV_ATTR_OPTION_ID FOREIGN KEY (`option_id`) REFERENCES `eav_attribute_option`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_ATTR_OPTION_LABEL_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `eav_entity` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'EAV entity ID',
    `type_id` INTEGER NOT NULL COMMENT 'EAV entity type ID',
    `attribute_set_id` INTEGER NOT NULL COMMENT 'EAV attribute set ID',
    `store_id` INTEGER NOT NULL COMMENT 'Store ID',
    `increment_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Entity increment ID',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_EAV_ENTITY_TYPE_ID (`type_id`),
    INDEX IDX_EAV_ENTITY_ATTRIBUTE_SET_ID (`attribute_set_id`),
    INDEX IDX_EAV_ENTITY_STORE_ID (`store_id`),
    CONSTRAINT FK_EAV_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ID FOREIGN KEY (`type_id`) REFERENCES `eav_entity_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_ENTITY_ATTR_SET_ID_EAV_ATTR_SET_ID FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_ENTITY_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_EAV_ENTITY` BEFORE UPDATE ON `eav_entity` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `eav_value_int` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'EAV entity ID',
    `value` INTEGER NOT NULL COMMENT 'EAV value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_EAV_VALUE_INT_LANGUAGE_ID (`language_id`),
    INDEX IDX_EAV_VALUE_INT_ENTITY_ID (`entity_id`),
    INDEX IDX_EAV_VALUE_INT_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_EAV_VALUE_INT_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_EAV_VALUE_INT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_VALUE_INT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_VALUE_INT_ENTITY_ID_EAV_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `eav_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_EAV_VALUE_INT` BEFORE UPDATE ON `eav_value_int` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `eav_value_datetime` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'EAV entity ID',
    `value` TIMESTAMP NOT NULL COMMENT 'EAV value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_EAV_VALUE_DATETIME_LANGUAGE_ID (`language_id`),
    INDEX IDX_EAV_VALUE_DATETIME_ENTITY_ID (`entity_id`),
    INDEX IDX_EAV_VALUE_DATETIME_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_EAV_VALUE_DATETIME_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_EAV_VALUE_DATETIME_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_VALUE_DATETIME_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_VALUE_DATETIME_ENTITY_ID_EAV_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `eav_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_EAV_VALUE_DATETIME` BEFORE UPDATE ON `eav_value_datetime` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `eav_value_decimal` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'EAV entity ID',
    `value` DECIMAL(12,4) NOT NULL COMMENT 'EAV value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_EAV_VALUE_DECIMAL_LANGUAGE_ID (`language_id`),
    INDEX IDX_EAV_VALUE_DECIMAL_ENTITY_ID (`entity_id`),
    INDEX IDX_EAV_VALUE_DECIMAL_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_EAV_VALUE_DECIMAL_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_EAV_VALUE_DECIMAL_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_VALUE_DECIMAL_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_VALUE_DECIMAL_ENTITY_ID_EAV_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `eav_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_EAV_VALUE_DECIMAL` BEFORE UPDATE ON `eav_value_decimal` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `eav_value_varchar` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'EAV entity ID',
    `value` VARCHAR(255) NOT NULL COMMENT 'EAV value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_EAV_VALUE_VARCHAR_LANGUAGE_ID (`language_id`),
    INDEX IDX_EAV_VALUE_VARCHAR_ENTITY_ID (`entity_id`),
    INDEX IDX_EAV_VALUE_VARCHAR_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_EAV_VALUE_VARCHAR_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_EAV_VALUE_VARCHAR_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_VALUE_VARCHAR_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_VALUE_VARCHAR_ENTITY_ID_EAV_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `eav_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_EAV_VALUE_VARCHAR` BEFORE UPDATE ON `eav_value_varchar` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `eav_value_text` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'EAV entity ID',
    `value` TEXT NOT NULL COMMENT 'EAV value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_EAV_VALUE_TEXT_LANGUAGE_ID (`language_id`),
    INDEX IDX_EAV_VALUE_TEXT_ENTITY_ID (`entity_id`),
    CONSTRAINT FK_EAV_VALUE_TEXT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_VALUE_TEXT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_VALUE_TEXT_ENTITY_ID_EAV_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `eav_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_EAV_VALUE_TEXT` BEFORE UPDATE ON `eav_value_text` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `eav_entity_type` VALUES (1, 'customer', 'customer_entity', 'customer_value', 0, CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_set` VALUES (1, 1, 'Default', CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_group` VALUES (1, 1, 'General', CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute` VALUES 
(1,1,'username','varchar','text','',1,'',1,1,1,1,1,NULL,NULL),
(2,1,'password','varchar','password','',1,'',0,0,0,0,0,NULL,NULL),
(3,1,'email','varchar','email','',1,'',1,1,1,1,1,NULL,NULL);
INSERT INTO `eav_entity_attribute` VALUES 
(1, 1, 1, 0),
(1, 1, 2, 0),
(1, 1, 3, 0);
INSERT INTO `eav_attribute_label` VALUES
(1, 1, 'Username'),
(2, 1, 'Password'),
(3, 1, 'Email');

CREATE TABLE IF NOT EXISTS `customer_entity` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Customer ID',
    `type_id` INTEGER NOT NULL DEFAULT 1 COMMENT 'EAV entity type ID',
    `attribute_set_id` INTEGER NOT NULL COMMENT 'EAV attribute set ID',
    `store_id` INTEGER NOT NULL COMMENT 'Store ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `increment_id` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Entity increment ID',
    `confirm_token` CHAR(32) NULL DEFAULT NULL COMMENT 'Confirming link token',
    `confirm_token_created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Confirming link token creation date',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CUSTOMER_ENTITY_TYPE_ID (`type_id`),
    INDEX IDX_CUSTOMER_ENTITY_ATTRIBUTE_SET_ID (`attribute_set_id`),
    INDEX IDX_CUSTOMER_ENTITY_STORE_ID (`store_id`),
    CONSTRAINT UNQ_CUSTOMER_ENTITY_CONFIRM_TOKEN UNIQUE (`confirm_token`),
    CONSTRAINT FK_CUSTOMER_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ID FOREIGN KEY (`type_id`) REFERENCES `eav_entity_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_ENTITY_ATTR_SET_ID_EAV_ATTR_SET_ID FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_ENTITY_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CUSTOMER_ENTITY` BEFORE UPDATE ON `customer_entity` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `customer_value_int` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Customer entity ID',
    `value` INTEGER NOT NULL COMMENT 'Customer value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_CUSTOMER_VALUE_INT_LANGUAGE_ID (`language_id`),
    INDEX IDX_CUSTOMER_VALUE_INT_ENTITY_ID (`entity_id`),
    INDEX IDX_CUSTOMER_VALUE_INT_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_CUSTOMER_VALUE_INT_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_CUSTOMER_VALUE_INT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_VALUE_INT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_VALUE_INT_ENTITY_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `customer_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CUSTOMER_VALUE_INT` BEFORE UPDATE ON `customer_value_int` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `customer_value_datetime` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Customer entity ID',
    `value` TIMESTAMP NOT NULL COMMENT 'Customer value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_CUSTOMER_VALUE_DATETIME_LANGUAGE_ID (`language_id`),
    INDEX IDX_CUSTOMER_VALUE_DATETIME_ENTITY_ID (`entity_id`),
    INDEX IDX_CUSTOMER_VALUE_DATETIME_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_CUSTOMER_VALUE_DATETIME_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_CUSTOMER_VALUE_DATETIME_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_VALUE_DATETIME_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_VALUE_DATETIME_ENTITY_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `customer_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CUSTOMER_VALUE_DATETIME` BEFORE UPDATE ON `customer_value_datetime` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `customer_value_decimal` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Customer entity ID',
    `value` DECIMAL(12,4) NOT NULL COMMENT 'Customer value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_CUSTOMER_VALUE_DECIMAL_LANGUAGE_ID (`language_id`),
    INDEX IDX_CUSTOMER_VALUE_DECIMAL_ENTITY_ID (`entity_id`),
    INDEX IDX_CUSTOMER_VALUE_DECIMAL_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_CUSTOMER_VALUE_DECIMAL_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_CUSTOMER_VALUE_DECIMAL_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_VALUE_DECIMAL_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_VALUE_DECIMAL_ENTITY_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `customer_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CUSTOMER_VALUE_DECIMAL` BEFORE UPDATE ON `customer_value_decimal` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `customer_value_varchar` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Customer entity ID',
    `value` VARCHAR(255) NOT NULL COMMENT 'Customer value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_CUSTOMER_VALUE_VARCHAR_LANGUAGE_ID (`language_id`),
    INDEX IDX_CUSTOMER_VALUE_VARCHAR_ENTITY_ID (`entity_id`),
    INDEX IDX_CUSTOMER_VALUE_VARCHAR_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_CUSTOMER_VALUE_VARCHAR_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_CUSTOMER_VALUE_VARCHAR_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_VALUE_VARCHAR_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_VALUE_VARCHAR_ENTITY_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `customer_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CUSTOMER_VALUE_VARCHAR` BEFORE UPDATE ON `customer_value_varchar` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `customer_value_text` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Customer entity ID',
    `value` TEXT NOT NULL COMMENT 'Customer value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_CUSTOMER_VALUE_TEXT_LANGUAGE_ID (`language_id`),
    INDEX IDX_CUSTOMER_VALUE_TEXT_ENTITY_ID (`entity_id`),
    CONSTRAINT FK_CUSTOMER_VALUE_TEXT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_VALUE_TEXT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_VALUE_TEXT_ENTITY_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `customer_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CUSTOMER_VALUE_TEXT` BEFORE UPDATE ON `customer_value_text` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `customer_group` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Customer group ID',
    `name` VARCHAR(50) DEFAULT '' COMMENT 'Customer group name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`)
);

CREATE TRIGGER `TGR_UPDATE_CUSTOMER_GROUP` BEFORE UPDATE ON `customer_group` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `customer_group` VALUES (NULL,'Default',NULL,NULL);

CREATE TABLE IF NOT EXISTS `customer_in_group` (
    `group_id` INTEGER NOT NULL COMMENT 'Customer group ID',
    `customer_id` INTEGER NOT NULL COMMENT 'Customer ID',
    PRIMARY KEY (`group_id`,`customer_id`),
    INDEX IDX_CUSTOMER_IN_GROUP_CUSTOMER_ID (`customer_id`),
    CONSTRAINT FK_CUSTOMER_IN_GROUP_GROUP_ID_CUSTOMER_GROUP_ID FOREIGN KEY (`group_id`) REFERENCES `customer_group`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CUSTOMER_IN_GROUP_CUSTOMER_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
); 

CREATE TABLE IF NOT EXISTS `customer_level` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Customer level ID',
    `level` INTEGER NOT NULL DEFAULT 0 COMMENT 'Customer level',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_CUSTOMER_LEVEL_LEVEL UNIQUE (`level`)
);

CREATE TRIGGER `TGR_UPDATE_CUSTOMER_LEVEL` BEFORE UPDATE ON `customer_level` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `customer_level_language` (
    `level_id` INTEGER NOT NULL COMMENT 'Customer level ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `name` VARCHAR(50) DEFAULT '' COMMENT 'Customer level name',
    PRIMARY KEY (`level_id`,`language_id`),
    INDEX IDX_CUSTOMER_LEVEL_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_CUSTOMER_LEVEL_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `oauth_client` (
    `customer_id` INTEGER NOT NULL COMMENT 'Customer ID',
    `oauth_server` VARCHAR(255) NOT NULL COMMENT 'OAuth server name',
    `open_id` VARCHAR(255) NOT NULL COMMENT 'Open ID',
    PRIMARY KEY (`oauth_server`,`customer_id`),
    CONSTRAINT `UNQ_OAUTH_CLIENT_OAUTH_SERVER_OPEN_ID` UNIQUE(`oauth_server`,`open_id`)
);

CREATE TABLE IF NOT EXISTS `api_rest_role` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Role ID',
    `name` VARCHAR(255) DEFAULT '' COMMENT 'Role name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`)
);

CREATE TRIGGER `TGR_UPDATE_API_REST_ROLE` BEFORE UPDATE ON `api_rest_role` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `api_rest_role` VALUES (-1,'Admin',NULL,NULL),(NULL,'Anonymous',NULL,NULL),(NULL,'Customer',NULL,NULL);

CREATE TABLE IF NOT EXISTS `api_rest_attribute` (
    `role_id` INTEGER NOT NULL COMMENT 'Role ID',
    `attribute_id` INTEGER NOT NULL COMMENT 'Attribute ID',
    `writeable` BOOLEAN DEFAULT 0 COMMENT 'Is writeable',
    `readable` BOOLEAN DEFAULT 0 COMMENT 'Is readable',
    PRIMARY KEY (`role_id`,`attribute_id`),
    INDEX IDX_API_REST_ATTR_ATTR_ID (`attribute_id`),
    CONSTRAINT FK_API_REST_ATTR_API_REST_ROLE FOREIGN KEY (`role_id`) REFERENCES `api_rest_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_API_REST_ATTR_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `oauth_consumer` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Consumer ID',
    `name` VARCHAR(255) DEFAULT '' COMMENT 'Consumer name',
    `role_id` INTEGER NOT NULL COMMENT 'Role ID',
    `key` CHAR(32) NOT NULL COMMENT 'Key code',
    `secret` CHAR(32) NOT NULL COMMENT 'Secret code',
    `callback_url` VARCHAR(255) NOT NULL COMMENT 'Callback Url',
    `rejected_callback_url` VARCHAR(255) DEFAULT '' COMMENT 'Rejected callback Url',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_OAUTH_CONSUMER_KEY UNIQUE (`key`),
    CONSTRAINT UNQ_OAUTH_CONSUMER_SECRET UNIQUE (`secret`),
    CONSTRAINT FK_OAUTH_CONSUMER_API_REST_ROLE FOREIGN KEY (`role_id`) REFERENCES `api_rest_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_OAUTH_CONSUMER` BEFORE UPDATE ON `oauth_consumer` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `oauth_token` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Token ID',
    `consumer_id` INTEGER NOT NULL COMMENT 'Consumer ID',
    `open_id` CHAR(32) NOT NULL COMMENT 'Open ID',
    `admin_id` INTEGER NULL DEFAULT NULL COMMENT 'Admin ID',
    `customer_id` INTEGER NULL DEFAULT NULL COMMENT 'Customer ID',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Authorized',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    PRIMARY KEY (`id`),
    INDEX IDX_OAUTH_TOKEN_CONSUMER_ID (`consumer_id`),
    INDEX IDX_OAUTH_TOKEN_CUSTOMER_ID (`customer_id`),
    INDEX IDX_OAUTH_TOKEN_ADMIN_ID (`admin_id`),
    CONSTRAINT CHK_OAUTH_TOKEN_CUSTOMER_ID_ADMIN_ID CHECK ((`admin_id` IS NULL AND `customer_id` IS NOT NULL) OR (`admin_id` IS NOT NULL AND `customer_id` IS NULL)),
    CONSTRAINT UNQ_OAUTH_TOKEN_CONSUMER_ID_CUSTOMER_ID_ADMIN_ID UNIQUE (`consumer_id`,`customer_id`,`admin_id`),
    CONSTRAINT UNQ_OAUTH_TOKEN_CONSUMER_ID_ADMIN_ID_OPEN_ID UNIQUE (`consumer_id`,`admin_id`,`open_id`),
    CONSTRAINT FK_OAUTH_TOKEN_CONSUMER_ID_OAUTH_CONSUMER_ID FOREIGN KEY (`consumer_id`) REFERENCES `oauth_consumer`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_OAUTH_TOKEN_CUSTOMER_ID_ENTITY_CUSTOMER_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `api_soap_role` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Role ID',
    `name` VARCHAR(255) DEFAULT '' COMMENT 'Role name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`)
);

CREATE TRIGGER `TGR_UPDATE_API_SOAP_ROLE` BEFORE UPDATE ON `api_soap_role` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `api_soap_permission` (
    `role_id` INTEGER NOT NULL COMMENT 'Role ID',
    `resource` VARCHAR(255) NOT NULL COMMENT 'Resource',
    `permission` BOOLEAN DEFAULT 1 COMMENT 'Permission',
    PRIMARY KEY (`role_id`,`resource`)
);

CREATE TABLE IF NOT EXISTS `api_soap_user` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'User ID',
    `role_id` INTEGER NOT NULL COMMENT 'Role ID',
    `name` VARCHAR(255) DEFAULT '' COMMENT 'User name',
    `email` VARCHAR(255) DEFAULT '' COMMENT 'User email',
    `key` CHAR(32) NOT NULL COMMENT 'Api key',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_API_SOAP_USER_ROLE_ID (`role_id`),
    CONSTRAINT UNQ_API_SOAP_USER_KEY UNIQUE (`key`),
    CONSTRAINT FK_API_SOAP_USER_ROLE_ID_API_SOAP_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `api_soap_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_API_SOAP_USER` BEFORE UPDATE ON `api_soap_user` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `eav_entity_type` VALUES (2, 'address', 'address_entity', 'address_value', 0, CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_set` VALUES (NULL, 2, 'Default', CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_group` VALUES (NULL, 2, 'General', CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute` VALUES 
(4,2,'name','varchar','text','',1,'',0,0,0,0,0,NULL,NULL),
(5,2,'country','varchar','select','',1,'',0,0,0,0,0,NULL,NULL),
(6,2,'region','varchar','select','',1,'',0,0,0,0,0,NULL,NULL),
(7,2,'city','varchar','select','',1,'',0,0,0,0,0,NULL,NULL),
(8,2,'county','varchar','select','',1,'',0,0,0,0,0,NULL,NULL),
(9,2,'address','text','text','',1,'',0,0,0,0,0,NULL,NULL),
(10,2,'tel','varchar','tel','',1,'',0,0,0,0,0,NULL,NULL),
(11,2,'email','varchar','email','',0,'',0,0,0,0,0,NULL,NULL);
INSERT INTO `eav_entity_attribute` VALUES 
(2, 2, 4, 0),
(2, 2, 5, 0),
(2, 2, 6, 0),
(2, 2, 7, 0),
(2, 2, 8, 0),
(2, 2, 9, 0),
(2, 2, 10, 0),
(2, 2, 11, 0);
INSERT INTO `eav_attribute_label` VALUES
(4, 1, 'Name'),
(5, 1, 'Country'),
(6, 1, 'Region'),
(7, 1, 'City'),
(8, 1, 'County'),
(9, 1, 'Address'),
(10, 1, 'Telephone'),
(11, 1, 'Email');

CREATE TABLE IF NOT EXISTS `address_entity` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Address ID',
    `type_id` INTEGER NOT NULL DEFAULT 2 COMMENT 'EAV entity type ID',
    `attribute_set_id` INTEGER NOT NULL COMMENT 'EAV attribute set ID',
    `store_id` INTEGER NOT NULL COMMENT 'Store ID',
    `customer_id` INTEGER NULL COMMENT 'Customer ID',
    `is_default` BOOLEAN DEFAULT 0 COMMENT 'Is default address',
    `status` BOOLEAN DEFAULT 0 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_ADDRESS_ENTITY_TYPE_ID (`type_id`),
    INDEX IDX_ADDRESS_ENTITY_ATTRIBUTE_SET_ID (`attribute_set_id`),
    INDEX IDX_ADDRESS_ENTITY_CUSTOMER_ID (`customer_id`),
    CONSTRAINT FK_ADDRESS_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ID FOREIGN KEY (`type_id`) REFERENCES `eav_entity_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_ENTITY_ATTR_SET_ID_EAV_ATTR_SET_ID FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_ENTITY_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_ENTITY_CUSTOMER_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_ADDRESS_ENTITY` BEFORE UPDATE ON `address_entity` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `address_value_int` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Address entity ID',
    `value` INTEGER NOT NULL COMMENT 'Address value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ADDRESS_VALUE_INT_LANGUAGE_ID (`language_id`),
    INDEX IDX_ADDRESS_VALUE_INT_ENTITY_ID (`entity_id`),
    INDEX IDX_ADDRESS_VALUE_INT_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ADDRESS_VALUE_INT_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ADDRESS_VALUE_INT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_VALUE_INT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_VALUE_INT_ENTITY_ID_ADDRESS_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `address_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_ADDRESS_VALUE_INT` BEFORE UPDATE ON `address_value_int` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `address_value_datetime` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Address entity ID',
    `value` TIMESTAMP NOT NULL COMMENT 'Address value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ADDRESS_VALUE_DATETIME_LANGUAGE_ID (`language_id`),
    INDEX IDX_ADDRESS_VALUE_DATETIME_ENTITY_ID (`entity_id`),
    INDEX IDX_ADDRESS_VALUE_DATETIME_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ADDRESS_VALUE_DATETIME_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ADDRESS_VALUE_DATETIME_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_VALUE_DATETIME_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_VALUE_DATETIME_ENTITY_ID_ADDRESS_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `address_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_ADDRESS_VALUE_DATETIME` BEFORE UPDATE ON `address_value_datetime` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `address_value_decimal` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Address entity ID',
    `value` DECIMAL(12,4) NOT NULL COMMENT 'Address value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ADDRESS_VALUE_DECIMAL_LANGUAGE_ID (`language_id`),
    INDEX IDX_ADDRESS_VALUE_DECIMAL_ENTITY_ID (`entity_id`),
    INDEX IDX_ADDRESS_VALUE_DECIMAL_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ADDRESS_VALUE_DECIMAL_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ADDRESS_VALUE_DECIMAL_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_VALUE_DECIMAL_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_VALUE_DECIMAL_ENTITY_ID_ADDRESS_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `address_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_ADDRESS_VALUE_DECIMAL` BEFORE UPDATE ON `address_value_decimal` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `address_value_varchar` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Address entity ID',
    `value` VARCHAR(255) NOT NULL COMMENT 'Address value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ADDRESS_VALUE_VARCHAR_LANGUAGE_ID (`language_id`),
    INDEX IDX_ADDRESS_VALUE_VARCHAR_ENTITY_ID (`entity_id`),
    INDEX IDX_ADDRESS_VALUE_VARCHAR_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ADDRESS_VALUE_VARCHAR_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ADDRESS_VALUE_VARCHAR_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_VALUE_VARCHAR_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_VALUE_VARCHAR_ENTITY_ID_ADDRESS_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `address_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_ADDRESS_VALUE_VARCHAR` BEFORE UPDATE ON `address_value_varchar` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `address_value_text` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Address entity ID',
    `value` TEXT NOT NULL COMMENT 'Address value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ADDRESS_VALUE_TEXT_LANGUAGE_ID (`language_id`),
    INDEX IDX_ADDRESS_VALUE_TEXT_ENTITY_ID (`entity_id`),
    CONSTRAINT FK_ADDRESS_VALUE_TEXT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_VALUE_TEXT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADDRESS_VALUE_TEXT_ENTITY_ID_ADDRESS_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `address_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_ADDRESS_VALUE_TEXT` BEFORE UPDATE ON `address_value_text` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `eav_entity_type` VALUES (3, 'product', 'product_entity', 'product_value', 0, CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_set` VALUES (NULL, 3, 'Default', CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_group` VALUES (NULL, 3, 'General', CURRENT_TIMESTAMP, NULL);

CREATE TABLE IF NOT EXISTS `product_type` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Product type ID',
    `code` VARCHAR(20) NOT NULL COMMENT 'Product type code',
    `name` VARCHAR(255) NOT NULL COMMENT 'Product type name',
    PRIMARY KEY (`id`),
    INDEX IDX_PRODUCT_TYPE_CODE (`code`)
);

INSERT INTO `product_type` VALUES (NULL,'simple','Simple Product'),(NULL,'virtual','Virtual Product');

CREATE TABLE IF NOT EXISTS `product_entity` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Product ID',
    `type_id` INTEGER NOT NULL DEFAULT 3 COMMENT 'EAV entity type ID',
    `attribute_set_id` INTEGER NOT NULL COMMENT 'EAV attribute set ID',
    `sku` VARCHAR(255) NOT NULL COMMENT 'Stock keeping unit',
    `product_type_id` INTEGER NOT NULL COMMENT 'Product type',
    `store_id` INTEGER NOT NULL COMMENT 'Store ID',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_PRODUCT_ENTITY_TYPE_ID (`type_id`),
    INDEX IDX_PRODUCT_ENTITY_ATTRIBUTE_SET_ID (`attribute_set_id`),
    INDEX IDX_PRODUCT_ENTITY_STORE_ID (`store_id`),
    INDEX IDX_PRODUCT_ENTITY_PRODUCT_TYPE_ID (`product_type_id`),
    CONSTRAINT UNQ_PRODUCT_ENTITY_SKU UNIQUE (`sku`),
    CONSTRAINT FK_PRODUCT_ENTITY_PRODUCT_TYPE_ID_PRODUCT_TYPE_ID FOREIGN KEY (`product_type_id`) REFERENCES `product_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ID FOREIGN KEY (`type_id`) REFERENCES `eav_entity_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_ENTITY_ATTR_SET_ID_EAV_ATTR_SET_ID FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_ENTITY_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_PRODUCT_ENTITY` BEFORE UPDATE ON `product_entity` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `product_value_int` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Product entity ID',
    `value` INTEGER NOT NULL COMMENT 'Product value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_PRODUCT_VALUE_INT_LANGUAGE_ID (`language_id`),
    INDEX IDX_PRODUCT_VALUE_INT_ENTITY_ID (`entity_id`),
    INDEX IDX_PRODUCT_VALUE_INT_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_PRODUCT_VALUE_INT_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_PRODUCT_VALUE_INT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_VALUE_INT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_VALUE_INT_ENTITY_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_PRODUCT_VALUE_INT` BEFORE UPDATE ON `product_value_int` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `product_value_datetime` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Product entity ID',
    `value` TIMESTAMP NOT NULL COMMENT 'Product value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_PRODUCT_VALUE_DATETIME_LANGUAGE_ID (`language_id`),
    INDEX IDX_PRODUCT_VALUE_DATETIME_ENTITY_ID (`entity_id`),
    INDEX IDX_PRODUCT_VALUE_DATETIME_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_PRODUCT_VALUE_DATETIME_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_PRODUCT_VALUE_DATETIME_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_VALUE_DATETIME_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_VALUE_DATETIME_ENTITY_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_PRODUCT_VALUE_DATETIME` BEFORE UPDATE ON `product_value_datetime` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `product_value_decimal` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Product entity ID',
    `value` DECIMAL(12,4) NOT NULL COMMENT 'Product value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_PRODUCT_VALUE_DECIMAL_LANGUAGE_ID (`language_id`),
    INDEX IDX_PRODUCT_VALUE_DECIMAL_ENTITY_ID (`entity_id`),
    INDEX IDX_PRODUCT_VALUE_DECIMAL_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_PRODUCT_VALUE_DECIMAL_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_PRODUCT_VALUE_DECIMAL_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_VALUE_DECIMAL_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_VALUE_DECIMAL_ENTITY_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_PRODUCT_VALUE_DECIMAL` BEFORE UPDATE ON `product_value_decimal` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `product_value_varchar` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Product entity ID',
    `value` VARCHAR(255) NOT NULL COMMENT 'Product value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_PRODUCT_VALUE_VARCHAR_LANGUAGE_ID (`language_id`),
    INDEX IDX_PRODUCT_VALUE_VARCHAR_ENTITY_ID (`entity_id`),
    INDEX IDX_PRODUCT_VALUE_VARCHAR_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_PRODUCT_VALUE_VARCHAR_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_PRODUCT_VALUE_VARCHAR_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_VALUE_VARCHAR_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_VALUE_VARCHAR_ENTITY_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_PRODUCT_VALUE_VARCHAR` BEFORE UPDATE ON `product_value_varchar` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `product_value_text` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Product entity ID',
    `value` TEXT NOT NULL COMMENT 'Product value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_PRODUCT_VALUE_TEXT_LANGUAGE_ID (`language_id`),
    INDEX IDX_PRODUCT_VALUE_TEXT_ENTITY_ID (`entity_id`),
    CONSTRAINT FK_PRODUCT_VALUE_TEXT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_VALUE_TEXT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_VALUE_TEXT_ENTITY_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_PRODUCT_VALUE_TEXT` BEFORE UPDATE ON `product_value_text` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `product_option` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Product option',
    `product_id` INTEGER NOT NULL COMMENT 'Product ID',
    `input` VARCHAR(10) NOT NULL COMMENT 'EAV attribute form element',
    `is_required` BOOLEAN DEFAULT 0 COMMENT 'Is attribute required',
    `sku` VARCHAR(255) DEFAULT '' COMMENT 'Product option sku',
    `sort_order` INTEGER NOT NULL COMMENT 'Sort order',
    PRIMARY KEY (`id`),
    INDEX IDX_PRODUCT_OPTION_PRODUCT_ID (`product_id`),
    INDEX IDX_PRODUCT_OPTION_SORT_ORDER (`sort_order`),
    CONSTRAINT FK_PRODUCT_OPTION_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `product_option_title` (
    `option_id` INTEGER NOT NULL COMMENT 'Option ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `title` VARCHAR(255) DEFAULT '' COMMENT 'Product option title',
    PRIMARY KEY (`option_id`,`language_id`),
    INDEX IDX_PRODUCT_OPTION_TITLE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_PRODUCT_OPTION_TITLE_OPTION_ID_PRODUCT_OPTION_ID FOREIGN KEY (`option_id`) REFERENCES `product_option`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_OPTION_TITLE_OPTION_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `product_option_price` (
    `option_id` INTEGER NOT NULL COMMENT 'Option ID',
    `store_id` INTEGER NOT NULL COMMENT 'Store ID',
    `price` DECIMAL(12,4) DEFAULT 0 COMMENT 'Product option price',
    `is_fixed` BOOLEAN DEFAULT 1 COMMENT 'Is price fixed or in percent',
    PRIMARY KEY (`option_id`,`store_id`),
    INDEX IDX_PRODUCT_OPTION_PRICE_STORE_ID (`store_id`),
    CONSTRAINT FK_PRODUCT_OPTION_PRICE_OPTION_ID_PRODUCT_OPTION_ID FOREIGN KEY (`option_id`) REFERENCES `product_option`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_OPTION_PRICE_OPTION_ID_PRODUCT_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `product_option_value` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Product option value',
    `option_id` INTEGER NOT NULL COMMENT 'Option ID',
    `sku` VARCHAR(255) DEFAULT '' COMMENT 'Product option sku',
    `sort_order` INTEGER NOT NULL COMMENT 'Sort order',
    PRIMARY KEY (`id`),
    INDEX IDX_PRODUCT_OPTION_VALUE_OPTION_ID (`option_id`),
    INDEX IDX_PRODUCT_OPTION_VALUE_SORT_ORDER (`sort_order`),
    CONSTRAINT FK_PRODUCT_OPTION_VALUE_OPTION_ID_PRODUCT_OPTION_ID FOREIGN KEY (`option_id`) REFERENCES `product_option`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `product_option_value_title` (
    `value_id` INTEGER NOT NULL COMMENT 'Value ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `title` VARCHAR(255) DEFAULT '' COMMENT 'Product option value title',
    PRIMARY KEY (`value_id`,`language_id`),
    INDEX IDX_PRODUCT_OPTION_VALUE_TITLE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_PRODUCT_OPTION_VALUE_TITLE_OPTION_ID_PRODUCT_VALUE_ID FOREIGN KEY (`value_id`) REFERENCES `product_option_value`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_OPTION_VALUE_TITLE_OPTION_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `product_option_value_price` (
    `value_id` INTEGER NOT NULL COMMENT 'Value ID',
    `store_id` INTEGER NOT NULL COMMENT 'Store ID',
    `price` DECIMAL(12,4) DEFAULT 0 COMMENT 'Product option value price',
    `is_fixed` BOOLEAN DEFAULT 1 COMMENT 'Is price fixed or in percent',
    PRIMARY KEY (`value_id`,`store_id`),
    INDEX IDX_PRODUCT_OPTION_VALUE_PRICE_STORE_ID (`store_id`),
    CONSTRAINT FK_PRODUCT_OPTION_VALUE_PRICE_OPTION_ID_PRODUCT_VALUE_ID FOREIGN KEY (`value_id`) REFERENCES `product_option_value`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_OPTION_VALUE_PRICE_OPTION_ID_PRODUCT_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `product_in_store` (
    `product_id` INTEGER NOT NULL COMMENT 'Product ID',
    `store_id` INTEGER NOT NULL COMMENT 'Store ID',
    PRIMARY KEY (`product_id`,`store_id`),
    INDEX IDX_PRODUCT_IN_STORE_STORE_ID (`store_id`),
    CONSTRAINT FK_PRODUCT_IN_STORE_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_IN_STORE_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `warehouse` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Warehouse ID',
    `name` VARCHAR(255) DEFAULT '' COMMENT 'Warehouse Name',
    `country` VARCHAR(3) NOT NULL COMMENT 'Country ISO code',
    `region` VARCHAR(50) DEFAULT '' COMMENT 'Region name',
    `city` VARCHAR(50) DEFAULT '' COMMENT 'City name',
    `address` VARCHAR(255) DEFAULT '' COMMENT 'Address',
    `contact_info` TEXT COMMENT 'Telephone number',
    `longitude` DECIMAL(10,6) NULL DEFAULT NULL COMMENT 'Longitude',
    `latitude` DECIMAL(10,6) NULL DEFAULT NULL COMMENT 'Latitude',
    `open_at` TIME DEFAULT '00:00:00' COMMENT 'Opening time',
    `close_at` TIME DEFAULT '23:59:59' COMMENT 'Closing time',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_WAREHOUSE_OPEN_AT_CLOSE_AT (`open_at`,`close_at`),
    INDEX IDX_WAREHOUSE_STATUS (`status`)
);

CREATE TRIGGER `TGR_UPDATE_WAREHOUSE` BEFORE UPDATE ON `warehouse` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `warehouse_inventory` (
    `warehouse_id` INTEGER NOT NULL COMMENT 'Warehouse ID',
    `product_id` INTEGER NOT NULL COMMENT 'Product ID',
    `sku` VARCHAR(255) NOT NULL COMMENT 'Product sku',
    `barcode` VARCHAR(255) DEFAULT '' COMMENT 'Product barcode',
    `qty` DECIMAL(12,4) NOT NULL COMMENT 'Quentity',
    `reserve_qty` DECIMAL(12,4) DEFAULT 0 COMMENT 'Reserve quentity',
    `min_qty` DECIMAL(12,4) DEFAULT 1 COMMENT 'Minimal quentity',
    `max_qty` DECIMAL(12,4) DEFAULT 10000 COMMENT 'Maximal quentity',
    `is_decimal` BOOLEAN DEFAULT 0 COMMENT 'Is quentity decimal',
    `backorders` BOOLEAN DEFAULT 0 COMMENT 'Is backorders allowed',
    `increment` DECIMAL(12,4) DEFAULT 1 COMMENT 'Quentity increment',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`product_id`,`warehouse_id`,`sku`),
    INDEX IDX_WAREHOUSE_INVENTORY_WAREHOUSE_ID (`warehouse_id`),
    CONSTRAINT CHK_WAREHOUSE_INVENTORY_QTY CHECK (`qty` > 0),
    CONSTRAINT FK_WAREHOUSE_INVENTORY_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_WAREHOUSE_INVENTORY_WAREHOUSE_ID_WAREHOUSE_ID FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_WAREHOUSE_INVENTORY` BEFORE UPDATE ON `warehouse_inventory` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `eav_entity_type` VALUES (4, 'category', 'category_entity', 'category_value', 0, CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_set` VALUES (NULL, 4, 'Default', CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_group` VALUES (NULL, 4, 'General', CURRENT_TIMESTAMP, NULL);

CREATE TABLE IF NOT EXISTS `category_entity` (
    `id` INTEGER NOT NULL AUTO_INCREMENT COMMENT 'Category ID',
    `type_id` INTEGER NOT NULL DEFAULT 4 COMMENT 'EAV entity type ID',
    `parent_id` INTEGER NULL DEFAULT NULL COMMENT 'Parent entity ID',
    `attribute_set_id` INTEGER NOT NULL COMMENT 'EAV attribute set ID',
    `store_id` INTEGER NOT NULL COMMENT 'Store ID',
    `sort_order` INTEGER NOT NULL COMMENT 'Sort order',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CATEGORY_ENTITY_PARENT_ID (`parent_id`),
    INDEX IDX_CATEGORY_ENTITY_TYPE_ID (`type_id`),
    INDEX IDX_CATEGORY_ENTITY_ATTRIBUTE_SET_ID (`attribute_set_id`),
    INDEX IDX_CATEGORY_ENTITY_STORE_ID (`store_id`),
    CONSTRAINT FK_CATEGORY_ENTITY_PARENT_ID_CATEGORY_ENTITY_ID FOREIGN KEY (`parent_id`) REFERENCES `category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ID FOREIGN KEY (`type_id`) REFERENCES `eav_entity_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_ENTITY_ATTR_SET_ID_EAV_ATTR_SET_ID FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_ENTITY_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CATEGORY_ENTITY` BEFORE UPDATE ON `category_entity` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `category_value_int` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Category entity ID',
    `value` INTEGER NOT NULL COMMENT 'Category value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_CATEGORY_VALUE_INT_LANGUAGE_ID (`language_id`),
    INDEX IDX_CATEGORY_VALUE_INT_ENTITY_ID (`entity_id`),
    INDEX IDX_CATEGORY_VALUE_INT_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_CATEGORY_VALUE_INT_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_CATEGORY_VALUE_INT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_VALUE_INT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_VALUE_INT_ENTITY_ID_CATEGORY_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CATEGORY_VALUE_INT` BEFORE UPDATE ON `category_value_int` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `category_value_datetime` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Category entity ID',
    `value` TIMESTAMP NOT NULL COMMENT 'Category value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_CATEGORY_VALUE_DATETIME_LANGUAGE_ID (`language_id`),
    INDEX IDX_CATEGORY_VALUE_DATETIME_ENTITY_ID (`entity_id`),
    INDEX IDX_CATEGORY_VALUE_DATETIME_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_CATEGORY_VALUE_DATETIME_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_CATEGORY_VALUE_DATETIME_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_VALUE_DATETIME_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_VALUE_DATETIME_ENTITY_ID_CATEGORY_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CATEGORY_VALUE_DATETIME` BEFORE UPDATE ON `category_value_datetime` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `category_value_decimal` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Category entity ID',
    `value` DECIMAL(12,4) NOT NULL COMMENT 'Category value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_CATEGORY_VALUE_DECIMAL_LANGUAGE_ID (`language_id`),
    INDEX IDX_CATEGORY_VALUE_DECIMAL_ENTITY_ID (`entity_id`),
    INDEX IDX_CATEGORY_VALUE_DECIMAL_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_CATEGORY_VALUE_DECIMAL_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_CATEGORY_VALUE_DECIMAL_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_VALUE_DECIMAL_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_VALUE_DECIMAL_ENTITY_ID_CATEGORY_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CATEGORY_VALUE_DECIMAL` BEFORE UPDATE ON `category_value_decimal` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `category_value_varchar` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Category entity ID',
    `value` VARCHAR(255) NOT NULL COMMENT 'Category value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_CATEGORY_VALUE_VARCHAR_LANGUAGE_ID (`language_id`),
    INDEX IDX_CATEGORY_VALUE_VARCHAR_ENTITY_ID (`entity_id`),
    INDEX IDX_CATEGORY_VALUE_VARCHAR_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_CATEGORY_VALUE_VARCHAR_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_CATEGORY_VALUE_VARCHAR_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_VALUE_VARCHAR_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_VALUE_VARCHAR_ENTITY_ID_CATEGORY_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CATEGORY_VALUE_VARCHAR` BEFORE UPDATE ON `category_value_varchar` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `category_value_text` (
    `attribute_id` INTEGER NOT NULL COMMENT 'EAV attribute ID',
    `language_id` INTEGER NOT NULL COMMENT 'Language ID',
    `entity_id` INTEGER NOT NULL COMMENT 'Category entity ID',
    `value` TEXT NOT NULL COMMENT 'Category value',
    `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Updated time',
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_CATEGORY_VALUE_TEXT_LANGUAGE_ID (`language_id`),
    INDEX IDX_CATEGORY_VALUE_TEXT_ENTITY_ID (`entity_id`),
    CONSTRAINT FK_CATEGORY_VALUE_TEXT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_VALUE_TEXT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CATEGORY_VALUE_TEXT_ENTITY_ID_CATEGORY_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CATEGORY_VALUE_TEXT` BEFORE UPDATE ON `category_value_text` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `product_in_category` (
    `product_id` INTEGER NOT NULL COMMENT 'Product ID',
    `category_id` INTEGER NOT NULL COMMENT 'Category ID',
    `sort_order` INTEGER DEFAULT 0 COMMENT 'Sort Order',
    PRIMARY KEY (`product_id`,`category_id`),
    INDEX IDX_PRODUCT_IN_CATEGORY_CATEGORY_ID (`category_id`),
    INDEX IDX_PRODUCT_IN_CATEGORY_SORT_ORDER (`sort_order`),
    CONSTRAINT FK_PRODUCT_IN_CATEGORY_PRODUCT_ID_PRODUCT_ENTITY_ID FOREIGN KEY (`product_id`) REFERENCES `product_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_PRODUCT_IN_CATEGORY_PRODUCT_ID_CATEGORY_ENTITY_ID FOREIGN KEY (`category_id`) REFERENCES `category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

SET FOREIGN_KEY_CHECKS = 1;