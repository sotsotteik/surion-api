<?php
/**
 * @desc Generator View to generate the model
 */
$this->title = 'Model Generator';
?>
<div class="row my-3 mx-3">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <h3>Model Generator</h3>
        <table class="table table-striped table-condensed">
            <tr>
                <th>Table</th>
                <th>#</th>
            </tr>
            <?php
            foreach ($data as $tbl):
                ?>
                <tr>
                    <td>
                        <?= $tbl['table_name'] ?>
                    </td>
                    <td>
                        <a href="<?= BASEURL . 'coregen/tables/tableName/' . $tbl['table_name'] ?>/db/<?= $dbParam ?>">Download</a>
                    </td>
                </tr>
                <?php
            endforeach;
            ?>
        </table>
    </div>
</div>