<?php
/**
 * This file is part of the Flagbit_MEP project.
 *
 * Flagbit_MEP is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This script is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * PHP version 5
 *
 * @category Flagbit_MEP
 * @package Flagbit_MEP
 * @author Pierre Bernard <pierre.bernard@flagbit.de>
 * @copyright 2012 Flagbit GmbH & Co. KG (http://www.flagbit.de). All rights served.
 * @license http://opensource.org/licenses/gpl-3.0 GNU General Public License, version 3 (GPLv3)
 */
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE `mep_template_version` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `template_version` varchar(6) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `template_header` text CHARACTER SET utf8,
  `template_footer` text CHARACTER SET utf8,
  `template_content` text CHARACTER SET utf8 NOT NULL,
  `template_profile_id` int(10) unsigned NOT NULL,
  `template_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `template_profile_id` (`template_profile_id`),
  CONSTRAINT `mep_template_version_ibfk_1` FOREIGN KEY (`template_profile_id`) REFERENCES `mep_profile` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");

$installer->run("
ALTER TABLE `mep_profile` ADD COLUMN `template_id` INT(11)
 UNSIGNED
 NOT NULL;
");

/** @var Flagbit_MEP_Model_Mysql4_Profile_Collection $profileCollection */
$profileCollection = Mage::getModel('mep/profile')->getCollection();

foreach ($profileCollection as $profile) {
    /** @var Flagbit_MEP_Model_Profile $profile */
    $data = array(
        'template_version' => 1,
        'template_header' => $profile->getTwigHeaderTemplate(),
        'template_footer' => $profile->getTwigFooterTemplate(),
        'template_content' => $profile->getTwigContentTemplate(),
        'template_profile_id' => $profile->getId(),
        'template_date' => time(),
    );
    /** @var Flagbit_MEP_Model_Template $newTemplate */
    $newTemplate = Mage::getModel('mep/template')->setData($data);
    $newTemplate->save();
    $profile->getResource()->saveField('template_id', $newTemplate->getId(), $profile->getId());
}

$installer->run("
ALTER TABLE `mep_profile` DROP `twig_content_template`;
ALTER TABLE `mep_profile` DROP `twig_header_template`;
ALTER TABLE `mep_profile` DROP `twig_footer_template`;
");

$installer->endSetup();