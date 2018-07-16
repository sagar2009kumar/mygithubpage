<?php
$installer = $this;  //Getting Installer Class Object In A Variable
$installer->startSetup();
$installer->run("
DROP TABLE IF EXISTS {$this->getTable('mofluid_chatsystem/msgadmin')};
DROP TABLE IF EXISTS {$this->getTable('mofluid_chatsystem/msgjson')};
DROP TABLE IF EXISTS {$this->getTable('mofluid_chatsystem/msgtext')};

CREATE TABLE IF NOT EXISTS {$this->getTable('mofluid_chatsystem/msgadmin')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11) unsigned NOT NULL, 
  `customer_name` varchar(255) default '',
  `request_id` int(11) default 0,
  `message` varchar(1000) default '',
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  `action` varchar(255) NOT NULL default '',
  `message_count` int(11) unsigned NOT NULL default 0,
  `sender` varchar(255) default '',
  `receiver` varchar(255) default '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$this->getTable('mofluid_chatsystem/msgjson')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11) unsigned NOT NULL, 
  `request_id` int(11) default 0,
  `message` nvarchar(4000) default '',
  `sender` varchar(255) default '',
  `receiver` varchar(255) default '',
  `time` datetime default NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS {$this->getTable('mofluid_chatsystem/msgtext')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11) unsigned NOT NULL, 
  `request_id` int(11) default 0,
  `message` varchar(4000) default '',
  `sender` varchar(255) default '',
  `receiver` varchar(255) default '',
  `time` datetime default NULL,
  `image` varchar(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");
$installer->endSetup();
?>
