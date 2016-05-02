<?php
//Pesquisa resultados no banco de dados
//set_include_path(".:/var/www/html/bdnupill/application/zf:/var/www/html/bdnupill/application/zf/Zend");
set_include_path(".:" .dirname(__FILE__) . '/../../../application/zf:'.dirname(__FILE__).'/../../../application/zf/Zend');
require_once(dirname(__FILE__) . '/../../../application/include/FirePHPCore/fb.php');

require_once(dirname(__FILE__) . '/../../../application/controllers/ControleBusca.php');
require_once(dirname(__FILE__) . '/../../../application/controllers/ControleDocumentos.php');
$termo = isset($_REQUEST['termo']) ? trim($_REQUEST['termo']) : '';
$forma_busca = isset($_REQUEST['forma_busca']) ? trim($_REQUEST['forma_busca']) : '';


if ($termo) {
	if (get_magic_quotes_gpc()) {
		$termo = stripslashes($termo);
	}
	$controller = ControleBusca::getInstance();
	$results = $controller-> busca_conteudo($termo, $forma_busca); //$forma_busca

	$personalizacao = false;
	if(Auth::check()){
		$personalizacao = Auth::checa_personalizacao();
	}
	
	if ($personalizacao){
		$controllerDoc = ControleDocumentos::getInstance();
		$id_usuario = isset($_SESSION['id']) ? $_SESSION['id'] : '';
		$escore_total_genero = $controllerDoc->retorna_escore_total_genero($id_usuario);
		$escore_total_autor = $controllerDoc->retorna_escore_total_autor($id_usuario);
	}

}
?>
<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr; <a
		href="<?php echo BUSCA_URI; ?>"><?php echo __('Busca');?></a> &rarr; <?php echo __('Conteúdo');?> (
		<?php echo $termo; ?>
	)
</div>
<div id="content">
<?php
if (!$termo) {
	?>
	<div id="search_no_results">
		<em><?php echo __('Termo de busca não especificado');?></em> <a
			href="<?php echo BUSCA_URI; ?>"><?php echo __('Voltar para o formulário de busca');?></a>
	</div>
	<?php
}
elseif (!$results) {
	?>
	<div id="search_no_results">
		<em><?php echo __('Nenhum resultado encontrado');?></em> <a
			href="<?php echo BUSCA_URI; ?>"><?php echo __('Voltar para o formulário de busca');?></a>
	</div>
	<?php
}
else {
	?>
	<div id="search_results">
		<!--    	<h4>Resultados da busca por <em><?php echo $termo; ?></em></h4>-->
		<table id="results" style="width: 990px">
			<thead>
				<tr>
					<th style="width: 20px"></th>
					<th style="width: 280px"><?php echo __('Título');?> / <?php echo __('Obra');?></th>
					<th style="width: 280px"><?php echo __('Autores');?></th>
					<th style="width: 130px"><?php echo __('Tipo');?></th>
					<th style="width: 180px"><?php echo __('Gênero');?></th>
					<th style="width: 80px"><?php echo __('Relevância');?></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$total_lucene = 0;
			$total_personalizacao = 0;
			foreach ($results as $result) {
				$total_lucene += $result->score;
				if ($personalizacao){
					$id_usuario = isset($_SESSION['id']) ? $_SESSION['id'] : '';
					$escore_genero = $controllerDoc->calc_escore_genero($result->genero, $id_usuario, $escore_total_genero);
					$escore_autor = $controllerDoc->calc_escore_autor($result->document_id, $id_usuario, $escore_total_autor);
					$escore_pref = ($escore_genero+$escore_autor)/2;
					$total_personalizacao += $escore_pref;
				}
			}
			
			foreach ($results as $result) {
				if (!$result->title){
					$result->title = $result->nome_arquivo;
				}
				if ($personalizacao) {
					$id_usuario = isset($_SESSION['id']) ? $_SESSION['id'] : '';
					$escore_genero = $controllerDoc->calc_escore_genero($result->genero, $id_usuario, $escore_total_genero);
					$escore_autor = $controllerDoc->calc_escore_autor($result->document_id, $id_usuario, $escore_total_autor);
					$escore_pref = ($escore_genero+$escore_autor)/2;
                                        if (($total_personalizacao==0)||($total_lucene==0)) {
                                           $escore_pref_normalizado=0;
                                            $media_escore=0;
										} else {
                                          	 $escore_pref_normalizado = $escore_pref/$total_personalizacao;
					  						 $media_escore = ($escore_pref_normalizado+($result->score/$total_lucene))/2;
                                        } 
					//$resultado_final = round($media_escore,4)*10;
					$resultado_final = round($media_escore,4)*10;
				} else {
					$resultado_final = round(($result->score/$total_lucene),4)*10;
					//$resultado_final = round(($result->score/$total_lucene),4)*100;	
				}
				?>
				<tr id="<?php echo $result->document_id; ?>">
					<td id="<?php echo $result->id_midia; ?>"><img src="<?php echo IMAGES_URI; ?>ico_download2.png" alt="<?php echo __('Detalhes sobre a mídia');?>" title="<?php echo __('Detalhes sobre a mídia');?>" /></td>
					<td id="<?php echo $result->id_midia; ?>">
					<?php
					if ($result->title == $result->obra) {
						echo $result->obra."<br />";
					}
					else {
						echo $result->obra."<br /><em>".$result->title."</em>";
					}
					?>
					</td>
					<td id="<?php echo $result->id_midia; ?>"><?php echo $result->autor_nome; ?></td>
					<td id="<?php echo $result->id_midia; ?>"><?php echo $result->tipo; ?></td>
					<td id="<?php echo $result->id_midia; ?>"><?php echo $result->genero; ?></td>
					<td id="<?php echo $result->id_midia; ?>"><?php echo $resultado_final; ?></td>
				</tr>
				<?php
			}
			?>
			</tbody>
		</table>
	</div>
	<script type="text/javascript">
    $(function() {

    	$('#results tbody td').each(function(index) {
			var url = "<?php echo DOCUMENTOS_URI; ?>?action=midias&id="+$(this).parent().attr('id')+"&id_midia="+$(this).attr('id');
			$(this).html('<a href="'+url+'">' + $(this).html() + '&nbsp;</a>');
		});

		/*
    	$.fn.dataTableExt.afnFiltering.push(
    		function(oSettings, aData, iDataIndex) {
	            if ($('#obras_digitalizadas').is(':checked')) {
		            if (aData[0].indexOf('<a href') !== -1) {
						return true;
		            }
	            }
	            else {
		            return true;
	            }
    			return false;
    		}
    	);
    	*/
        
    	var oTable = $('#results').dataTable({
        	//sAjaxSource: "?action=getTableData",
        	aoColumns: [
        		{ "sName": "midias" },
        		{ "sName": "titulo" },
        		{ "sName": "autores" },
        		{ "sName": "tipo" },
        		{ "sName": "genero" },
        		{ "sName": "relevancia" }
        	],
        	aaSorting: [[5,'desc']],
        	sPaginationType: "two_button",
        	iDisplayLength: 30,
        	sDom: '<"top"ifr>t<"bottom"p><"clear">',
        	oLanguage: {
				"sProcessing": "<?php echo __('Carregando');?>...",
				"sLengthMenu": "<?php echo __('Exibir _MENU_ resultados');?>",
				"sZeroRecords": "<?php echo __('Nenhum resultado encontrado');?>",
				"sInfo": "<?php echo __('Exibindo _START_ a _END_ de _TOTAL_ resultados');?>",
				"sInfoEmpty": "<?php echo __('Nenhum resultado');?>",
				"sInfoFiltered": "<?php echo __('(filtrados de _MAX_ resultados)');?>",
				"sInfoPostFix": "",
				"sSearch": "<?php echo __('Procurar nos resultados');?>:",
				"sUrl": "",
				"oPaginate": {
					"sFirst":    "<?php echo __('Primeira');?>",
					"sPrevious": "<?php echo __('Anterior');?>",
					"sNext":     "<?php echo __('Próxima');?>",
					"sLast":     "<?php echo __('Última');?>"
				}
			},
			fnInitComplete: function() {
				//$('.top').prepend('<label class="normal"><input id="obras_digitalizadas" type="checkbox" /> Apenas obras digitalizadas</label>');
				$('#results').show();

				$('#results tbody td').each(function(index) {
					var url = "<?php echo DOCUMENTOS_URI; ?>?action=midias&id="+$(this).parent().attr('id')+"&id_midia="+$(this).attr('id');
					$(this).html('<a href="'+url+'">' + $(this).html() + '&nbsp;</a>');
				});
				
				$('.dataTables_wrapper').fadeIn();
			}
        });

		/*
    	$('#obras_digitalizadas').change(function() {
			oTable.fnDraw();
        });
        */

    });
    </script>
    <?php
}
?>
</div>
