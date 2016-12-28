2016年12月23日15:22:36
ALTER TABLE `log_visitor` ADD CONSTRAINT UNQ_LOG_VISITOR_SESSION_ID_PRODUCT_ID UNIQUE (`session_id`,`product_id`);

2016年12月28日09:03:31
UPDATE `api_rest_role` SET `id`=0 WHERE `name`='Anonymous';
UPDATE `api_rest_role` SET `id`=1 WHERE `name`='Customer';
ALTER TABLE `api_rest_role` AUTO_INCREMENT=2;
DROP TABLE `api_rest_attribute`;
CREATE TABLE IF NOT EXISTS `api_rest_attribute` (
    `role_id` INTEGER NOT NULL,
    `resource` VARCHAR(50) NOT NULL,
    `operation` BOOLEAN DEFAULT 0,
    `attributes` TEXT,
    PRIMARY KEY (`role_id`,`resource`,`operation`),
    CONSTRAINT FK_API_REST_ATTR_API_REST_ROLE FOREIGN KEY (`role_id`) REFERENCES `api_rest_role`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);
