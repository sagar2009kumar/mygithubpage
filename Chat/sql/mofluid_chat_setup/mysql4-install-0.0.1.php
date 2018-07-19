<?php
$installer = $this;  //Getting Installer Class Object In A Variable
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('mofluid_chat/adminchat')};
DROP TABLE IF EXISTS {$this->getTable('mofluid_chat/totalcounter')};

CREATE TABLE IF NOT EXISTS {$this->getTable('mofluid_chat/adminchat')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11) unsigned NOT NULL, 
  `customer_name` varchar(255) default '',
  `request_id` int(11) default 0,
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  `message_count` int(11) unsigned NOT NULL default 0,
  `message` longtext default '',
  `last_message` longtext default'',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mofluid_chat/totalcounter')} (
  `customer_id` int(11) unsigned NOT NULL, 
  `request_id` int(11) default 0,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$installer->endSetup();
?>
