2016年12月23日15:22:36
ALTER TABLE `log_visitor` ADD CONSTRAINT UNQ_LOG_VISITOR_SESSION_ID_PRODUCT_ID UNIQUE (`session_id`,`product_id`);

2016年12月28日09:03:31
DROP TABLE `api_rest_attribute`;
CREATE TABLE IF NOT EXISTS `api_rest_attribute` (
    `role_id` INTEGER NOT NULL,
    `resource` VARCHAR(50) NOT NULL,
    `operation` BOOLEAN DEFAULT 0,
    `attributes` TEXT,
    PRIMARY KEY (`role_id`,`resource`,`operation`),
    CONSTRAINT FK_API_REST_ATTR_API_REST_ROLE FOREIGN KEY (`role_id`) REFERENCES `api_rest_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

2017年1月10日10:51:12
ALTER TABLE `api_rest_role` ADD `validation` TINYINT DEFAULT 1;
UPDATE `api_rest_role` SET `validation`=-1 WHERE `name`='Admin';
UPDATE `api_rest_role` SET `validation`=0 WHERE `name`='Anonymous';
UPDATE `api_rest_role` SET `validation`=1 WHERE `name`='Customer';
DROP TABLE `api_soap_user`;
CREATE TABLE IF NOT EXISTS `api_soap_user` (
    `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` INTEGER UNSIGNED NOT NULL,
    `email` VARCHAR(255) DEFAULT '',
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `public_key` TEXT,
    `private_key` TEXT,
    `phrase` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX IDX_API_SOAP_USER_ROLE_ID (`role_id`),
    CONSTRAINT UNQ_API_SOAP_USER_USERNAME UNIQUE (`username`),
    CONSTRAINT FK_API_SOAP_USER_ROLE_ID_API_SOAP_ROLE_ID FOREIGN KEY (`role_id`) REFERENCES `api_soap_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE IF NOT EXISTS `api_soap_session` (
    `session_id` VARCHAR(40) NOT NULL,
    `user_id` INTEGER UNSIGNED NOT NULL,
    `log_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`session_id`),
    INDEX `IDX_API_SOAP_SESSION_USER_ID` (`user_id`),
    CONSTRAINT FK_API_SOAP_SESSION_USER_ID_API_SOAP_USER_ID FOREIGN KEY (`user_id`) REFERENCES `api_soap_user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);
