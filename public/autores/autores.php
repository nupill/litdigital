<?php 
require_once(dirname(__FILE__) . '/../../application/controllers/ControleAutores.php');
require_once(dirname(__FILE__) . '/../../application/controllers/ControleDocumentos.php');
require_once(dirname(__FILE__) . '/../../application/controllers/ControleFatosHistoricos.php');
require_once(dirname(__FILE__) . '/../../application/controllers/ControleCriticasAutor.php');
require_once(dirname(__FILE__) . '/../../application/controllers/ControleComentarios.php');

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$controller = ControleAutores::getInstance();
$controller_documentos = ControleDocumentos::getInstance();
$controller_criticas_autor = ControleCriticasAutor::getInstance();
$controller_fatos_historicos = ControleFatosHistoricos::getInstance();
$controller_comentarios = ControleComentarios::getInstance();

$autor = $controller->get($id);
$estat_genero = $controller->getEstatGenero($id);

$loc_nasc = $controller->getLocNasc($id);
$loc_morte = $controller->getLocMorte($id);
$fontes = $controller->get_fontes($id);
$criticas_autor = $controller_criticas_autor->get_by_autor($id);

$documentos = $controller->get_documentos($id);

if ($autor) {
    $autor = $autor[0];
}

if (isset($autor['ano_nascimento'])) {
	$fatos_historicos = $controller_fatos_historicos->get_by_data($autor['ano_nascimento'],$autor['ano_morte']);
}

$comentarios_tmp = $controller_comentarios->get_autor($id);
$votos_tmp = $controller_comentarios->get_votos_autor($id);

$comentarios = array();
$votos = array();

foreach ($votos_tmp as $voto) {
	if (!isset($votos[$voto['Comentario_id']])) {
		$votos[$voto['Comentario_id']] = array();
	}
	$votos[$voto['Comentario_id']][$voto['Usuario_id']] = $voto;
}

foreach ($comentarios_tmp as $comentario) {
	if ($comentario['Comentario_id']) {
		if (!isset($comentarios[$comentario['Comentario_id']])) {
			$comentarios[$comentario['Comentario_id']] = array();
			$comentarios[$comentario['Comentario_id']]['replies'] = array();
			$comentarios[$comentario['Comentario_id']]['votes'] = array();
			if (isset($votos[$comentario['Comentario_id']])) {
				$comentarios[$comentario['Comentario_id']]['votes'] = $votos[$comentario['Comentario_id']];
			}
		}
		$comentario['votes'] = array();
		if (isset($votos[$comentario['id']])) {
			$comentario['votes'] = $votos[$comentario['id']];
		}
		$comentarios[$comentario['Comentario_id']]['replies'][$comentario['id']] = $comentario;
	}
	else {
		if (!isset($comentarios[$comentario['id']])) {
			$comentarios[$comentario['id']] = $comentario;
			$comentarios[$comentario['id']]['replies'] = array();
			$comentarios[$comentario['id']]['votes'] = array();
			if (isset($votos[$comentario['id']])) {
				$comentarios[$comentario['id']]['votes'] = $votos[$comentario['id']];
			}
		}
		else {
			$comentarios[$comentario['id']] = array_merge($comentarios[$comentario['id']], $comentario);
		}
	}
}
?>

<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início');?></a> &rarr;
	<?php echo __('Autores');?> &rarr;
	<?php 
	echo isset($autor['nome_completo']) ? (!empty($autor['nome_usual']) ? $autor['nome_usual'] : $autor['nome_completo']) : 'Nenhum resultado encontrado';
	?>
</div>
<div id="content">
	<?php 
	if (!$id) {
	?>
    <div id="no_results">
    	<em><?php echo __('ID não especificado');?></em>
    </div>
    <?php 
	}
	elseif (!$autor) {
	?>
    <div id="no_results">
    	<em><?php echo __('Autor não encontrado');?></em>
    </div> 
	<?php 
	}
	else {
		if ($autor['nome_usual']) {
			?>
			<h2><?php echo $autor['nome_usual'];?></h2>
			<em> <?php echo  __('Nome completo:').' '.$autor['nome_completo']; ?></em>
		<?php 
		} else { ?>
			<h2><?php $autor['nome_completo']; ?></h2>
		<?php 		
		}
	if ($autor['pseudonimo']) { ?>
		</br><em><?php echo __('Pseudônimo(s):').' '.$autor['pseudonimo']; ?></em>
		<?php 
	}
	?>
	<br />
	<br />
	<h3><?php echo __('Informações sobre o autor');?></h3>
	<ul>
		<?php
		
		/* Nascimento */
		
		if ($autor['ano_nascimento'] || $loc_nasc || $autor['seculo_nascimento']) {	
			$nascimento = "";
			if ($autor['ano_nascimento']) {
				$nascimento .= $autor['ano_nascimento'];
			} else {
				if ($autor['seculo_nascimento']) {
					$nascimento .= $autor['seculo_nascimento'];
				}
			}
			if ($loc_nasc && ($autor['ano_nascimento'] || $autor['seculo_nascimento'])) {
				$nascimento .= " - ";
			} 
			if ($loc_nasc) {
				$nascimento .= $loc_nasc;
			}
			echo "<li><b>".__('Nascimento')."</b>: " . $nascimento . "</li>";
		}		
		/* Morte */
		
		if ($autor['ano_morte'] || $loc_morte || $autor['seculo_morte']) {
			
			$morte = "";
			
			if ($autor['ano_morte']) {
				$morte .= $autor['ano_morte'];
			} else {
				if ($autor['seculo_morte']) {
					$morte .= $autor['seculo_morte'];
				}
			}
			if ($loc_morte && ($autor['ano_morte'] || $autor['seculo_morte'])) {
				$morte .= " - ";
			} 
			if ($loc_morte) {
				$morte .= $loc_morte;
			}
			echo "<li><b>".__('Morte')."</b>: " . $morte . "</li>";
		}
		
		// Descrição
		
		if ($autor['descricao']) {
			echo "<li><b>".__('Descrição')."</b>: " . $autor['descricao'] . "</li>";
		}
    	?>
	</ul>
	<?php
	if ($fontes) {
	?>
	<br />
	<h3><?php echo __('Fontes');?></h3>
	<ul>
	<?php
	foreach ($fontes as $fonte) {
	?>
	<li><?php echo $fonte['descricao']; ?></li>
	<?php
	}
	?>
	</ul>
	<br />
	<?php
	}
	}
	?>
    <h3><?php echo __('Obras do autor');?></h3>
    <br />
    <label class="normal" style="display: block">
    	<input id="obras_digitalizadas" type="checkbox" /> <?php echo __('Apenas obras digitalizadas');?> 
    </label>
    <table id="obras">
        <thead>
            <tr>
            	<th> </th>
                <th><?php echo __('Título');?></th>
                <th><?php echo __('Tipo');?></th>
                <th><?php echo __('Gênero');?></th>
                <th><?php echo __('Ano');?></th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($documentos) {
            foreach ($documentos as $documento) {
            	if ($documento['midias'] == 0) {
                	$documento['midias'] = '<img src="' .IMAGES_URI . 'ico_download2_disabled.png" alt="Não disponível para visualização" title="Não disponível para visualização" />';
                	
                }
                else {
                	$documento['midias'] = '<a href="'.DOCUMENTOS_URI.'?action=midias&id='.$documento['id'].'" alt="'.__('Visualizar obra').'" title="'.__('Visualizar obra').'">
                       		  <img src="' .IMAGES_URI . 'ico_download2.png" />
                       		  </a>';
                }
        ?>
            <tr id="<?php echo $documento['id']; ?>" class="<?php echo $documento['tipo']; ?>">
            	<td><?php echo $documento['midias'] ?></td>
                <td><?php echo $documento['titulo']; ?></td>
                <td><?php echo $documento['tipo']; ?></td>
                <td><?php echo $documento['genero']; ?></td>
                
                <?php 
                $ano = '-';
                if ($documento['ano_documento']) {
	                $ano = $documento['ano_documento'];
	            }
	            elseif ($documento['seculo_documento']) {
	                $ano = $documento['seculo_documento'];
	            }
                ?>
                
                <td><?php echo $ano; ?></td>
            </tr>
        <?php 
            }
        }
        ?>
        </tbody>
    </table>
	<?php
    	if ($criticas_autor) { ?>
			<hr />
		    <h3><?php echo __('Críticas sobre o Autor');?></h3>
		    <table id="criticas">
		        <thead>
		            <tr>
		                <th><?php echo __('Título');?></th>
		                <th><?php echo __('Autor da crítica');?></th>
		            </tr>
		        </thead>
		        <tbody>
		        	<?php
		            foreach ($criticas_autor as $critica_autor) {
		        	?>
		            <tr id="<?php echo $critica_autor['id']; ?>">
		                <td><?php echo $critica_autor['titulo']; ?></td>
		                <td><?php echo $critica_autor['autor_critica']; ?></td>
		            </tr>
		        <?php 
		        
		        }
		        ?>
		        </tbody>
		    </table>
    <?php } ?>
    
    <?php 
     	if ($estat_genero) {
     		
     	echo "<h3>";
     	echo __('Estatísticas');
		echo '</h3><br><div id="pieChart">';
		echo '<input type="hidden" id="graphTitle" name="graphTitle" value="';
		echo __('Gêneros literários do autor'); 
		echo '" /></div>';
     	}
     ?>
    
    <?php
    	if ($fatos_historicos) { ?>
		    <hr />
		    <h3><?php echo __('Fatos históricos associados');?></h3>
		    <table id="fatos_historicos">
		        <thead>
		            <tr>
		                <th><?php echo __('Ano do início');?></th>
		                <th><?php echo __('Ano do fim');?></th>
		                <th><?php echo __('Descrição');?></th>
		            </tr>
		        </thead>
		        <tbody>
		        	<?php
		            foreach ($fatos_historicos as $fato_historico) {
		        	?>
		            <tr id="<?php echo $fato_historico['id']; ?>">
		                <td><?php echo $fato_historico['ano_inicio']; ?></td>
		                <td><?php echo $fato_historico['ano_fim']; ?></td>
		                <td><?php echo $fato_historico['descricao']; ?></td>
		            </tr>
		        <?php 
	        }
	        ?>
	        </tbody>
	    </table>
     <?php } ?>

     
     <!--  Comentários (início) -->
     <hr />
     <h3><?php echo __('Comentários');?> (<span id="comment_count"><?php echo sizeof($comentarios); ?></span>)</h3>
     <?php 
     if (Auth::check()) {
     ?>
     <form id="comment_form" method="post" action="?action=add_comment&id=<?php echo $id; ?>">
     	<p><?php echo __('Insira seu comentário sobre este autor');?></p>
     	<input type="hidden" id="id" name="id" value="<?php echo $id; ?>" />
     	<label for="title"><?php echo __('Título');?></label>
     	<input type="text" id="title" name="title" />
     	<label for="comment"><?php echo __('Conteúdo');?></label>
     	<textarea id="comment" name="comment" maxlenght="4000"></textarea>
     	<input type="submit" value="<?php echo __('Enviar');?>" />
     	<span id="status"></span>
     </form>
     <?php
     }
     else {
     ?>
     <br />
     <h4><?php echo __('Faça login para poder comentar');?></h4>
     <br />
     <?php
	 }    
	 ?>
	 
     <div id="comments">
     	 <?php
	     foreach ($comentarios as $comentario) {
	     ?>
	     <div class="comment">
	     	<?php 
			/*
	     	<div class="comment_header">
		     	<span class="user"><?php echo $comentario['usuario']; ?></span>
		     	<em><?php echo date('d/m/Y H:i:s', strtotime($comentario['data_inclusao'])); ?></em>
		     	<div id="<?php echo $comentario['id']; ?>">
		     		<?php 
		     		$score_class = '';
		     		if ($comentario['score'] > 0) {
		     			$score_class = 'positive';
		     		}
		     		elseif ($comentario['score'] < 0) {
		     			$score_class = 'negative';
		     		}
		     		?>
			     	<span class="score <?php echo $score_class; ?>"><?php echo ($comentario['score']) ? $comentario['score'] : 0; ?></span>
				    <?php 
				    if (Auth::check()) {
				    	$vote = false;
				    	if (isset($comentario['votes'][$_SESSION['id']])) {
				    		$vote = $comentario['votes'][$_SESSION['id']]['tipo'];
				    	}
				    ?>
				    <button class="vote_up <?php if ($vote == 1) { echo 'active'; } ?>">Up</button>
				    <button class="vote_down <?php if ($vote == -1) { echo 'active'; } ?>">Down</button>
			     	<button class="reply">Responder</button>
			     	<?php 
				    }
				    ?>
		     	</div>
		     </div>
		     <div class="comment_content">
		     	<h4><?php echo $comentario['titulo']; ?></h4>
		     	<p><?php echo $comentario['conteudo']; ?></p>
		     </div>
		     */
		     ?>
		     <div class="comment_header">
		     	<h4><?php echo $comentario['titulo']; ?></h4>
		     	<input type="hidden" id="comment_id" value="<?php echo $comentario['id']; ?>" />
			    <button class="reply"><?php echo __('Responder');?></button>
			    <a name="<?php echo $comentario['id']; ?>"></a>
		     </div>
		     <div class="comment_reply">
			     <div class="comment_reply_header">
			     	<span class="user"><?php echo $comentario['usuario']; ?></span>
			     	<em><?php echo date('d/m/Y H:i:s', strtotime($comentario['data_inclusao'])); ?></em>
			     	<div id="<?php echo $comentario['id']; ?>">
			     		<?php 
			     		$score_class = '';
			     		if ($comentario['score'] > 0) {
			     			$score_class = 'positive';
			     		}
			     		elseif ($comentario['score'] < 0) {
			     			$score_class = 'negative';
			     		}
			     		?>
				     	<span class="score <?php echo $score_class; ?>"><?php echo ($comentario['score']) ? $comentario['score'] : 0; ?></span>
					    <?php 
					    if (Auth::check()) {
					    	$vote = false;
					    	if (isset($comentario['votes'][$_SESSION['id']])) {
					    		$vote = $comentario['votes'][$_SESSION['id']]['tipo'];
					    	}
					    ?>
					    <button class="vote_up <?php if ($vote == 1) { echo 'active'; } ?>" title="<?php echo ($vote == 1) ? 'Anular voto positivo' : 'Voto positivo'; ?>">Up</button>
					    <button class="vote_down <?php if ($vote == -1) { echo 'active'; } ?>" title="<?php echo ($vote == -1) ? 'Anular voto negativo' : 'Voto negativo'; ?>">Down</button>
					    <button class="comment_flag" title="Denunciar">Flag</button>
				     	<?php 
					    }
					    ?>
			     	</div>
			     </div>
			     <div class="reply_content">
			     	<p><?php echo $comentario['conteudo']; ?></p>
			     </div>
		     </div>
		     <?php
		     foreach ($comentario['replies'] as $reply) {
		     ?>
		     <div class="comment_reply">
			     <div class="comment_reply_header">
			     	<a name="<?php echo $reply['id']; ?>"></a>
			     	<span class="user"><?php echo $reply['usuario']; ?></span>
			     	<em><?php echo date('d/m/Y H:i:s', strtotime($reply['data_inclusao'])); ?></em>
			     	<div id="<?php echo $reply['id']; ?>">
			     		<?php 
			     		$score_class = '';
			     		if ($reply['score'] > 0) {
			     			$score_class = 'positive';
			     		}
			     		elseif ($reply['score'] < 0) {
			     			$score_class = 'negative';
			     		}
			     		?>
				     	<span class="score <?php echo $score_class; ?>"><?php echo ($reply['score']) ? $reply['score'] : 0; ?></span>
					    <?php 
					    if (Auth::check()) {
					    	$vote = false;
					    	if (isset($reply['votes'][$_SESSION['id']])) {
					    		$vote = $reply['votes'][$_SESSION['id']]['tipo'];
					    	}
					    ?>
					    <button class="vote_up <?php if ($vote == 1) { echo 'active'; } ?>" title="<?php echo ($vote == 1) ? 'Anular voto positivo' : 'Voto positivo'; ?>">Up</button>
					    <button class="vote_down <?php if ($vote == -1) { echo 'active'; } ?>" title="<?php echo ($vote == -1) ? 'Anular voto negativo' : 'Voto negativo'; ?>">Down</button>
					    <button class="comment_flag" title="Denunciar">Flag</button>
				     	<?php 
					    }
					    ?>
			     	</div>
			     </div>
			     <div class="reply_content">
			     	<p><?php echo $reply['conteudo']; ?></p>
			     </div>
		     </div>
		     <?php	
		     }
		     ?>
	     </div>
	     <?php 
	     }
	     ?>
     </div>
     <!--  Comentários (fim) -->
</div>

<script type="text/javascript">
var graphcontent = "";
function LoadData(jdata) {
  graphcontent = jdata;
};
<?php echo sprintf("LoadData(%s)", $estat_genero);
		//htmlspecialchars($estat_genero,ENT_QUOTES, 'UTF-8')); 
?>
</script>

<script type="text/javascript">

$(document).ready(function(){
	$('#obras').loadTable({
	    aoColumns: [
	    	{ "sWidth": "18px", "sName": "midias" },
	        { "sWidth": "550px", "sName": "titulo" },
	        { "sName": "tipo" },
	        { "sName": "genero" },
	        { "sName": "ano" }
	    ],
	    aaSorting: [[1,'asc']],
	    allowCreate: false,
	    allowDelete: false,
	    allowUpdate: false,
	    bServerSide: false,
	    sPaginationType: "two_button",
	    fnDrawCallback: function() {
	        $("#obras tbody td").each(function() {
	            var id = $(this).parent().attr('id');
	            var type = $(this).parent().attr('class');
	            type = type.split(' ');
	            type = type[0]; //first class
	            var uri = '<?php echo DOCUMENTOS_URI; ?>?id=' + id;
	            $(this).html('<a href="' + uri + '">' + $(this).html() + '&nbsp;</a>');
	        });
	    }
	});

	$.fn.dataTableExt.afnFiltering.push(
	    function(oSettings, aData, iDataIndex) {
		    if (oSettings.sTableId == 'obras') {
			    if ($(aData[0]).filter('a').length > 0 || !$('#obras_digitalizadas').attr('checked')) {
				    return true;
			    }
			    else {
				    return false
			    }
			}
	    	return true;
    	}
    );

	$('#obras_digitalizadas').click(function() {
		$("#obras").dataTable().fnDraw();
	});
	
	<?php if ($criticas_autor) { ?>
		$('#criticas').loadTable({
	    aoColumns: [
	        { "sWidth": "550px", "sName": "titulo" },
	        { "sName": "autor_critica" },
	    ],
	    allowCreate: false,
	    allowDelete: false,
	    allowUpdate: false,
	    bServerSide: false,
	    sPaginationType: "two_button",
	    fnDrawCallback: function() {
	        $("#criticas tbody td").each(function() {
	            var id = $(this).parent().attr('id');
	            var type = $(this).parent().attr('class');
	            type = type.split(' ');
	            type = type[0]; //first class
	            var uri = '<?php  echo CRITICAS_AUTOR_URI; ?>?id=' + id;
	            $(this).html('<a href="' + uri + '">' + $(this).text() + '&nbsp;</a>');
	        });
	    }
	});
	<?php 	
	}
	?>
	<?php if ($fatos_historicos) { ?>
		$('#fatos_historicos').loadTable({
		    aoColumns: [
		        { "sWidth": "100px", "sName": "ano_inicio" },
		        { "sWidth": "100px", "sName": "ano_fim" },
		        { "sName": "descricao" },
		    ],
		    allowCreate: false,
		    allowDelete: false,
		    allowUpdate: false,
		    bServerSide: false,
		    sPaginationType: "two_button",
		    fnDrawCallback: function() {
		        $("#fatos_historicos tbody td").each(function() {
		            var id = $(this).parent().attr('id');
		            var type = $(this).parent().attr('class');
		            type = type.split(' ');
		            type = type[0]; //first class
		      //      var uri = '<?php echo ''; ?>?id=' + id;
		     //       $(this).html('<a href="' + uri + '">' + $(this).text() + '&nbsp;</a>');
		        });
		    }
		});
	<?php 	
	}
	?>
});
</script>