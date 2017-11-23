CREATE TABLE IF NOT EXISTS `art_category_entity` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_id` INTEGER UNSIGNED NOT NULL DEFAULT 5,
    `parent_id` INTEGER UNSIGNED NULL DEFAULT NULL,
    `attribute_set_id` INTEGER UNSIGNED NOT NULL,
    `store_id` INTEGER UNSIGNED NOT NULL,
    `sort_order` INTEGER DEFAULT 0,
    `status` BOOLEAN DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX IDX_ART_CATEGORY_ENTITY_PARENT_ID (`parent_id`),
    INDEX IDX_ART_CATEGORY_ENTITY_TYPE_ID (`type_id`),
    INDEX IDX_ART_CATEGORY_ENTITY_ATTRIBUTE_SET_ID (`attribute_set_id`),
    INDEX IDX_ART_CATEGORY_ENTITY_STORE_ID (`store_id`),
    CONSTRAINT FK_ART_CATEGORY_ENTITY_PARENT_ID_ART_CATEGORY_ENTITY_ID FOREIGN KEY (`parent_id`) REFERENCES `art_category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ID FOREIGN KEY (`type_id`) REFERENCES `eav_entity_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_ENTITY_ATTR_SET_ID_EAV_ATTR_SET_ID FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_ENTITY_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE IF NOT EXISTS `art_category_value_int` (
    `attribute_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `entity_id` INTEGER UNSIGNED NOT NULL,
    `value` INTEGER NOT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ART_CATEGORY_VALUE_INT_LANGUAGE_ID (`language_id`),
    INDEX IDX_ART_CATEGORY_VALUE_INT_ENTITY_ID (`entity_id`),
    INDEX IDX_ART_CATEGORY_VALUE_INT_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ART_CATEGORY_VALUE_INT_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ART_CATEGORY_VALUE_INT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_VALUE_INT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_VALUE_INT_ENTITY_ID_ART_CATEGORY_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `art_category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `art_category_value_datetime` (
    `attribute_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `entity_id` INTEGER UNSIGNED NOT NULL,
    `value` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ART_CATEGORY_VALUE_DATETIME_LANGUAGE_ID (`language_id`),
    INDEX IDX_ART_CATEGORY_VALUE_DATETIME_ENTITY_ID (`entity_id`),
    INDEX IDX_ART_CATEGORY_VALUE_DATETIME_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ART_CATEGORY_VALUE_DATETIME_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ART_CATEGORY_VALUE_DATETIME_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_VALUE_DATETIME_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_VALUE_DATETIME_ENTITY_ID_ART_CATEGORY_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `art_category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `art_category_value_decimal` (
    `attribute_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `entity_id` INTEGER UNSIGNED NOT NULL,
    `value` DECIMAL(12,4) NOT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ART_CATEGORY_VALUE_DECIMAL_LANGUAGE_ID (`language_id`),
    INDEX IDX_ART_CATEGORY_VALUE_DECIMAL_ENTITY_ID (`entity_id`),
    INDEX IDX_ART_CATEGORY_VALUE_DECIMAL_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ART_CATEGORY_VALUE_DECIMAL_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ART_CATEGORY_VALUE_DECIMAL_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_VALUE_DECIMAL_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_VALUE_DECIMAL_ENTITY_ID_ART_CATEGORY_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `art_category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `art_category_value_varchar` (
    `attribute_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `entity_id` INTEGER UNSIGNED NOT NULL,
    `value` VARCHAR(255) NOT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ART_CATEGORY_VALUE_VARCHAR_LANGUAGE_ID (`language_id`),
    INDEX IDX_ART_CATEGORY_VALUE_VARCHAR_ENTITY_ID (`entity_id`),
    INDEX IDX_ART_CATEGORY_VALUE_VARCHAR_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ART_CATEGORY_VALUE_VARCHAR_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ART_CATEGORY_VALUE_VARCHAR_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_VALUE_VARCHAR_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_VALUE_VARCHAR_ENTITY_ID_ART_CATEGORY_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `art_category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `art_category_value_text` (
    `attribute_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `entity_id` INTEGER UNSIGNED NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ART_CATEGORY_VALUE_TEXT_LANGUAGE_ID (`language_id`),
    INDEX IDX_ART_CATEGORY_VALUE_TEXT_ENTITY_ID (`entity_id`),
    CONSTRAINT FK_ART_CATEGORY_VALUE_TEXT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_VALUE_TEXT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ART_CATEGORY_VALUE_TEXT_ENTITY_ID_ART_CATEGORY_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `art_category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `eav_attribute_set_label` (
    `attribute_set_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `label` VARCHAR(255) DEFAULT '',
    PRIMARY KEY (`attribute_set_id`,`language_id`),
    INDEX IDX_EAV_ATTR_SET_LABEL_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_EAV_ATTR_SET_LABEL_ATTR_SET_ID FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_ATTR_SET_LABEL_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `eav_attribute_group_label` (
    `attribute_group_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `label` VARCHAR(255) DEFAULT '',
    PRIMARY KEY (`attribute_group_id`,`language_id`),
    INDEX IDX_EAV_ATTR_GROUP_LABEL_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_EAV_ATTR_GROUP_LABEL_ATTR_GROUP_ID FOREIGN KEY (`attribute_group_id`) REFERENCES `eav_attribute_group`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_EAV_ATTR_GROUP_LABEL_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `eav_entity_type` VALUES (5, 'art_category', 'art_category_entity', 'art_category_value', 0, CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_set` VALUES (NULL, 5, 'Default', CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_group` VALUES (NULL, 5, 'Article Category Infomation', 0, CURRENT_TIMESTAMP, NULL),
(NULL, 5, 'Display Settings', 0, CURRENT_TIMESTAMP, NULL);

INSERT INTO `eav_attribute` VALUES 
(97,5,'name','varchar','text','',1,'',0,NULL,NULL,0,1,0,0,NULL,NULL),
(98,5,'description','text','wysiwyg','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(99,5,'uri_key','varchar','text','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(100,5,'meta_title','varchar','text','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(101,5,'meta_description','varchar','text','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(102,5,'meta_keywords','varchar','text','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(103,5,'author','varchar','text','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(104,5,'source','varchar','text','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(105,5,'thumbnail','int','resource','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(106,5,'image','int','resource','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(107,5,'include_in_menu','varchar','select','',1,'1',0,'\\Seahinet\\Lib\\Source\\Yesno',NULL,0,0,0,0,NULL,NULL),
(108,5,'display_mode','varchar','select','',1,'0',0,'\\Seahinet\\Article\\Source\\DisplayMode',NULL,0,0,0,0,NULL,NULL),
(109,5,'block','varchar','select','',0,'',0,'\\Seahinet\\Cms\\Source\\Block',NULL,0,0,0,0,NULL,NULL),
(110,5,'sortable','varchar','multiselect','',1,'',0,'\\Seahinet\\Article\\Source\\Sortable',NULL,0,0,0,0,NULL,NULL),
(111,5,'default_sortable','varchar','select','',1,'',0,'\\Seahinet\\Article\\Source\\Sortable',NULL,0,0,0,0,NULL,NULL);
INSERT INTO `eav_entity_attribute` VALUES 
(12, 20, 97, 0),
(12, 20, 98, 0),
(12, 20, 99, 0),
(12, 20, 100, 0),
(12, 20, 101, 0),
(12, 20, 102, 0),
(12, 20, 103, 0),
(12, 20, 104, 0),
(12, 20, 105, 0),
(12, 20, 106, 0),
(12, 20, 107, 0),
(12, 21, 108, 0),
(12, 21, 109, 0),
(12, 21, 110, 0),
(12, 21, 111, 0);
INSERT INTO `eav_attribute_label` VALUES
(97, 1, 'Name'),
(98, 1, 'Description'),
(99, 1, 'Uri Key'),
(100, 1, 'Meta Title'),
(101, 1, 'Meta Description'),
(102, 1, 'Meta Keywords'),
(103, 1, 'Autohr'),
(104, 1, 'Source'),
(105, 1, 'Thumbnail'),
(106, 1, 'Image'),
(107, 1, 'Include in Navigation Menu'),
(108, 1, 'Display Mode'),
(109, 1, 'CMS Block'),
(110, 1, 'Available Product Listing Sort By'),
(111, 1, 'Default Product Listing Sort By');


CREATE TABLE IF NOT EXISTS `article_entity` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `type_id` INTEGER UNSIGNED NOT NULL DEFAULT 3,
    `attribute_set_id` INTEGER UNSIGNED NOT NULL,
    `status` BOOLEAN DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX IDX_ARTICLE_ENTITY_TYPE_ID (`type_id`),
    INDEX IDX_ARTICLE_ENTITY_ATTRIBUTE_SET_ID (`attribute_set_id`),
    CONSTRAINT FK_ARTICLE_ENTITY_TYPE_ID_EAV_ENTITY_TYPE_ID FOREIGN KEY (`type_id`) REFERENCES `eav_entity_type`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_ENTITY_ATTR_SET_ID_EAV_ATTR_SET_ID FOREIGN KEY (`attribute_set_id`) REFERENCES `eav_attribute_set`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_value_int` (
    `attribute_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `entity_id` INTEGER UNSIGNED NOT NULL,
    `value` INTEGER NOT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ARTICLE_VALUE_INT_LANGUAGE_ID (`language_id`),
    INDEX IDX_ARTICLE_VALUE_INT_ENTITY_ID (`entity_id`),
    INDEX IDX_ARTICLE_VALUE_INT_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ARTICLE_VALUE_INT_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ARTICLE_VALUE_INT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_VALUE_INT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_VALUE_INT_ENTITY_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_value_datetime` (
    `attribute_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `entity_id` INTEGER UNSIGNED NOT NULL,
    `value` TIMESTAMP NOT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ARTICLE_VALUE_DATETIME_LANGUAGE_ID (`language_id`),
    INDEX IDX_ARTICLE_VALUE_DATETIME_ENTITY_ID (`entity_id`),
    INDEX IDX_ARTICLE_VALUE_DATETIME_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ARTICLE_VALUE_DATETIME_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ARTICLE_VALUE_DATETIME_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_VALUE_DATETIME_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_VALUE_DATETIME_ENTITY_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_value_decimal` (
    `attribute_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `entity_id` INTEGER UNSIGNED NOT NULL,
    `value` DECIMAL(12,4) NOT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ARTICLE_VALUE_DECIMAL_LANGUAGE_ID (`language_id`),
    INDEX IDX_ARTICLE_VALUE_DECIMAL_ENTITY_ID (`entity_id`),
    INDEX IDX_ARTICLE_VALUE_DECIMAL_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ARTICLE_VALUE_DECIMAL_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ARTICLE_VALUE_DECIMAL_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_VALUE_DECIMAL_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_VALUE_DECIMAL_ENTITY_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_value_varchar` (
    `attribute_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `entity_id` INTEGER UNSIGNED NOT NULL,
    `value` VARCHAR(255) NOT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ARTICLE_VALUE_VARCHAR_LANGUAGE_ID (`language_id`),
    INDEX IDX_ARTICLE_VALUE_VARCHAR_ENTITY_ID (`entity_id`),
    INDEX IDX_ARTICLE_VALUE_VARCHAR_ATTR_ID_VALUE (`attribute_id`,`value`),
    INDEX IDX_ARTICLE_VALUE_VARCHAR_ENTITY_ID_VALUE (`entity_id`,`value`),
    CONSTRAINT FK_ARTICLE_VALUE_VARCHAR_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_VALUE_VARCHAR_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_VALUE_VARCHAR_ENTITY_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_value_text` (
    `attribute_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `entity_id` INTEGER UNSIGNED NOT NULL,
    `value` MEDIUMTEXT NOT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`attribute_id`,`language_id`,`entity_id`),
    INDEX IDX_ARTICLE_VALUE_TEXT_LANGUAGE_ID (`language_id`),
    INDEX IDX_ARTICLE_VALUE_TEXT_ENTITY_ID (`entity_id`),
    CONSTRAINT FK_ARTICLE_VALUE_TEXT_ATTR_ID_EAV_ATTR_ID FOREIGN KEY (`attribute_id`) REFERENCES `eav_attribute`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_VALUE_TEXT_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_VALUE_TEXT_ENTITY_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`entity_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_option` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INTEGER UNSIGNED NOT NULL,
    `input` VARCHAR(11) NOT NULL,
    `is_required` BOOLEAN DEFAULT 0,
    `sku` VARCHAR(255) DEFAULT '',
    `price` DECIMAL(12,4) DEFAULT 0,
    `is_fixed` BOOLEAN DEFAULT 1,
    `sort_order` INTEGER DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX IDX_ARTICLE_OPTION_ARTICLE_ID (`article_id`),
    INDEX IDX_ARTICLE_OPTION_SORT_ORDER (`sort_order`),
    CONSTRAINT FK_ARTICLE_OPTION_ARTICLE_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`article_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_option_title` (
    `option_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `title` VARCHAR(255) DEFAULT '',
    PRIMARY KEY (`option_id`,`language_id`),
    INDEX IDX_ARTICLE_OPTION_TITLE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_ARTICLE_OPTION_TITLE_OPTION_ID_ARTICLE_OPTION_ID FOREIGN KEY (`option_id`) REFERENCES `article_option`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_OPTION_TITLE_OPTION_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_option_value` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `option_id` INTEGER UNSIGNED NOT NULL,
    `sku` VARCHAR(255) DEFAULT '',
    `price` DECIMAL(12,4) DEFAULT 0,
    `is_fixed` BOOLEAN DEFAULT 1,
    `sort_order` INTEGER DEFAULT 0,
    PRIMARY KEY (`id`),
    INDEX IDX_ARTICLE_OPTION_VALUE_OPTION_ID (`option_id`),
    INDEX IDX_ARTICLE_OPTION_VALUE_SORT_ORDER (`sort_order`),
    CONSTRAINT FK_ARTICLE_OPTION_VALUE_OPTION_ID_ARTICLE_OPTION_ID FOREIGN KEY (`option_id`) REFERENCES `article_option`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_option_value_title` (
    `value_id` INTEGER UNSIGNED NOT NULL,
    `language_id` INTEGER UNSIGNED NOT NULL,
    `title` VARCHAR(255) DEFAULT '',
    PRIMARY KEY (`value_id`,`language_id`),
    INDEX IDX_ARTICLE_OPTION_VALUE_TITLE_LANGUAGE_ID (`language_id`),
    CONSTRAINT FK_ARTICLE_OPTION_VALUE_TITLE_OPTION_ID_ARTICLE_VALUE_ID FOREIGN KEY (`value_id`) REFERENCES `article_option_value`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_OPTION_VALUE_TITLE_OPTION_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_link` (
    `article_id` INTEGER UNSIGNED NOT NULL,
    `linked_article_id` INTEGER UNSIGNED NOT NULL,
    `type` CHAR(1) NOT NULL,
    `sort_order` INTEGER DEFAULT 0,
    PRIMARY KEY (`article_id`,`linked_article_id`,`type`),
    INDEX IDX_ARTICLE_LINK_LINKED_ARTICLE_ID (`linked_article_id`),
    INDEX IDX_ARTICLE_LINK_SORT_ORDER (`sort_order`),
    CONSTRAINT FK_ARTICLE_LINK_ARTICLE_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`article_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_LINK_LINKED_ARTICLE_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`linked_article_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE IF NOT EXISTS `article_in_category` (
    `article_id` INTEGER UNSIGNED NOT NULL,
    `category_id` INTEGER UNSIGNED NOT NULL,
    `sort_order` INTEGER DEFAULT 0,
    PRIMARY KEY (`article_id`,`category_id`),
    INDEX IDX_ARTICLE_IN_CATEGORY_CATEGORY_ID (`category_id`),
    INDEX IDX_ARTICLE_IN_CATEGORY_SORT_ORDER (`sort_order`),
    CONSTRAINT FK_ARTICLE_IN_CATEGORY_ARTICLE_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`article_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_IN_CATEGORY_ARTICLE_ID_ART_CATEGORY_ENTITY_ID FOREIGN KEY (`category_id`) REFERENCES `art_category_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_in_store` (
    `article_id` INTEGER UNSIGNED NOT NULL,
    `store_id` INTEGER UNSIGNED NOT NULL,
    PRIMARY KEY (`article_id`,`store_id`),
    INDEX IDX_ARTICLE_IN_STORE_STORE_ID (`store_id`),
    CONSTRAINT FK_ARTICLE_IN_STORE_ARTICLE_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`article_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_IN_STORE_STORE_ID_CORE_STORE_ID FOREIGN KEY (`store_id`) REFERENCES `core_store`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `article_review` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `article_id` INTEGER UNSIGNED NOT NULL,
    `customer_id` INTEGER UNSIGNED NULL DEFAULT NULL,
    `language_id` INTEGER UNSIGNED NULL DEFAULT NULL,
    `subject` VARCHAR(255) NULL DEFAULT NULL,
    `content` BLOB,
    `reply` BLOB,
    `images` TEXT,
    `anonymous` BOOLEAN DEFAULT 0,
    `status` BOOLEAN DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX IDX_ARTICLE_REVIEW_ARTICLE_ID (`article_id`),
    INDEX IDX_ARTICLE_REVIEW_CUSTOMER_ID (`customer_id`),
    INDEX IDX_ARTICLE_REVIEW_LANGUAGE_ID (`language_id`),
    INDEX IDX_ARTICLE_REVIEW_STATUS (`status`),
    CONSTRAINT FK_ARTICLE_REVIEW_ARTICLE_ID_ARTICLE_ENTITY_ID FOREIGN KEY (`article_id`) REFERENCES `article_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_REVIEW_CUSTOMER_ID_CUSTOMER_ENTITY_ID FOREIGN KEY (`customer_id`) REFERENCES `customer_entity`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FK_ARTICLE_REVIEW_LANGUAGE_ID_CORE_LANGUAGE_ID FOREIGN KEY (`language_id`) REFERENCES `core_language`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `eav_entity_type` VALUES (6, 'article', 'article_entity', 'article_value', 0, CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_set` VALUES (NULL, 6, 'Default', CURRENT_TIMESTAMP, NULL);
INSERT INTO `eav_attribute_group` VALUES (NULL, 6, 'Article Infomation', 0, CURRENT_TIMESTAMP, NULL),
(NULL, 6, 'Meta Infomation', 0, CURRENT_TIMESTAMP, NULL),
(NULL, 6, 'Images', 1, CURRENT_TIMESTAMP, NULL);

INSERT INTO `eav_attribute` VALUES 
(112,6,'name','varchar','text','',1,'',0,NULL,NULL,1,1,0,1,NULL,NULL),
(113,6,'uri_key','varchar','text','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(114,6,'description','text','wysiwyg','',1,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(115,6,'short_description','text','wysiwyg','',1,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(116,6,'sku','varchar','text','',1,'',0,NULL,NULL,1,1,0,1,NULL,NULL),
(117,6,'new_start','datetime','date','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(118,6,'new_end','datetime','date','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(119,6,'meta_title','varchar','text','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(120,6,'meta_description','text','textarea','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(121,6,'meta_keywords','text','textarea','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(122,6,'images','text','hidden','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(123,6,'default_image','int','hidden','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(124,6,'thumbnail','int','hidden','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL),
(125,6,'additional','text','hidden','',0,'',0,NULL,NULL,0,0,0,0,NULL,NULL);
INSERT INTO `eav_entity_attribute` VALUES 
(13, 22, 112, 0),
(13, 22, 113, 0),
(13, 22, 114, 0),
(13, 22, 115, 0),
(13, 22, 116, 0),
(13, 22, 117, 0),
(13, 22, 118, 0),
(13, 23, 119, 0),
(13, 23, 120, 0),
(13, 23, 121, 0),
(13, 24, 122, 0),
(13, 24, 123, 0),
(13, 24, 124, 0),
(13, 24, 125, 0);
INSERT INTO `eav_attribute_label` VALUES
(112, 1, 'Name'),
(113, 1, 'Uri Key'),
(114, 1, 'Description'),
(115, 1, 'Short Description'),
(116, 1, 'SKU'),
(117, 1, 'Set Product as New from Date'),
(118, 1, 'Set Product as New to Date'),
(119, 1, 'Meta Title'),
(120, 1, 'Meta Description'),
(121, 1, 'Meta Keywords'),
(122, 1, 'Images'),
(123, 1, 'Default Image'),
(124, 1, 'Thumbnail'),
(125, 1, 'Additional');