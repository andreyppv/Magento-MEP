<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('mep_profile_attribute')}
ADD `use_twig_template` int(11) NOT NULL DEFAULT 0 AFTER `inheritance_type`,
ADD `twig_content_template` text NOT NULL AFTER `use_twig_template`;
");

$installer->endSetup();