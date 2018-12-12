<?php

$installer = $this; // Getting the installer class object in a variable 

$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('mofluid_mynotes/mynotes')};

CREATE TABLE IF NOT EXISTS {$this->getTable('mofluid_mynotes/mynotes')} (
`id` int(11) unsigned NOT NULL auto_increment,
`customer_id` int(11) unsigned NOT NULL,
`product_id` int(11) unsigned NOT NULL,
`note_description` longtext default '',
`created_at` datetime default CURRENT_TIMESTAMP,
PRIMARY KEY(`id`)
) ENGINE=InnoDB default CHARSET=utf8;

");

$installer->endSetup();
