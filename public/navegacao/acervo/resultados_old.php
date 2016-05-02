<?php 

require_once(APPLICATION_PATH . "/controllers/ControleDocumentos.php");

$controle_documentos = ControleDocumentos::getInstance();
$acervo = $controle_documentos->get_distinct_acervo();

?>

<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>">Início</a> &rarr;
	<a href="<?php echo NAVEGACAO_DOCUMENTO_URI; ?>">Navegação</a> &rarr;
	Acervo
</div>

<div id="div_acervo" style="margin-top: 20px">
	<p>Filtrar por <b>acervo</b>:</p>
	<form id="form_acervo" action="<?php echo NAVEGACAO_URI; ?>acervo/"" method="post" class="inline">
		<select id="acervo" name="acervo">
			<option value="todos">Todos</option>
		    <?php 
		    foreach ($acervo as $array => $nome) {
		    ?>
				<option value="<?php echo $nome['acervo'] ?>"><?php echo $nome['acervo']; ?></option>
		    <?php            
		    }
		    ?>  
		</select>
		<input type="submit" id="submit_acervo" name="submit_acervo" value="Pesquisar" />
	</form>
</div>

<div id="content">
    <div id="search_results">
    	<table id="results">
            <thead>
                <tr>
                    <th></th>
                    <th>Título da obra</th>
                    <th>Autor(es)</th>
                    <th>Tipo</th>
                    <th>Gênero</th>
                    <th>Ano</th>
                </tr>
            </thead>
        </table>
    </div>
    <script type="text/javascript">
    $(function() {
    	
    	var oTable = $('#results').loadTable({
            sAjaxSource: "?action=getTableData&pseudoParam=<?php echo time(); ?>&<?php echo make_request_url($_REQUEST); ?>",
            aoColumns: [
                { "sWidth": "18px", "sName": "midias" },
                { "sWidth": "350px", "sName": "titulo" },
                { "sName": "autores_nome_completo" },
                { "sWidth": "100px", "sName": "nome_tipodocumento" },
                { "sName": "nome_genero" },
                { "sName": "ano_documento" }
            ],
            allowDelete: false,
            allowCreate: false,
            allowUpdate: false,
            aaSorting: [[1,'asc']],
            sPaginationType: "two_button",
            iDisplayLength: 30,
            sDom: '<"top"ifr>t<"bottom"p><"clear">',
            oLanguage: {
                "sProcessing": "Carregando...",
                "sLengthMenu": "Exibir _MENU_ resultados",
                "sZeroRecords": "Nenhum resultado encontrado",
                "sInfo": "Exibindo _START_ a _END_ de _TOTAL_ resultados",
                "sInfoEmpty": "Nenhum resultado",
                "sInfoFiltered": "(filtrados de _MAX_ resultados)",
                "sInfoPostFix": "",
                "sSearch": "Procurar nos resultados:",
                "sUrl": "",
                "oPaginate": {
                    "sFirst":    "Primeira",
                    "sPrevious": "Anterior",
                    "sNext":     "Próxima",
                    "sLast":     "Última"
                }
            },
            fnInitComplete: function() {
            	$('.top').prepend('<label class="normal" style="display: block"><input id="obras_digitalizadas" type="checkbox" /> Apenas obras digitalizadas</label>');
            }
        });

        $('#obras_digitalizadas').change(function() {
        	
            if ($(this).is(':checked')) {
                $.fn.getTable('results').fnSetAjaxSource('?action=getTableData&somente_midias=1&<?php echo make_request_url($_POST); ?>');
            }
            else {
                $.fn.getTable('results').fnSetAjaxSource('?action=getTableData&somente_midias=0&<?php echo make_request_url($_POST); ?>');
            }
            $.fn.getTable('results').fnDraw();
        });
        
    });
    </script>
</div>
