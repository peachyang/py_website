SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `core_merchant`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Merchant ID',
    `code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Merchant code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `is_default` BOOLEAN NOT NULL DEFAULT 0 COMMENT 'Is default',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
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
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_STORE_MERCHANT_ID (`merchant_id`),
    INDEX IDX_CORE_STORE_MERCHANT_ID_STATUS (`merchant_id`,`status`),
    CONSTRAINT UNQ_CORE_STORE_CODE UNIQUE (`code`),
    CONSTRAINT FK_CORE_STORE_MERCHANT_ID_CORE_MERCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CORE_STORE` BEFORE UPDATE ON `core_store` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_store` VALUES (null,1,'default','Default',1,1,null,null);

CREATE TABLE IF NOT EXISTS `core_language`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Language ID',
    `merchant_id` INTEGER UNSIGNED NOT NULL COMMENT 'Merchant ID',
    `code` VARCHAR(10) NOT NULL DEFAULT '' COMMENT 'RFC 5646 language code',
    `name` VARCHAR(30) NOT NULL DEFAULT '' COMMENT 'Language name',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_LANGUAGE_MERCHANT_ID (`merchant_id`),
    INDEX IDX_CORE_LANGUAGE_MERCHANT_ID_STATUS (`merchant_id`,`status`),
    CONSTRAINT UNQ_CORE_LANGUAGE_MERCHANT_ID_CODE UNIQUE (`merchant_id`,`code`),
    CONSTRAINT FK_CORE_LANGUAGE_MERCHANT_ID_CORE_MERCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CORE_LANGUAGE` BEFORE UPDATE ON `core_language` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_language` VALUES (null,1,'en-US','English',1,1,null,null);

CREATE TABLE IF NOT EXISTS `cms_page`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Page ID',
    `store_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Store ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `uri_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'URI Key',
    `title` VARCHAR(255) DEFAULT '' COMMENT 'Page title',
    `keywords` VARCHAR(255) DEFAULT '' COMMENT 'Meta keywords',
    `description` VARCHAR(255) DEFAULT '' COMMENT 'Meta description',
    `thumbnail` VARCHAR(255) DEFAULT '' COMMENT 'Thumbnail',
    `image` VARCHAR(255) DEFAULT '' COMMENT 'Image',
    `content` BLOB COMMENT 'Page content',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CMS_PAGE_URI_KEY (`uri_key`),
    INDEX IDX_CMS_PAGE_STORE_ID (`store_id`),
    CONSTRAINT FK_CMS_PAGE_STORE_ID_CMS_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CMS_PAGE` BEFORE UPDATE ON `cms_page` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `cms_page_language`(
    `page_id` INTEGER UNSIGNED NOT NULL COMMENT 'Page ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    PRIMARY KEY (`page_id`,`language_id`),
    INDEX IDX_CMS_PAGE_LANGUAGE_PAGE_ID (`page_id`),
    INDEX IDX_CMS_PAGE_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`page_id`) REFERENCES `cms_page`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_PAGE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_category`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Category ID',
    `uri_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'URI Key',
    `parent_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Parent Category ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CMS_CATEGORY_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_CMS_CATEGORY_URI_KEY UNIQUE (`uri_key`),
    CONSTRAINT FK_CMS_CATEGORY_PARENT_ID_CMS_CATEGORY_ID FOREIGN KEY (`parent_id`) REFERENCES `cms_category`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CMS_CATEGORY` BEFORE UPDATE ON `cms_category` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `cms_category_language`(
    `category_id` INTEGER UNSIGNED NOT NULL COMMENT 'Category ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    `name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Category name',
    PRIMARY KEY (`category_id`,`language_id`),
    INDEX IDX_CMS_CATEGORY_LANGUAGE_CATEGORY_ID (`category_id`),
    INDEX IDX_CMS_CATEGORY_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_CMS_CATEGORY_LANGUAGE_CATEGORY_ID_CMS_CATEGORY_ID FOREIGN KEY (`category_id`) REFERENCES `cms_category`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_CATEGORY_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_category_page`(
    `category_id` INTEGER UNSIGNED NOT NULL COMMENT 'Category ID',
    `page_id` INTEGER UNSIGNED NOT NULL COMMENT 'Page ID',
    PRIMARY KEY (`category_id`,`page_id`),
    INDEX IDX_CMS_CATEGORY_PAGE_CATEGORY_ID (`category_id`),
    INDEX IDX_CMS_CATEGORY_PAGE_PAGE_ID (`page_id`),
    CONSTRAINT FK_CMS_CATEGORY_PAGE_CATEGORY_ID_CMS_CATEGORY_ID FOREIGN KEY (`category_id`) REFERENCES `cms_category`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CMS_CATEGORY_PAGE_PAGE_ID_CMS_PAGE_ID FOREIGN KEY (`page_id`) REFERENCES `cms_page`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `cms_block`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Block ID',
    `store_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Store ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Identify code',
    `content` BLOB COMMENT 'Page content',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CMS_BLOCK_STORE_ID (`store_id`),
    CONSTRAINT IDX_CMS_BLOCK_CODE UNIQUE (`code`),
    CONSTRAINT FK_CMS_BLOCK_STORE_ID_CMS_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CMS_BLOCK` BEFORE UPDATE ON `cms_block` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `cms_block_language`(
    `block_id` INTEGER UNSIGNED NOT NULL COMMENT 'Block ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    PRIMARY KEY (`block_id`,`language_id`),
    INDEX IDX_CMS_BLOCK_LANGUAGE_PAGE_ID (`block_id`),
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
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_ADMIN_ROLE_NAME UNIQUE (`name`)
);

CREATE TRIGGER `TGR_UPDATE_ADMIN_OPERATION` BEFORE UPDATE ON `admin_operation` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `admin_operation` VALUES(-1,'ALL','',0,null,null);

CREATE TABLE IF NOT EXISTS `admin_role` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Role ID',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Is enabled',
    `name` VARCHAR(255) NOT NULL COMMENT 'Role name',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_ADMIN_ROLE_NAME UNIQUE (`name`)
);

CREATE TRIGGER `TGR_UPDATE_ADMIN_ROLE` BEFORE UPDATE ON `admin_role` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `admin_role` VALUES (null,1,'Administrator',null,null);

CREATE TABLE IF NOT EXISTS `admin_role_recursive`(
    `role_id` INTEGER UNSIGNED NOT NULL COMMENT 'Role ID',
    `child_id` INTEGER UNSIGNED NOT NULL COMMENT 'Child ID',
    PRIMARY KEY (`role_id`,`child_id`),
    INDEX IDX_ADMIN_ROLE_RECURSIVE_ROLE_ID (`role_id`),
    INDEX IDX_ADMIN_ROLE_RECURSIVE_CHILD_ID (`child_id`),
    CONSTRAINT FK_ADMIN_ROLE_ROLE_ID_ADMIN_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ADMIN_ROLE_CHILD_ID_ADMIN_ROLE_ID FOREIGN KEY (`child_id`) REFERENCES `admin_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `admin_permission` (
    `role_id` INTEGER UNSIGNED NOT NULL COMMENT 'Role ID',
    `operation_id` INTEGER NOT NULL COMMENT 'Operation ID',
    `permission` BOOLEAN NOT NULL DEFAULT '1' COMMENT 'Permission',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    PRIMARY KEY (`role_id`,`operation_id`),
    INDEX IDX_ADMIN_PERMISSION_ROLE_ID (`role_id`),
    INDEX IDX_ADMIN_PERMISSION_OPERATION_ID (`operation_id`),
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
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
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
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Config ID',
    `merchant_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Merchant ID',
    `store_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Store ID',
    `path` VARCHAR(255) NOT NULL COMMENT 'Config path',
    `value` VARCHAR(255) COMMENT 'Config value',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_CORE_CONFIG_STORE_ID (`store_id`),
    INDEX IDX_CORE_CONFIG_MERCHANT_ID (`merchant_id`),
    INDEX IDX_CORE_CONFIG_STORE_ID_PATH (`store_id`,`path`),
    INDEX IDX_CORE_CONFIG_MERCHANT_ID_PATH (`merchant_id`,`path`),
    CONSTRAINT UNQ_CORE_CONFIG_MERCHANT_ID_STORE_ID_PATH UNIQUE (`merchant_id`,`store_id`,`path`),
    CONSTRAINT FK_CORE_CONFIG_MERCHANT_ID_CORE_MARCHANT_ID FOREIGN KEY (`merchant_id`) REFERENCES `core_merchant`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_CORE_CONFIG_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_CORE_CONFIG` BEFORE UPDATE ON `core_config` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

INSERT INTO `core_config` VALUES (null,1,null,'global/base_url','/',null,null),(null,1,NULL,'global/admin_path','admin',null,null);

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
    `status` BOOLEAN DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_EMAIL_TEMPLATE_CODE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_EMAIL_TEMPLATE` BEFORE UPDATE ON `email_template` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `email_template_language`(
    `template_id` INTEGER UNSIGNED NOT NULL COMMENT 'Template ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    PRIMARY KEY (`template_id`,`language_id`),
    INDEX IDX_EMAIL_TEMPLATE_LANGUAGE_TEMPLATE_ID (`template_id`),
    INDEX IDX_EMAIL_TEMPLATE_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_EMAIL_TAMPLATE_LANGUAGE_TEMPLATE_ID_EMAIL_TAMPLATE_ID FOREIGN KEY (`template_id`) REFERENCES `email_template`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EMAIL_TAMPLATE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `message_template` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Template ID',
    `code` VARCHAR(255) NOT NULL COMMENT 'Template code',
    `content` BLOB COMMENT 'Content',
    `status` BOOLEAN DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_MESSAGE_TEMPLATE_CODE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_MESSAGE_TEMPLATE` BEFORE UPDATE ON `message_template` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `message_template_language`(
    `template_id` INTEGER UNSIGNED NOT NULL COMMENT 'Template ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    PRIMARY KEY (`template_id`,`language_id`),
    INDEX IDX_MESSAGE_TEMPLATE_LANGUAGE_TEMPLATE_ID (`template_id`),
    INDEX IDX_MESSAGE_TEMPLATE_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_MESSAGE_TAMPLATE_LANGUAGE_TEMPLATE_ID_EMAIL_TAMPLATE_ID FOREIGN KEY (`template_id`) REFERENCES `email_template`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_MESSAGE_TAMPLATE_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `core_schedule`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Schedule ID',
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
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Translation ID',
    `string` VARCHAR(255) NOT NULL COMMENT 'Translation string',
    `translate` VARCHAR(255) NOT NULL COMMENT 'Translate',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'Locale',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_I18N_TRANSLATION_LOCALE_STATUS (`locale`,`status`),
    INDEX IDX_I18N_TRANSLATION_STRING_LOCALE_STATUS (`string`,`locale`,`status`)
);

CREATE TRIGGER `TGR_UPDATE_I18N_TRANSLATION` BEFORE UPDATE ON `i18n_translation` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `i18n_country`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Country ID',
    `iso2_code` CHAR(2) NOT NULL COMMENT 'Country iso2 code',
    `iso3_code` CHAR(3) NOT NULL COMMENT 'Country iso3 code',
    `default_name` VARCHAR(50) NOT NULL COMMENT 'Country name',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_I18N_COUNTRY_ISO2_CODE UNIQUE (`iso2_code`),
    CONSTRAINT UNQ_I18N_COUNTRY_ISO3_CODE UNIQUE (`iso3_code`)
);

CREATE TABLE IF NOT EXISTS `i18n_country_name`(
    `country_id` INTEGER UNSIGNED NOT NULL COMMENT 'Country ID',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'Locale',
    `name` VARCHAR(255) NOT NULL COMMENT 'Region name',
    PRIMARY KEY (`country_id`,`locale`),
    INDEX IDX_I18N_COUNTRY_NAME_COUNTRY_ID (`country_id`),
    CONSTRAINT FK_I18N_COUNTRY_NAME_COUNTRY_ID FOREIGN KEY (`country_id`) REFERENCES `i18n_country`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_region`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Region ID',
    `parent_id` INTEGER UNSIGNED NOT NULL COMMENT 'Country ID',
    `code` VARCHAR(10) NOT NULL COMMENT 'Region code',
    `default_name` VARCHAR(255) NOT NULL COMMENT 'Region default name',
    PRIMARY KEY (`id`),
    INDEX IDX_I18N_REGION_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_I18N_REGION_PARENT_ID_CODE UNIQUE (`parent_id`,`code`),
    CONSTRAINT FK_I18N_REGION_PARENT_ID FOREIGN KEY (`parent_id`) REFERENCES `i18n_country`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_region_name`(
    `region_id` INTEGER UNSIGNED NOT NULL COMMENT 'Region ID',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'Locale',
    `name` VARCHAR(255) NOT NULL COMMENT 'Region name',
    PRIMARY KEY (`region_id`,`locale`),
    INDEX IDX_I18N_REGION_NAME_REGION_ID (`region_id`),
    CONSTRAINT FK_I18N_REGION_NAME_REGION_ID FOREIGN KEY (`region_id`) REFERENCES `i18n_region`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_city`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'City ID',
    `parent_id` INTEGER UNSIGNED NOT NULL COMMENT 'Region ID',
    `code` VARCHAR(10) NOT NULL COMMENT 'City code',
    `default_name` VARCHAR(255) NOT NULL COMMENT 'City default name',
    PRIMARY KEY (`id`),
    INDEX IDX_I18N_CITY_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_I18N_CITY_PARENT_ID_CODE UNIQUE (`parent_id`,`code`),
    CONSTRAINT FK_I18N_CITY_PARENT_ID FOREIGN KEY (`parent_id`) REFERENCES `i18n_region`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_city_name`(
    `city_id` INTEGER UNSIGNED NOT NULL COMMENT 'City ID',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'Locale',
    `name` VARCHAR(255) NOT NULL COMMENT 'City name',
    PRIMARY KEY (`city_id`,`locale`),
    INDEX IDX_I18N_CITY_NAME_CITY_ID (`city_id`),
    CONSTRAINT FK_I18N_CITY_NAME_CITY_ID FOREIGN KEY (`city_id`) REFERENCES `i18n_city`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_county`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'County ID',
    `parent_id` INTEGER UNSIGNED NOT NULL COMMENT 'City ID',
    `code` VARCHAR(10) COMMENT 'County code',
    `default_name` VARCHAR(255) NOT NULL COMMENT 'County default name',
    PRIMARY KEY (`id`),
    INDEX IDX_I18N_COUNTY_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_I18N_COUNTY_PARENT_ID_CODE UNIQUE (`parent_id`,`code`),
    CONSTRAINT FK_I18N_COUNTY_PARENT_ID FOREIGN KEY (`parent_id`) REFERENCES `i18n_city`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_county_name`(
    `county_id` INTEGER UNSIGNED NOT NULL COMMENT 'County ID',
    `locale` VARCHAR(20) NOT NULL DEFAULT 'en-US' COMMENT 'Locale',
    `name` VARCHAR(255) NOT NULL COMMENT 'County name',
    PRIMARY KEY (`county_id`,`locale`),
    INDEX IDX_I18N_COUNTY_NAME_COUNTY_ID (`county_id`),
    CONSTRAINT FK_I18N_COUNTY_NAME_COUNTY_ID FOREIGN KEY (`county_id`) REFERENCES `i18n_county`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `i18n_currency`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Currency ID',
    `code` CHAR(3) NOT NULL COMMENT 'ISO 4217 currency code',
    `symbol` VARCHAR(10) NOT NULL DEFAULT '$' COMMENT 'Currency symbol',
    `rate` DECIMAL(12,6) NOT NULL DEFAULT 1 COMMENT 'Currency rate',
    `format` VARCHAR(30) NOT NULL DEFAULT '%s%.2f' COMMENT 'Price format',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    CONSTRAINT UNQ_I18N_CURRENCY_CODE UNIQUE (`code`)
);

CREATE TRIGGER `TGR_UPDATE_I18N_CURRENCY` BEFORE UPDATE ON `i18n_currency` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `newsletter_subscriber`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Subscriber ID',
    `email` VARCHAR(255) NOT NULL COMMENT 'Subscriber email',
    `name` VARCHAR(255) DEFAULT '' COMMENT 'Subscriber name',
    `language_id` INTEGER UNSIGNED COMMENT 'Language ID',
    `code` CHAR(32) NOT NULL DEFAULT '' COMMENT 'Confirm code',
    `status` BOOLEAN NOT NULL DEFAULT 1 COMMENT 'Status',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated time',
    PRIMARY KEY (`id`),
    INDEX IDX_NEWSLETTER_SUBSCRIBER_LANGUAGE_ID (`language_id`),
    CONSTRAINT UNQ_NEWSLETTER_SUBSCRIBER_EMAIL UNIQUE (`email`),
    CONSTRAINT FK_NEWSLETTER_SUBSCRIBER_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_NEWSLETTER_SUBSCRIBER` BEFORE UPDATE ON `newsletter_subscriber` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `email_queue`(
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Queue ID',
    `template_id` INTEGER UNSIGNED COMMENT 'Template ID',
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
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Category ID',
    `store_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Store ID',
    `parent_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Parent category ID',
    `code` VARCHAR(45) DEFAULT NULL COMMENT 'Category code',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated Time',
    PRIMARY KEY (`id`),
    INDEX IDX_RESOURCE_CATEGORY_STORE_ID (`store_id`),
    INDEX IDX_RESOURCE_CATEGORY_PARENT_ID (`parent_id`),
    CONSTRAINT UNQ_RESOURCE_CATEGORY_CODE UNIQUE (`code`),
    CONSTRAINT FK_RESOURCE_CATEGORY_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT FK_RESOURCE_CATEGORY_PARENT_ID_RESOURCE_CATEGORY_ID FOREIGN KEY (`parent_id`) REFERENCES `resource_category`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_RESOURCE_CATEGORY` BEFORE UPDATE ON `resource_category` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS `resource_category_language` (
    `category_id` INTEGER UNSIGNED NOT NULL COMMENT 'Category ID',
    `language_id` INTEGER UNSIGNED NOT NULL COMMENT 'Language ID',
    `name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT 'Category name',
    PRIMARY KEY (`category_id`,`language_id`),
    INDEX IDX_RESOURCE_CATEGORY_LANGUAGE_CATEGORY_ID (`category_id`),
    INDEX IDX_RESOURCE_CATEGORY_LANGUAGE_LANGUAGE_ID (`language_id`),
    CONSTRAINT `FK_RESOURCE_CATEGORY_LANGUAGE_CATEGORY_ID_RESOURCE_CATEGORY_ID` FOREIGN KEY (`category_id`) REFERENCES `resource_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_RESOURCE_CATEGORY_LANGUAGE_LANGUAGE_ID_CORE_LANGUAGE_ID` FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `resource` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Resource ID',
    `store_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Store ID',
    `category_id` INTEGER UNSIGNED DEFAULT NULL COMMENT 'Category ID',
    `file_name` VARCHAR(120) NOT NULL COMMENT 'File name',
    `old_name` VARCHAR(120) DEFAULT NULL COMMENT 'Uploaded file name',
    `file_type` VARCHAR(20) DEFAULT '' COMMENT 'File MIME',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Created Time',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Updated TIme',
    PRIMARY KEY (`id`),
    INDEX IDX_RESOURCE_STORE_ID (`store_id`),
    INDEX IDX_RESOURCE_CATEGORY_ID (`category_id`),
    INDEX IDX_RESOURCE_CATEGORY_ID_FILE_TYPE (`category_id`,`file_type`),
    CONSTRAINT `FK_RESOURCE_STORE_ID_CORE_STORE_ID` FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_RESOURCE_CATEGORY_ID_RESOURCE_CATEGORY_ID` FOREIGN KEY (`category_id`) REFERENCES `resource_category`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TRIGGER `TGR_UPDATE_RESOURCE` BEFORE UPDATE ON `resource` FOR EACH ROW SET NEW.`updated_at`=CURRENT_TIMESTAMP;

SET FOREIGN_KEY_CHECKS = 1;