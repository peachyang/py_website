2016年12月23日15:22:36
ALTER TABLE `log_visitor` ADD CONSTRAINT UNQ_LOG_VISITOR_SESSION_ID_PRODUCT_ID UNIQUE (`session_id`,`product_id`);