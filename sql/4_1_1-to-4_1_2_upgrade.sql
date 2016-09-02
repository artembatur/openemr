--
--  Comment Meta Language Constructs:
--
--  #IfNotTable
--    argument: table_name
--    behavior: if the table_name does not exist,  the block will be executed

--  #IfTable
--    argument: table_name
--    behavior: if the table_name does exist, the block will be executed

--  #IfMissingColumn
--    arguments: table_name colname
--    behavior:  if the table exists but the column does not,  the block will be executed

--  #IfNotColumnType
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a column colname with a data type equal to value, then the block will be executed

--  #IfNotRow
--    arguments: table_name colname value
--    behavior:  If the table table_name does not have a row where colname = value, the block will be executed.

--  #IfNotRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfNotRow3D
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3, the block will be executed.

--  #IfNotRow4D
--    arguments: table_name colname value colname2 value2 colname3 value3 colname4 value4
--    behavior:  If the table table_name does not have a row where colname = value AND colname2 = value2 AND colname3 = value3 AND colname4 = value4, the block will be executed.

--  #IfNotRow2Dx2
--    desc:      This is a very specialized function to allow adding items to the list_options table to avoid both redundant option_id and title in each element.
--    arguments: table_name colname value colname2 value2 colname3 value3
--    behavior:  The block will be executed if both statements below are true:
--               1) The table table_name does not have a row where colname = value AND colname2 = value2.
--               2) The table table_name does not have a row where colname = value AND colname3 = value3.

--  #IfRow2D
--    arguments: table_name colname value colname2 value2
--    behavior:  If the table table_name does have a row where colname = value AND colname2 = value2, the block will be executed.

--  #IfIndex
--    desc:      This function is most often used for dropping of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the table and index exist the relevant statements are executed, otherwise not.

--  #IfNotIndex
--    desc:      This function will allow adding of indexes/keys.
--    arguments: table_name colname
--    behavior:  If the index does not exist, it will be created

--  #IfNotMigrateClickOptions
--    Custom function for the importing of the Clickoptions settings (if exist) from the codebase into the database

--  #EndIf
--    all blocks are terminated with a #EndIf statement.

#IfNotTable report_results
CREATE TABLE `report_results` (
  `report_id` bigint(20) NOT NULL,
  `field_id` varchar(31) NOT NULL default '',
  `field_value` text,
  PRIMARY KEY (`report_id`,`field_id`)
) ENGINE=MyISAM;
#EndIf

#IfMissingColumn version v_acl
ALTER TABLE `version` ADD COLUMN `v_acl` int(11) NOT NULL DEFAULT 0;
#EndIf

#IfMissingColumn documents_legal_detail dld_moved
ALTER TABLE `documents_legal_detail` ADD COLUMN `dld_moved` tinyint(4) NOT NULL DEFAULT '0'; 
#EndIf

#IfMissingColumn documents_legal_detail dld_patient_comments
ALTER TABLE `documents_legal_detail` ADD COLUMN `dld_patient_comments` text COMMENT 'Patient comments stored here';
#EndIf

#IfMissingColumn documents_legal_master dlm_upload_type
ALTER TABLE `documents_legal_master` ADD COLUMN `dlm_upload_type` tinyint(4) DEFAULT '0' COMMENT '0-Provider Uploaded,1-Patient Uploaded';
#EndIf

#IfMissingColumn list_options codes
ALTER TABLE `list_options` ADD COLUMN `codes` varchar(255) NOT NULL DEFAULT '';
UPDATE list_options SET `codes`='SNOMED-CT:449868002' WHERE list_id='smoking_status' AND option_id='1' AND title='Current every day smoker';
UPDATE list_options SET `codes`='SNOMED-CT:428041000124106' WHERE list_id='smoking_status' AND option_id='2' AND title='Current some day smoker';
UPDATE list_options SET `codes`='SNOMED-CT:8517006' WHERE list_id='smoking_status' AND option_id='3' AND title='Former smoker';
UPDATE list_options SET `codes`='SNOMED-CT:266919005' WHERE list_id='smoking_status' AND option_id='4' AND title='Never smoker';
UPDATE list_options SET `codes`='SNOMED-CT:77176002' WHERE list_id='smoking_status' AND option_id='5' AND title='Smoker, current status unknown';
UPDATE list_options SET `codes`='SNOMED-CT:266927001' WHERE list_id='smoking_status' AND option_id='9' AND title='Unknown if ever smoked';
#EndIf

#IfNotRow2Dx2 list_options list_id smoking_status option_id 15 title Heavy tobacco smoker
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, codes ) VALUES ('smoking_status', '15', 'Heavy tobacco smoker', 70, 0, "SNOMED-CT:428071000124103");
#EndIf

#IfNotRow2Dx2 list_options list_id smoking_status option_id 16 title Light tobacco smoker
INSERT INTO list_options ( list_id, option_id, title, seq, is_default, codes ) VALUES ('smoking_status', '16', 'Light tobacco smoker', 80, 0, "SNOMED-CT:428061000124105");
#EndIf

#IfMissingColumn code_types ct_term
ALTER TABLE `code_types` ADD COLUMN ct_term tinyint(1) NOT NULL default 0 COMMENT '1 if this is a clinical term';
#EndIf

#IfNotRow code_types ct_key SNOMED-CT
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (
  `id` int(11) NOT NULL DEFAULT '0',
  `seq` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM ;
INSERT INTO `temp_table_one` (`id`, `seq`) VALUES ( IF( ((SELECT MAX(`ct_id`) FROM `code_types`)>=100), ((SELECT MAX(`ct_id`) FROM `code_types`) + 1), 100 ) , IF( ((SELECT MAX(`ct_seq`) FROM `code_types`)>=100), ((SELECT MAX(`ct_seq`) FROM `code_types`) + 1), 100 )  );
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external, ct_claim, ct_proc, ct_term ) VALUES ('SNOMED-CT' , (SELECT MAX(`id`) FROM `temp_table_one`), (SELECT MAX(`seq`) FROM `temp_table_one`), 0, '', 0, 0, 1, 0, 0, 'SNOMED Clinical Term', 7, 0, 0, 1);
DROP TABLE `temp_table_one`;
#EndIf

#IfNotColumnType codes code varchar(25)
ALTER TABLE `codes` CHANGE `code` `code` varchar(25) NOT NULL default '';
#EndIf

#IfNotColumnType billing code varchar(20)
ALTER TABLE `billing` CHANGE `code` `code` varchar(20) default NULL;
#EndIf

#IfNotColumnType ar_activity code varchar(20)
ALTER TABLE `ar_activity` CHANGE `code` `code` varchar(20) NOT NULL COMMENT 'empty means claim level';
#EndIf

#IfNotTable procedure_questions
CREATE TABLE `procedure_questions` (
  `lab_id`              bigint(20)   NOT NULL DEFAULT 0   COMMENT 'references users.id to identify the lab',
  `procedure_code`      varchar(31)  NOT NULL DEFAULT ''  COMMENT 'references procedure_type.procedure_code to identify this order type',
  `question_code`       varchar(31)  NOT NULL DEFAULT ''  COMMENT 'code identifying this question',
  `seq`                 int(11)      NOT NULL default 0   COMMENT 'sequence number for ordering',
  `question_text`       varchar(255) NOT NULL DEFAULT ''  COMMENT 'descriptive text for question_code',
  `required`            tinyint(1)   NOT NULL DEFAULT 0   COMMENT '1 = required, 0 = not',
  `maxsize`             int          NOT NULL DEFAULT 0   COMMENT 'maximum length if text input field',
  `fldtype`             char(1)      NOT NULL DEFAULT 'T' COMMENT 'Text, Number, Select, Multiselect, Date, Gestational-age',
  `options`             text         NOT NULL DEFAULT ''  COMMENT 'choices for fldtype S and T',
  `activity`            tinyint(1)   NOT NULL DEFAULT 1   COMMENT '1 = active, 0 = inactive',
  PRIMARY KEY (`lab_id`, `procedure_code`, `question_code`)
) ENGINE=MyISAM;
#EndIf

#IfMissingColumn procedure_type activity
ALTER TABLE `procedure_type` ADD COLUMN `activity` tinyint(1) NOT NULL default 1;
#EndIf

#IfMissingColumn procedure_type notes
ALTER TABLE `procedure_type` ADD COLUMN `notes` varchar(255) NOT NULL default '';
#EndIf

#IfNotTable procedure_answers
CREATE TABLE `procedure_answers` (
  `procedure_order_id`  bigint(20)   NOT NULL DEFAULT 0  COMMENT 'references procedure_order.procedure_order_id',
  `procedure_order_seq` int(11)      NOT NULL DEFAULT 1  COMMENT 'references procedure_order_code.seq',
  `question_code`       varchar(31)  NOT NULL DEFAULT '' COMMENT 'references procedure_questions.question_code',
  `answer_seq`          int(11)      NOT NULL AUTO_INCREMENT COMMENT 'supports multiple-choice questions',
  `answer`              varchar(255) NOT NULL DEFAULT '' COMMENT 'answer data',
  PRIMARY KEY (`procedure_order_id`, `procedure_order_seq`, `question_code`, `answer_seq`)
) ENGINE=MyISAM;
#EndIf

#IfNotTable procedure_providers
CREATE TABLE `procedure_providers` (
  `ppid`         bigint(20)   NOT NULL auto_increment,
  `name`         varchar(255) NOT NULL DEFAULT '',
  `npi`          varchar(15)  NOT NULL DEFAULT '',
  `protocol`     varchar(15)  NOT NULL DEFAULT 'DL',
  `login`        varchar(255) NOT NULL DEFAULT '',
  `password`     varchar(255) NOT NULL DEFAULT '',
  `orders_path`  varchar(255) NOT NULL DEFAULT '',
  `results_path` varchar(255) NOT NULL DEFAULT '',
  `notes`        text         NOT NULL DEFAULT '',
  PRIMARY KEY (`ppid`)
) ENGINE=MyISAM;
#EndIf

#IfNotTable procedure_order_code
CREATE TABLE `procedure_order_code` (
  `procedure_order_id`  bigint(20)  NOT NULL,
  `procedure_order_seq` int(11)     NOT NULL AUTO_INCREMENT COMMENT 'supports multiple tests per order',
  `procedure_type_id`   bigint(20)  NOT NULL                COMMENT 'references procedure_type.procedure_type_id',
  `procedure_code`      varchar(31) NOT NULL DEFAULT ''     COMMENT 'copy of procedure_type.procedure_code',
  PRIMARY KEY (`procedure_order_id`, `procedure_order_seq`)
) ENGINE=MyISAM;
INSERT INTO procedure_order_code
  SELECT po.procedure_order_id, 1, po.procedure_type_id, pt.procedure_code
  FROM procedure_order AS po
  LEFT JOIN procedure_type AS pt ON pt.procedure_type_id = po.procedure_type_id;
ALTER TABLE `procedure_order`
  DROP COLUMN `procedure_type_id`;
#EndIf

#IfMissingColumn procedure_order lab_id
ALTER TABLE `procedure_order`
  ADD COLUMN `lab_id`            bigint(20)   NOT NULL DEFAULT 0  COMMENT 'references procedure_providers.ppid',
  ADD COLUMN `specimen_type`     varchar(31)  NOT NULL DEFAULT '' COMMENT 'from the Specimen_Type list',
  ADD COLUMN `specimen_location` varchar(31)  NOT NULL DEFAULT '' COMMENT 'from the Specimen_Location list',
  ADD COLUMN `specimen_volume`   varchar(30)  NOT NULL DEFAULT '' COMMENT 'from a text input field';
UPDATE procedure_order AS po, procedure_order_code AS pc, procedure_type AS pt
  SET po.lab_id = pt.lab_id WHERE
  po.lab_id = 0 AND
  pc.procedure_order_id = po.procedure_order_id AND
  pt.procedure_type_id = pc.procedure_type_id AND
  pt.lab_id != 0;
#EndIf

#IfMissingColumn procedure_report procedure_order_seq
ALTER TABLE procedure_report
  ADD COLUMN `procedure_order_seq` int(11) NOT NULL DEFAULT 1 COMMENT 'references procedure_order_code.procedure_order_seq';
#EndIf

#IfMissingColumn procedure_order diagnoses
ALTER TABLE `procedure_order`
  ADD COLUMN `diagnoses` text NOT NULL DEFAULT '' COMMENT 'diagnoses and maybe other coding (e.g. ICD9:111.11)';
#EndIf

#IfMissingColumn procedure_providers remote_host
ALTER TABLE `procedure_providers`
  ADD COLUMN `remote_host` varchar(255)  NOT NULL DEFAULT ''  COMMENT 'IP or hostname of remote server',
  ADD COLUMN `send_app_id` varchar(255)  NOT NULL DEFAULT ''  COMMENT 'Sending application ID (MSH-3.1)',
  ADD COLUMN `send_fac_id` varchar(255)  NOT NULL DEFAULT ''  COMMENT 'Sending facility ID (MSH-4.1)',
  ADD COLUMN `recv_app_id` varchar(255)  NOT NULL DEFAULT ''  COMMENT 'Receiving application ID (MSH-5.1)',
  ADD COLUMN `recv_fac_id` varchar(255)  NOT NULL DEFAULT ''  COMMENT 'Receiving facility ID (MSH-6.1)',
  ADD COLUMN `DorP`        char(1)       NOT NULL DEFAULT 'D' COMMENT 'Debugging or Production (MSH-11)';
#EndIf

#IfMissingColumn procedure_order_code procedure_source
ALTER TABLE `procedure_order_code`
  ADD COLUMN `procedure_source` char(1) NOT NULL DEFAULT '1' COMMENT '1=original order, 2=added after order sent';
#EndIf

#IfMissingColumn procedure_result result_code
ALTER TABLE `procedure_result`
  ADD COLUMN `result_data_type` char(1) NOT NULL DEFAULT 'S' COMMENT
  'N=Numeric, S=String, F=Formatted, E=External, L=Long text as first line of comments',
  ADD COLUMN `result_code` varchar(31) NOT NULL DEFAULT '' COMMENT
  'LOINC code, might match a procedure_type.procedure_code',
  ADD COLUMN `result_text` varchar(255) NOT NULL DEFAULT '' COMMENT
  'Description of result_code';
# This severs the link between procedure_result and procedure_type:
UPDATE procedure_result AS ps, procedure_type AS pt
  SET ps.result_code = pt.procedure_code, ps.result_text = pt.description
  WHERE pt.procedure_type_id = ps.procedure_type_id;
ALTER TABLE `procedure_result` DROP COLUMN procedure_type_id;
#EndIf

#IfMissingColumn procedure_questions tips
ALTER TABLE `procedure_questions`
  ADD COLUMN `tips` varchar(255) NOT NULL DEFAULT '' COMMENT 'Additional instructions for answering the question';
#EndIf

#IfMissingColumn procedure_order_code procedure_name
ALTER TABLE `procedure_order_code`
  ADD COLUMN `procedure_name` varchar(255) NOT NULL DEFAULT '' COMMENT
  'Descriptive name of procedure_code';
# This severs the link between procedure_order_code and procedure_type:
UPDATE procedure_order_code AS pc, procedure_order AS po, procedure_type AS pt
  SET pc.procedure_name = pt.name
  WHERE po.procedure_order_id = pc.procedure_order_id AND
  pt.lab_id = po.lab_id AND
  pt.procedure_code = pc.procedure_code;
ALTER TABLE `procedure_order_code` DROP COLUMN procedure_type_id;
#EndIf

#IfMissingColumn procedure_report report_notes
ALTER TABLE procedure_report
  ADD COLUMN `report_notes` text NOT NULL DEFAULT '' COMMENT 'Notes from the lab';
#EndIf

#IfNotRow code_types ct_key SNOMED-PR
DROP TABLE IF EXISTS `temp_table_one`;
CREATE TABLE `temp_table_one` (
  `id` int(11) NOT NULL DEFAULT '0',
  `seq` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM ;
INSERT INTO `temp_table_one` (`id`, `seq`) VALUES ( IF( ((SELECT MAX(`ct_id`) FROM `code_types`)>=100), ((SELECT MAX(`ct_id`) FROM `code_types`) + 1), 100 ) , IF( ((SELECT MAX(`ct_seq`) FROM `code_types`)>=100), ((SELECT MAX(`ct_seq`) FROM `code_types`) + 1), 100 )  );
INSERT INTO code_types (ct_key, ct_id, ct_seq, ct_mod, ct_just, ct_fee, ct_rel, ct_nofs, ct_diag, ct_active, ct_label, ct_external, ct_claim, ct_proc, ct_term ) VALUES ('SNOMED-PR' , (SELECT MAX(`id`) FROM `temp_table_one`), (SELECT MAX(`seq`) FROM `temp_table_one`), 0, 'SNOMED', 1, 0, 0, 0, 0, 'SNOMED Procedure', 9, 1, 1, 0);
DROP TABLE `temp_table_one`;
#EndIf

#IfNotTable background_services 
CREATE TABLE IF NOT EXISTS `background_services` (
  `name` varchar(31) NOT NULL,
  `title` varchar(127) NOT NULL COMMENT 'name for reports',
  `active` tinyint(1) NOT NULL default '0',
  `running` tinyint(1) NOT NULL default '-1',
  `next_run` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `execute_interval` int(11) NOT NULL default '0' COMMENT 'minimum number of minutes between function calls,0=manual mode',
  `function` varchar(127) NOT NULL COMMENT 'name of background service function',
  `require_once` varchar(255) default NULL COMMENT 'include file (if necessary)',
  `sort_order` int(11) NOT NULL default '100' COMMENT 'lower numbers will be run first',
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM;
#EndIf

#IfNotRow background_services name phimail
INSERT INTO `background_services` (`name`, `title`, `execute_interval`, `function`, `require_once`, `sort_order`) VALUES
('phimail', 'phiMail Direct Messaging Service', 5, 'phimail_check', '/library/direct_message_check.inc', 100);
#EndIf

#IfNotRow users username phimail-service
INSERT INTO `users` (username,password,lname,authorized,active) 
  VALUES ('phimail-service','NoLogin','phiMail Gateway',0,0);
#EndIf

#IfNotRow users username portal-user
INSERT INTO `users` (username,password,lname,authorized,active) 
  VALUES ('portal-user','NoLogin','Patient Portal User',0,0);
#EndIf

#IfNotTable direct_message_log
CREATE TABLE IF NOT EXISTS `direct_message_log` (
  `id` bigint(20) NOT NULL auto_increment,
  `msg_type` char(1) NOT NULL COMMENT 'S=sent,R=received',
  `msg_id` varchar(127) NOT NULL,
  `sender` varchar(255) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `create_ts` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `status` char(1) NOT NULL COMMENT 'Q=queued,D=dispatched,R=received,F=failed',
  `status_info` varchar(511) default NULL,
  `status_ts` timestamp NULL default NULL,
  `patient_id` bigint(20) default NULL,
  `user_id` bigint(20) default NULL,
  PRIMARY KEY  (`id`),
  KEY `msg_id` (`msg_id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=MyISAM;
#EndIf

#IfMissingColumn procedure_order_code diagnoses
ALTER TABLE `procedure_order_code`
  ADD COLUMN `diagnoses` text NOT NULL DEFAULT '' COMMENT
  'diagnoses and maybe other coding (e.g. ICD9:111.11)';
UPDATE procedure_order_code AS pc, procedure_order AS po
  SET pc.diagnoses = po.diagnoses
  WHERE po.procedure_order_id = pc.procedure_order_id;
#EndIf

# At this point this obsolete column will always exist, because it was created
# and then moved to another table during this release cycle.
ALTER TABLE `procedure_order` DROP COLUMN diagnoses;

#IfMissingColumn lists modifydate
ALTER TABLE `lists` ADD COLUMN `modifydate` timestamp NOT NULL default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
#EndIf

#IfMissingColumn code_types ct_problem
ALTER TABLE `code_types` ADD COLUMN `ct_problem` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 if this code type is used as a medical problem';
UPDATE code_types SET ct_problem = 1 WHERE ct_key='ICD9';
UPDATE code_types SET ct_problem = 1 WHERE ct_key='DSMIV';
UPDATE code_types SET ct_problem = 1 WHERE ct_key='ICD10';
UPDATE code_types SET ct_problem = 1 WHERE ct_key='SNOMED';
#EndIf

#IfMissingColumn procedure_order date_transmitted
ALTER TABLE `procedure_order`
  ADD COLUMN `date_transmitted` datetime DEFAULT NULL COMMENT
  'time of order transmission, null if unsent';
UPDATE procedure_order SET date_transmitted = date_ordered WHERE
  date_transmitted IS NULL AND date_ordered IS NOT NULL;
#EndIf

#IfNotRow2D list_options list_id lists option_id issue_types
INSERT INTO list_options (`list_id`,`option_id`,`title`) VALUES ('lists','issue_types','Issue Types');
#EndIf

#IfNotMigrateClickOptions
#EndIf

#IfNotTable issue_types
CREATE TABLE `issue_types` (
  `category` varchar(75) NOT NULL DEFAULT '',
  `type` varchar(75) NOT NULL DEFAULT '',
  `plural` varchar(75) NOT NULL DEFAULT '',
  `singular` varchar(75) NOT NULL DEFAULT '',
  `abbreviation` varchar(75) NOT NULL DEFAULT '',
  `style` smallint(6) NOT NULL DEFAULT '0',
  `force_show` smallint(6) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category`,`type`)
) ENGINE=MyISAM;
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('10','default','medical_problem','Medical Problems','Problem','P','0','1');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('30','default','medication','Medications','Medication','M','0','1');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('20','default','allergy','Allergies','Allergy','A','0','1');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('40','default','surgery','Surgeries','Surgery','S','0','0');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('50','default','dental','Dental Issues','Dental','D','0','0');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('10','athletic_team','football_injury','Football Injuries','Injury','I','2','1');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('20','athletic_team','medical_problem','Medical Problems','Medical','P','0','0');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('30','athletic_team','allergy','Allergies','Allergy','A','1','0');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('40','athletic_team','general','General','General','G','1','0');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('10','ippf_specific','medical_problem','Medical Problems','Problem','P','0','1');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('30','ippf_specific','medication','Medications','Medication','M','0','1');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('20','ippf_specific','allergy','Allergies','Allergy','Y','0','1');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('40','ippf_specific','surgery','Surgeries','Surgery','S','0','0');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('50','ippf_specific','ippf_gcac','Abortions','Abortion','A','3','0');
INSERT INTO issue_types(`ordering`,`category`,`type`,`plural`,`singular`,`abbreviation`,`style`,`force_show`) VALUES ('60','ippf_specific','contraceptive','Contraception','Contraception','C','4','0');
#EndIf

#IfMissingColumn issue_types active
ALTER TABLE `issue_types` ADD COLUMN `active` tinyint(1) NOT NULL DEFAULT '1';
#EndIf

#IfNotColumnType immunizations administered_date datetime
ALTER TABLE `immunizations`
  MODIFY COLUMN administered_date datetime DEFAULT NULL;
#EndIf

#IfMissingColumn immunizations amount_administered
ALTER TABLE `immunizations`
  ADD COLUMN `amount_administered` int(11) DEFAULT NULL;
#EndIf

#IfMissingColumn immunizations amount_administered_unit
ALTER TABLE `immunizations`
  ADD COLUMN `amount_administered_unit` varchar(50) DEFAULT NULL;
#EndIf

#IfMissingColumn immunizations expiration_date
ALTER TABLE `immunizations`
  ADD COLUMN `expiration_date` date DEFAULT NULL;
#EndIf

#IfMissingColumn immunizations route
ALTER TABLE `immunizations`
  ADD COLUMN `route` varchar(100) DEFAULT NULL;
#EndIf

#IfMissingColumn immunizations administration_site
ALTER TABLE `immunizations`
  ADD COLUMN `administration_site` varchar(100) DEFAULT NULL;
#EndIf

#IfMissingColumn immunizations added_erroneously
ALTER TABLE `immunizations`
  ADD COLUMN `added_erroneously` tinyint(1) NOT NULL DEFAULT '0';
#EndIf

#IfMissingColumn immunizations historical
ALTER TABLE `immunizations`
ADD COLUMN `historical` tinyint(2) NOT NULL DEFAULT '00';
#EndIf

#IfMissingColumn immunizations vfc
ALTER TABLE `immunizations`
ADD COLUMN `vfc` varchar(100) not null DEFAULT 'V01';
#EndIf

#IfMissingColumn immunizations submitted
ALTER TABLE `immunizations`
ADD COLUMN `submitted` varchar(1) not null DEFAULT '0' COMMENT '0 = not submitted, 1 = submitted and passed, F = submitted and failed';
#EndIf

#IfMissingColumn documents path_depth
ALTER TABLE `documents` ADD COLUMN `path_depth` TINYINT DEFAULT '1' COMMENT 'Depth of path to use in url to find document. Not applicable for CouchDB.';
#Endif

#IfNotTable users_secure
CREATE TABLE `users_secure` (
  `id` bigint(20) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255),
  `salt` varchar(255),
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `password_history1` varchar(255),
  `salt_history1` varchar(255),
  `password_history2` varchar(255),
  `salt_history2` varchar(255),
  PRIMARY KEY (`id`),
  UNIQUE KEY `USERNAME_ID` (`id`,`username`)
) ENGINE=InnoDb; 
#EndIf

#IfMissingColumn patient_access_onsite portal_salt
ALTER TABLE `patient_access_onsite` ADD COLUMN `portal_salt` VARCHAR(100) NULL;
#Endif

#IfMissingColumn procedure_order clinical_hx
ALTER TABLE `procedure_order`
  ADD COLUMN `clinical_hx` varchar(255) DEFAULT '' COMMENT
  'clinical history text that may be relevant to the order';
#EndIf

#IfMissingColumn procedure_order_code do_not_send
ALTER TABLE `procedure_order_code`
  ADD COLUMN `do_not_send` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = normal, 1 = do not transmit to lab';
#EndIf

#IfNotTable misc_address_book
CREATE TABLE `misc_address_book` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `fname` varchar(255) DEFAULT NULL,
  `mname` varchar(255) DEFAULT NULL,
  `lname` varchar(255) DEFAULT NULL,
  `street` varchar(60) DEFAULT NULL,
  `city` varchar(30) DEFAULT NULL,
  `state` varchar(30) DEFAULT NULL,
  `zip` varchar(20) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
#EndIf

#IfMissingColumn documents imported
ALTER TABLE `documents` ADD COLUMN `imported` TINYINT DEFAULT 0 NULL COMMENT 'Parsing status for CCR/CCD/CCDA importing';
#EndIf

#IfNotTable erx_ttl_touch
CREATE TABLE `erx_ttl_touch` (
  `patient_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'Patient record Id',
  `process` ENUM('allergies','medications') NOT NULL COMMENT 'NewCrop eRx SOAP process',
  `updated` DATETIME NOT NULL COMMENT 'Date and time of last process update for patient',
  PRIMARY KEY (`patient_id`, `process`) )
ENGINE = InnoDB COMMENT = 'Store records last update per patient data process';
#EndIf


#IfNotTable rsa_pairs
CREATE TABLE `rsa_pairs` (
  `public` text NOT NULL,
  `private` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDb;
#EndIf

#IfMissingColumn patient_access_onsite portal_salt
ALTER TABLE `patient_access_onsite` ADD COLUMN `portal_salt` VARCHAR(100) NULL;
#Endif

#IfNotTable appointment_encounter
CREATE TABLE `appointment_encounter` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `eid` int NOT NULL,
  `encounter` int not null,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
#EndIf

#IfNotTable immunizations_schedules
CREATE TABLE IF NOT EXISTS `immunizations_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `age_max` int(11) DEFAULT NULL,
  `frequency` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Dumping data for table `immunizations_schedules`
--

INSERT INTO `immunizations_schedules` (`id`, `description`, `age`, `age_max`, `frequency`) VALUES
(1, 'Newborn', 0, NULL, NULL),
(2, '2 Months', 2, NULL, NULL),
(3, '4 Months', 4, NULL, NULL),
(4, '6 Months', 6, NULL, NULL),
(5, '9 Months', 9, NULL, NULL),
(6, '12 Months', 12, NULL, NULL),
(7, '15 Months', 15, NULL, NULL),
(8, '18 Months', 18, NULL, NULL),
(9, '4 Years', 48, NULL, NULL),
(10, '11 Years', 132, NULL, NULL),
(11, '15 Years', 160, NULL, NULL),
(12, 'Influenza', 36, 216, 'annual'),
(13, '3 Years', 36, NULL, NULL);
#EndIf


#ifnottable immunizations_schedules_codes
CREATE TABLE IF NOT EXISTS `immunizations_schedules_codes` (
  `id` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(45) DEFAULT NULL,
  `cvx_code` varchar(45) DEFAULT NULL,
  `proc_codes` varchar(45) DEFAULT NULL,
  `justify_codes` varchar(45) DEFAULT NULL,
  `default_site` varchar(45) DEFAULT NULL,
  `comments` varchar(45) DEFAULT NULL,
  `drug_route` varchar(2) DEFAULT 'TD',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `immunizations_schedules_codes`
--

INSERT INTO `immunizations_schedules_codes` (`id`, `description`, `manufacturer`, `cvx_code`, `proc_codes`, `justify_codes`, `default_site`, `comments`, `drug_route`) VALUES
(1, 'Hepatitis B', NULL, '8', 'CPT4:90744', 'ICD9:V05.9', 'RT', 'Newborn', 'IM'),
(2, 'Pentacel', 'PMC', '120', 'CPT4:90698', 'ICD9:V06.3;ICD9:V03.81', 'RT', NULL, 'IM'),
(3, 'Hepatitis B', NULL, '8', 'CPT4:90744', 'ICD9:V05.3', 'RT', NULL, 'IM'),
(4, 'Prevnar 13', 'WAL', '133', 'CPT4:90670', 'ICD9:V03.82', 'LT', NULL, 'IM'),
(5, 'Rotateq', 'MSD', '116', 'CPT4:90680', 'ICD9:V04.89', 'PO', NULL, 'PO'),
(6, 'DTaP', NULL, '20', 'CPT4:90700', 'ICD9:V06.1', 'RT', NULL, 'IM'),
(7, 'IPV', 'PMC', '10', 'CPT4:90713', 'ICD9:V04.0', 'RT', '(IPOL?)', 'TD'),
(8, 'Influenza(Preservative Free)', 'PMC', '140', 'CPT4:90655', 'ICD9:V04.81', 'LT', '(Fluzone?)(6 months)', 'IM'),
(9, 'HIB', NULL, '47', 'CPT4:90645', 'ICD9:V03.81', 'LT', '(HibTiter?/Wyeth)(LT 9 Mo)', 'IM'),
(10, 'MMR', NULL, '03', 'CPT4:90707', 'ICD9:V06.4', 'RA', '(Merck?)', 'IM'),
(11, 'Varicella', NULL, '21', 'CPT4:90716', 'ICD9:V05.4', 'LA', '(Merck?/Varivax?)', 'IM'),
(12, 'Hepatitis A', NULL, '83', 'CPT4:90633', 'ICD9:V05.9', 'RT', 'RT 12 Months', 'IM'),
(13, 'HIB', NULL, '47', 'CPT4:90645', 'ICD9:V03.81', 'RT', '(RT 15 months)', 'IM'),
(14, 'Hepatitis A', NULL, '83', 'CPT4:90633', 'ICD9:V05.9', 'RD', 'RD 18 Months', 'IM'),
(15, 'Kinrix', 'SKB', '130', 'CPT4:90696', 'ICD9:V06.3', 'RT', NULL, 'IM'),
(16, 'Menactra', 'PMC', '114', 'CPT4:90734', 'ICD9:V03.89', 'RD', NULL, 'IM'),
(17, 'Adacel-TDaP', 'PMC', '115', 'CPT4:90715', 'ICD9:V06.1', 'LD', NULL, 'IM'),
(18, 'Gardasil', 'MSD', '62', 'CPT4:90649', 'ICD9:V05.8', 'RD', NULL, 'IM'),
(19, 'Influenza', NULL, '141', 'CPT4:90658', 'ICD9:V04.81', 'RD', 'Annual Influenza Age 3-18', 'IM'),
(20, 'FluMist', 'MED', '149', 'CPT4:90660', 'ICD9:V04.81', 'NS', 'FluMist Age 3-18', 'IM'),
(21, 'Gardasil 9', NULL, '165', 'CPT4:90651', 'ICD9:V08.9', NULL, ' Generic: Human Papillomavirus 9-valent Vacci', 'IM');
#EndIf

#ifnotTableimmunizations_schedules_options

CREATE TABLE IF NOT EXISTS `immunizations_schedules_options` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `code_id` int(11) DEFAULT NULL,
  `seq` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `immunizations_schedules_options`
--

INSERT INTO `immunizations_schedules_options` (`id`, `schedule_id`, `code_id`, `seq`) VALUES
(0, 13, 20, 10),
(1, 1, 1, 10),
(2, 2, 2, 10),
(3, 2, 3, 20),
(4, 2, 4, 30),
(5, 2, 5, 40),
(6, 3, 2, 10),
(7, 3, 3, 20),
(8, 3, 4, 30),
(9, 3, 5, 40),
(10, 4, 6, 10),
(11, 4, 7, 20),
(12, 4, 4, 30),
(13, 4, 8, 40),
(14, 4, 5, 50),
(15, 4, 2, 60),
(16, 5, 3, 10),
(17, 5, 9, 20),
(18, 5, 8, 30),
(19, 6, 10, 10),
(20, 6, 11, 20),
(21, 6, 12, 30),
(22, 6, 8, 40),
(23, 7, 6, 10),
(24, 7, 13, 20),
(25, 7, 4, 30),
(26, 8, 14, 10),
(27, 9, 6, 10),
(28, 9, 7, 20),
(29, 9, 15, 30),
(30, 9, 11, 40),
(31, 9, 10, 50),
(32, 10, 16, 10),
(33, 10, 17, 20),
(34, 10, 18, 30),
(35, 11, 16, 10),
(36, 12, 19, 10),
(37, 2, 6, 50),
(38, 2, 7, 60),
(39, 2, 9, 70),
(40, 3, 6, 50),
(41, 3, 7, 60),
(42, 3, 9, 70),
(43, 4, 9, 70),
(44, 12, 20, 20),
(45, 9, 20, 60),
(46, 10, 20, 40),
(47, 11, 20, 20),
(48, 7, 8, 40),
(49, 8, 8, 20),
(50, 13, 8, 20),
(51, 13, 19, 30),
(52, 9, 19, 60),
(53, 10, 19, 50),
(54, 11, 19, 30),
(56, 10, 21, 12);
#EndIF