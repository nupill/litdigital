<?php
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}
?>
<div id="content">
    <h2>
        <a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
        <a href="<?php echo ADMIN_DOCUMENTOS_URI; ?>"><?php echo __('Documentos');?></a> &raquo;
        <?php echo __('Objetos de Arte');?>
    </h2>
    <br />
    <form action="?action=del" method="post">
        <table id="objetos-arte" class="disable_first">
            <thead>
                <tr>
                    <th><input type="checkbox" name="check_all" id="check_all" /></th>
                    <th><?php echo __('Título');?></th>
                    <th><?php echo __('Autor(es)');?></th>
                    <th><?php echo __('Mídias');?></th>
                </tr>
            </thead>
        </table>
    </form>
</div>
<em class="info"><?php echo __('Clique em um registro da tabela para editá-lo.');?></em>

<script type="text/javascript">
    $(document).ready(function(){
        $('#objetos-arte').loadTable({
            sAjaxSource: "?action=getTableData",
            aoColumns: [
                { "sWidth": "30px", "sName": "id", "bSortable": false },
                { "sWidth": "450px", "sName": "titulo" },
                { "sName": "autores" },
                { "sWidth": "70px", "sName": "midias" }
            ],
            aaSorting: [[1,'asc']],
            sPaginationType: "two_button",
            iDisplayLength: 15,
            fileEditForm: "editar/",
            fileAddForm: "cadastrar/",
            delConfirmation: true
        });
    });
</script>