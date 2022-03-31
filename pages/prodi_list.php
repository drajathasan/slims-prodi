<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2022-03-31 05:25:56
 * @File name           : index.php
 */

defined('INDEX_AUTH') OR die('Direct access not allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-membership');
// start the session
require SB . 'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// set dependency
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require __DIR__ . '/../helper.php';
// end dependency

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

// Database
$db = \SLiMS\DB::getInstance();

// Pages title
$page_title = 'Daftar Fakultas/Prodi';

/* Action Area */
if (isset($_POST['saveData']))
{
    if (isset($_POST['updateRecordId']))
    {
        $Process = $db->prepare('update daftar_fakultas_prodi set code = ?, parent = ?, label = ?, description = ?, updated_at = ? where id = ?');
        $Process->execute([$_POST['code'], $_POST['parent'], $_POST['label'], $_POST['description'], date('Y-m-d H:i:s'), $_POST['updateRecordId']]);
    }
    else
    {
        $Process = $db->prepare('insert into daftar_fakultas_prodi set code = ?, parent = ?, label = ?, created_at = ?, updated_at = ?, description = ?');
        $Process->execute([$_POST['code'], $_POST['parent'], $_POST['label'], date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $_POST['description']]);
    }

    if (!$Process) exit(utility::jsToastr('Galat', 'Data tidak berhasil disimpan', 'error'));
    
    redirectWithMessage( getCurrentUrl($_GET), 'Data berhasil disimpan', function($url, $message){
        utility::jsToastr('Sukses', $message, 'success');
        echo <<<HTML
            <script>
                parent.$('#mainContent').simbioAJAX('{$url}');
            </script>
        HTML;
    });
}

if (isset($_POST['itemAction']))
{
    function haveChild($db, $id)
    {
        $data = $db->query('select id from daftar_fakultas_prodi where parent = ' . ((int)$id));

        return (bool)$data->num_rows;
    }

    $exclude = [];
    foreach ((is_array($_POST['itemID']) ? $_POST['itemID'] : [$_POST['itemID']]) as $id) {
        if (!haveChild($dbs, $id))
        {
            $dbs->query('delete from daftar_fakultas_prodi where id = ' . ((int)$id));
        }
        else
        {
            $exclude[] = $id;
        }
    }

    redirectWithMessage( getCurrentUrl($_GET), 'Data berhasil dihapus', function($url, $message) use($exclude) {
        utility::jsToastr('Sukses', $message, 'success');

        if (count($exclude) > 0)
        {
            utility::jsToastr('Peringatan', 'id ' . implode(',', $exclude) . ' tidak dapat dihapus karena berstatus sebagai inang. Jika dihapus akan merusak relasi data yang ada.', 'warning');
        }

        echo <<<HTML
            <script>
                parent.$('#mainContent').simbioAJAX('{$url}');
            </script>
        HTML;
    });
    exit;
}
/* End Action Area */
?>
<div class="menuBox">
    <div class="menuBoxInner memberIcon">
        <div class="per_title">
            <h2><?php echo $page_title; ?></h2>
        </div>
        <div class="sub_section">
            <div class="btn-group">
                <a href="<?= getCurrentUrl(['list' => 1]) ?>" class="btn btn-primary">Daftar Data</a>
                <a href="<?= getCurrentUrl(['add' => 1]) ?>" class="btn btn-success">Tambah Data</a>
            </div>
            <form name="search" action="<?= getCurrentUrl() ?>" id="search" method="get" class="form-inline"><?php echo __('Search'); ?>
                <input type="text" name="keywords" class="form-control col-md-3" /><?php if (isset($_GET['expire'])) { echo '<input type="hidden" name="expire" value="true" />'; } ?>
                <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
            </form>
        </div>
    </div>
</div>
<?php

if (isset($_REQUEST['itemID']) || isset($_GET['add']))
{
    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', getCurrentUrl(), 'post');
    $form->submit_button_attr = 'name="saveData" value="' . __('Save') . '" class="s-btn btn btn-default"';
    // form table attributes
    $form->table_attr = 'id="dataList" cellpadding="0" cellspacing="0"';
    $form->table_header_attr = 'class="alterCell"';
    $form->table_content_attr = 'class="alterCell2"';

    /* Form Element(s) */
    $search = $db->prepare('select * from daftar_fakultas_prodi where id = ?');
    $search->execute([$_REQUEST['itemID']??0]);

    $data = [];
    if ($search->rowCount())
    {
        $data = $search->fetch(PDO::FETCH_ASSOC);
        $form->addHidden('updateRecordId', $data['id']);
        $form->edit_mode = true;
        $form->submit_button_attr = 'name="saveData" value="' . __('Update') . '" class="s-btn btn btn-default"';
    }

    // Parent
    $parent = $db->query('select id,label from daftar_fakultas_prodi where parent = 0');
    
    $parentOptions = [['0', 'Pilih']];
    while ($parentData = $parent->fetch(PDO::FETCH_ASSOC)) {
        $parentOptions[] = [$parentData['id'], $parentData['label']];
    }

    $form->addSelectList('parent', 'Inang', $parentOptions, $data['parent']??'0', 'class="select2"', 'Inang');
    // Guest form is active?
    $form->addTextField('text', 'code', 'Kode', $data['code'] ?? '', 'class="form-control" style="width: 40%;"', 'Nomor unik untuk membedakan antar prodi');
    // Guest form is active?
    $form->addTextField('text', 'label', 'Label', $data['label']??'', 'class="form-control"', 'Berisi nama');
    // Description
    $form->addTextField('textarea', 'description', 'Deskripsi', $data['description'] ?? '', 'rows="1" class="form-control"', __('Description'));

    // print out the form object
    echo $form->printOut();
}
else
{
    // table spec
    $table_spec = 'daftar_fakultas_prodi';

    // create datagrid
    $datagrid = new simbio_datagrid();

    $datagrid->setSQLColumn('id', 'parent', '`code` Kode', '`label` Label', '`description` Deskripsi', '`updated_at` "Diperbaharui Pada"');
    $datagrid->invisible_fields = [0];

    // is there any search
    $criteria = 'label IS NOT NULL ';
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $keywords = $dbs->escape_string($_GET['keywords']);
        $criteria .= " AND (code LIKE '%$keywords%' OR description LIKE '%$keywords%') ";
    }

    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->icon_edit = SWB.'admin/'.$sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/edit.gif';
    $datagrid->table_name = 'memberList';
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = getCurrentUrl();

    function setParent($dbs, $data)
    {
        if ($data[1] > 0)
        {
            $parentCode = $_SESSION['parent'][$data[1]]??null;

            if (is_null($parentCode))
            {
                $parent = $dbs->prepare('select * from daftar_fakultas_prodi where id = ?');
                $parent->bind_param('i', $id);
                $id = $data[1];
                $parent->execute();
                $parentData = $parent->get_result()->fetch_assoc();
                $_SESSION['parent'][$data[1]] = $parentData['label'];
                $parentCode = $parentData['label'];
            }

            return '<strong class="d-block">' . $data[4] . '</strong><small class="badge badge-secondary rounded-pill p-2"><strong>Bagian dari ' . $parentCode . '</strong></small>';
        }

        return '<strong class="d-block">' . $data[4] . '</strong><small class="badge badge-primary rounded-pill p-2"><strong>Inang</strong></small>';
    }

    $datagrid->modifyColumnContent(4, 'callback{setParent}');

    echo $datagrid->createDataGrid($dbs, $table_spec, 20, true);
}
