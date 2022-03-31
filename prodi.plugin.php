<?php
/**
 * Plugin Name: Prodi
 * Plugin URI: -
 * Description: -
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://github.com/drajathasan/
 */

// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

$plugin->registerMenu('master_file', 'Daftar Prodi', __DIR__ . '/pages/prodi_list.php');

// Member Custom Fields
$plugin->register('member_custom_fields', function(&$member_custom_fields){
    $member_custom_fields = [
        [
            'dbfield' => 'prodiid',
            'label' => 'Prodi',
            'class' => 'select2" style="width: 30%',
            'type' => 'dropdown',
            'default' => 0,
            'width' => '40',
            'data' => (function(){
                $db = \SLiMS\DB::getInstance();
                $prodi = $db->query('select id,label from daftar_fakultas_prodi where parent > 0');
                $prodi->execute();

                $listprodi = [[0, 'Pilih']];
                while ($data = $prodi->fetch(\PDO::FETCH_ASSOC)) {
                    $listprodi[] = [$data['id'], $data['label']];
                }

                return serialize($listprodi);
            })()
        ]
    ];
});

// Biblio Custom Fields
$plugin->register('biblio_custom_fields', function(&$member_custom_fields){
    $member_custom_fields = [
        [
            'dbfield' => 'prodiid',
            'label' => 'Prodi',
            'class' => 'select2" style="width: 30%',
            'type' => 'dropdown',
            'default' => 0,
            'width' => '40',
            'data' => (function(){
                $db = \SLiMS\DB::getInstance();
                $prodi = $db->query('select id,label from daftar_fakultas_prodi where parent > 0');
                $prodi->execute();

                $listprodi = [[0, 'Pilih']];
                while ($data = $prodi->fetch(\PDO::FETCH_ASSOC)) {
                    $listprodi[] = [$data['id'], $data['label']];
                }

                return serialize($listprodi);
            })()
        ]
    ];
});