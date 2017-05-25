-- run this file if you are upgrading an existing installation to the i18n version.
ALTER TABLE pa2_module ADD COLUMN `module_lang` varchar(10) NOT NULL DEFAULT 'en_US.UTF8';
