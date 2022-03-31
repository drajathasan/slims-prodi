<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-03-31 20:03:58
 * @modify date 2022-03-31 22:51:19
 * @license GPLv3
 * @desc [description]
 */

use SLiMS\DB;

class CreateTable extends \SLiMS\Migration\Migration
{
    public function up()
    {
        \SLiMS\DB::getInstance()->query("CREATE TABLE IF NOT EXISTS `daftar_fakultas_prodi` (
                `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `code` varchar(30) NOT NULL,
                `label` varchar(50) NOT NULL,
                `description` text DEFAULT NULL,
                `parent` int NOT NULL DEFAULT '0',
                `created_at` datetime NULL,
                `updated_at` datetime NULL
            ) ENGINE='MyISAM';
        ");

        \SLiMS\DB::getInstance()->query("ALTER TABLE `member_custom`ADD `prodiid` int NOT NULL DEFAULT '0';");
    }

    public function down()
    {
        
    }
}