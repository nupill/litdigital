<?php 
require_once(dirname(__FILE__) . '/../../application/controllers/ControleDocumentos.php');
require_once(dirname(__FILE__) . '/../../application/controllers/ControleCriticasObra.php');
require_once(dirname(__FILE__) . '/../../application/controllers/ControleFatosHistoricos.php');
require_once(dirname(__FILE__) . '/../../application/controllers/ControleComentarios.php');
require_once(dirname(__FILE__) . '/../../application/controllers/ControleEditoras.php');


$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$controller = ControleDocumentos::getInstance();
$controle_editora = ControleEditoras::getInstance();

$documento = $controller->get($id);
$midias = $controller->get_midias($id);
$fontes = $controller->get_fontes($id);
$editoras = $controller->get_editoras($id);
if ($documento) {
    $documento = $documento[0];
}

$autor = $controller->get_autores($id);
if ($autor) {
	$autor = $autor[0];
}

$generos = $controller->get_generos_new($id);
$idiomas = $controller->get_idiomas_new($id);


//pegar autores da obra e pelas datas de nascimento destes, buscar os fatos historicos

$controller_fatos_historicos = ControleFatosHistoricos::getInstance();
$controller_criticas_obra = ControleCriticasObra::getInstance();
$controller_comentarios = ControleComentarios::getInstance();

$criticas_obra = $controller_criticas_obra->get_by_obra($id);

$fatos_historicos = array();
if (isset($autor['ano_nascimento'])) {
	$fatos_historicos = $controller_fatos_historicos->get_by_data($autor['ano_nascimento'],$autor['ano_morte']);
}

$comentarios_tmp = $controller_comentarios->get_documento($id);
$votos_tmp = $controller_comentarios->get_votos_documento($id);

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
	<a href="<?php echo HOME_URI; ?>"><?php echo __('Início'); ?></a> &rarr;
	<?php echo __('Documentos'); ?> &rarr;
	<?php 
	echo isset($documento['titulo']) ? $documento['titulo'] : __('Nenhum resultado encontrado');
	?>
</div>
<div id="content">
	<?php 
	if (!$id) {
	?>
    <div id="no_results">
    	<em><?php echo __('ID não especificado'); ?></em>
    </div>
    <?php 
	}
	elseif (!$documento) {
	?>
    <div id="no_results">
    	<em><?php echo __('Documento não encontrado'); ?></em>
    </div> 
	<?php 
	}
	else {
	?>
	<h2><?php echo __('Título'); ?>: <i><?php echo $documento['titulo']; ?></i></h2>
	<?php 
		if (isset($documento['subtitulo']) && $documento['subtitulo']) {
		?>
	           <em><?php echo __('Subtítulo'); ?>: <?php echo $documento['subtitulo']; ?></em></br> 
	        <?php   
	        }
		    if (isset($documento['titulo_alternativo']) && $documento['titulo_alternativo']) {
	        ?>
	           <em><?php echo __('Título alternativo'); ?>: <?php echo $documento['titulo_alternativo']; ?></em></br>
	        <?php   
	        }
	?>
	<em>
	<?php 
	$autores = $controller->get_autores($id);
    for ($i=0; $i<sizeof($autores); $i++) {
    ?>
        <a href="<?php echo AUTORES_URI; ?>?id=<?php echo $autores[$i]['id']; ?>"><?php echo __('Autor'); ?>: <?php echo !empty($autores[$i]['nome_usual']) ? $autores[$i]['nome_usual'] : $autores[$i]['nome_completo']; ?></a>
    <?php
        if ($i<sizeof($autores)-1) {
           echo ', ';   
        } 
    }
    
    ?>
	</em>
	<br />
	<br />
    <h3><?php echo __('Informações sobre o documento'); ?></h3>
	<ul>
		<?php 
		//Obra Literária
	    if (isset($documento['abrangencia']) && $documento['abrangencia']) {
	    ?>
	        <li><?php echo __('Abrangência'); ?>: <?php echo $documento['abrangencia']; ?></li>
	    <?php   
	    }
	    if (isset($documento['direitos']) && $documento['direitos']) {
	    ?>
	        <li><?php echo __('Direitos'); ?>: <?php echo $documento['direitos']; ?></li>
	    <?php   
	    }
	    if (isset($documento['localizacao']) && $documento['localizacao']) {
	    ?>
	        <li><?php echo __('Localização'); ?>: <?php echo $documento['localizacao']; ?></li>
	    <?php   
	    }
	    if (isset($documento['estado']) && $documento['estado']) {
	    ?>
	        <li><?php echo __('Estado'); ?>: <?php echo $documento['estado']; ?></li>
	    <?php   
	    }
	    if (isset($documento['ano_producao']) && $documento['ano_producao'] &&
	        isset($documento['ano_producao_fim']) && $documento['ano_producao_fim']) {
        ?>
           <li><?php echo __('Ano de produção'); ?>: <?php echo $documento['ano_producao'] . ' - ' . $documento['ano_producao_fim']; ?></li>
        <?php   
        }
        elseif (isset($documento['ano_producao']) && $documento['ano_producao']) {
        ?>
            <li><?php echo __('Ano de produção'); ?>: <?php echo $documento['ano_producao']; ?></li>
        <?php   
        }
	    elseif (isset($documento['seculo_producao']) && $documento['seculo_producao']) {
        ?>
           <li><?php echo __('Século de produção'); ?>: <?php echo $documento['seculo_producao']; ?></li>
        <?php   
        }
        
        if (isset($documento['dimensao']) && $documento['dimensao']) {
        ?>
           <li><?php echo __('Dimensão'); ?>: <?php echo $documento['dimensao']; ?></li>
        <?php   
        }
        if (isset($documento['TipoDocumento_id']) && $documento['TipoDocumento_id']) {
        	$tipo = $controller->get_tipos($documento['TipoDocumento_id']);
        ?>
           <li><?php echo __('Tipo'); ?>: <?php echo $tipo; ?></li>
        <?php   
        }
        if (isset($documento['Categoria_id']) && $documento['Categoria_id']) {
            $categoria = $controller->get_categorias(null, $documento['Categoria_id']);
            $categoria = $categoria[0]['nome'];
        ?>
           <li><?php echo __('Categoria'); ?>: <?php echo $categoria; ?></li>
        <?php
        }
           
        }        
        if ($generos) {        
        $ultimoGenero = end($generos);        
        ?>
        <li><?php echo __('Gêneros'); ?>:        
        <?php
        foreach ($generos as $genero) {
            if($genero != $ultimoGenero){
        ?>
            <?php echo $genero['nome']; ?>;
        <?php
            }
            else{
        ?>
            <?php echo $genero['nome'];?>
        <?php
            }
        }
        ?>
        </li>
        <?php
        }
        
        if ($editoras) {
        	for ($i=0; $i<sizeof($editoras); $i++) {
        		$loc = $controle_editora->getLocal($editoras[$i]['id']);
        		if ($editoras[$i]['periodico']==0) {
        		?>
        		<li><?php echo __('Editora'); ?>: <?php echo $editoras[$i]['nome'].", ".$loc; ?>
        		<?php 
        		} else {
        		?>
        		<li><?php echo __('Periódico'); ?>: <?php echo $editoras[$i]['nome'].", ".$loc; ?>
        		<?php 
        		}
       // 		if (isset($editoras[$i]['descricao']) && $editoras[$i]['descricao']) {
      // 				echo '- '.$editoras[$i]['descricao'];
      //  			}
        		?>
        		</li> 
        		<?php 
        	}       	
        }

        if (isset($documento['descricao']) && $documento['descricao']) {
        	?>
        <li><?php echo __('Descrição'); ?>: <?php echo $documento['descricao']; ?></li>
        <?php
        }
       
        
        if (isset($documento['ano_publicacao_inicio']) && $documento['ano_publicacao_inicio'] &&
            isset($documento['ano_publicacao_fim']) && $documento['ano_publicacao_fim']) {
        ?>
        	<li><?php echo __('Ano de publicação'); ?>: <?php echo $documento['ano_publicacao_inicio'] . " - " . $documento['ano_publicacao_fim']; ?></li>
        <?php
        }
        elseif (isset($documento['ano_publicacao_inicio']) && $documento['ano_publicacao_inicio']) {
        ?>
           <li><?php echo __('Ano de publicação'); ?>: <?php echo $documento['ano_publicacao_inicio']; ?></li>
        <?php
        }
        elseif (isset($documento['seculo_publicacao']) && $documento['seculo_publicacao']) {
        ?>
           <li><?php echo __('Século de publicação'); ?>: <?php echo $documento['seculo_publicacao']; ?></li>
        <?php   
        }
        if (isset($documento['seculo_encenacao']) && $documento['seculo_encenacao']) {
        ?>
           <li><?php echo __('Século de encenação'); ?>: <?php echo $documento['seculo_encenacao']; ?></li>
        <?php   
        /*
        }
        
	    if (isset($documento['Idioma_id']) && $documento['Idioma_id']) {
            $idioma = $controller->get_idiomas($documento['Idioma_id']);
            $idioma = $idioma[0]['descricao'];
        ?>
           <li><?php echo __('Idioma'); ?>: <?php echo $idioma; ?></li>
        */
        }    
        if ($idiomas) {        
        $ultimoIdioma = end($idiomas);        
        ?>
        <li><?php echo __('Idiomas'); ?>:        
        <?php
        foreach ($idiomas as $idioma) {
            if($idioma != $ultimoIdioma){
        ?>
            <?php echo $idioma['descricao']; ?>;
        <?php
            }
            else{
        ?>
            <?php echo $idioma['descricao'];?>
        <?php
            }
        }
        ?>
        </li>
        <?php   
        if (isset($documento['ano_encenacao']) && $documento['ano_encenacao']) {
        ?>
           <li><?php echo __('Ano de encenação'); ?>: <?php echo $documento['ano_encenacao']; ?></li>
        <?php   
        }
        if (isset($documento['local_encenacao']) && $documento['local_encenacao']) {
        ?>
           <li><?php echo __('Local de encenação'); ?>: <?php echo $documento['local_encenacao']; ?></li>
        <?php   
        }
        if (isset($documento['personagens']) && $documento['personagens']) {
        ?>
           <li><?php echo __('Personagens'); ?>: <?php echo $documento['personagens']; ?></li>
        <?php   
        }
        if (isset($documento['palavra_chave']) && $documento['palavra_chave']) {
        ?>
           <li><?php echo __('Palavra-chave'); ?>: <?php echo $documento['palavra_chave']; ?></li>
        <?php   
        }
        
        //Audiovisual
	    if (isset($documento['volume']) && $documento['volume']) {
        ?>
           <li><?php echo __('Volume'); ?>: <?php echo $documento['volume']; ?></li>
        <?php   
        }
        if (isset($documento['local_publicacao']) && $documento['local_publicacao']) {
        ?>
           <li><?php echo __('Local de publicação'); ?>: <?php echo $documento['local_publicacao']; ?></li>
        <?php   
        }
	    if (isset($documento['edicao']) && $documento['edicao']) {
        ?>
           <li><?php echo __('Edição'); ?>: <?php echo $documento['edicao']; ?></li>
        <?php   
        }
        
	    //Biblioteca
        if (isset($documento['num_paginas']) && $documento['num_paginas']) {
        ?>
           <li><?php echo __('Número de páginas'); ?>: <?php echo $documento['num_paginas']; ?></li>
        <?php   
        }
        if (isset($documento['tradutor']) && $documento['tradutor']) {
        ?>
           <li><?php echo __('Tradutor'); ?>: <?php echo $documento['tradutor']; ?></li>
        <?php   
        }
        if (isset($documento['CDU']) && $documento['CDU']) {
        ?>
           <li><?php echo __('CDU'); ?>: <?php echo $documento['CDU']; ?></li>
        <?php   
        }
        
	    //Comprovante de Adaptação
        if (isset($documento['comprovante']) && $documento['comprovante']) {
        ?>
           <li><?php echo __('Comprovante'); ?>: <?php echo $documento['comprovante']; ?></li>
        <?php   
        }
        if (isset($documento['instituicao']) && $documento['instituicao']) {
        ?>
           <li><?php echo __('Instituição'); ?>: <?php echo $documento['instituicao']; ?></li>
        <?php   
        }
        
        //Comprovante de Crítica
        if (isset($documento['artigo']) && $documento['artigo']) {
        ?>
           <li><?php echo __('Artigo'); ?>: <?php echo $documento['artigo']; ?></li>
        <?php   
        }
        if (isset($documento['local_realizacao']) && $documento['local_realizacao']) {
        ?>
           <li><?php echo __('Local de realização'); ?>: <?php echo $documento['local_realizacao']; ?></li>
        <?php   
        }
        if (isset($documento['organizador']) && $documento['organizador']) {
        ?>
           <li><?php echo __('Organizador'); ?>: <?php echo $documento['organizador']; ?></li>
        <?php   
        }
        if (isset($documento['tipo_publicacao']) && $documento['tipo_publicacao']) {
        ?>
           <li><?php echo __('Tipo de publicação'); ?>: <?php echo $documento['tipo_publicacao']; ?></li>
        <?php   
        }
        if (isset($documento['autor_capitulo']) && $documento['autor_capitulo']) {
        ?>
           <li><?php echo __('Autor do capítulo'); ?>: <?php echo $documento['autor_capitulo']; ?></li>
        <?php   
        }
        if (isset($documento['titulo_capitulo']) && $documento['titulo_capitulo']) {
        ?>
           <li><?php echo __('Título do capítulo'); ?>: <?php echo $documento['titulo_capitulo']; ?></li>
        <?php   
        }
        if (isset($documento['pag_inicial_capitulo']) && $documento['pag_inicial_capitulo']) {
        ?>
           <li><?php echo __('Página inicial do capítulo'); ?>: <?php echo $documento['pag_inicial_capitulo']; ?></li>
        <?php   
        }
        if (isset($documento['pag_final_capitulo']) && $documento['pag_final_capitulo']) {
        ?>
           <li><?php echo __('Página final do capítulo'); ?>: <?php echo $documento['pag_final_capitulo']; ?></li>
        <?php   
        }
        if (isset($documento['pag_inicial_artigo']) && $documento['pag_inicial_artigo']) {
        ?>
           <li><?php echo __('Página inicial do artigo'); ?>: <?php echo $documento['pag_inicial_artigo']; ?></li>
        <?php   
        }
        if (isset($documento['pag_final_artigo']) && $documento['pag_final_artigo']) {
        ?>
           <li><?php echo __('Página final do artigo'); ?>: <?php echo $documento['pag_final_artigo']; ?></li>
        <?php   
        }
        if (isset($documento['congresso']) && $documento['congresso']) {
        ?>
           <li><?php echo __('Congresso'); ?>: <?php echo $documento['congresso']; ?></li>
        <?php   
        }
        
	    //Comprovante de Edição
        if (isset($documento['pag_inicial']) && $documento['pag_inicial']) {
        ?>
           <li><?php echo __('Página inicial'); ?>: <?php echo $documento['pag_inicial']; ?></li>
        <?php   
        }
        if (isset($documento['pag_final']) && $documento['pag_final']) {
        ?>
           <li><?php echo __('Página final'); ?>: <?php echo $documento['pag_final']; ?></li>
        <?php   
        }
        
	    //Correspondência
        if (isset($documento['destinatario']) && $documento['destinatario']) {
        ?>
           <li><?php echo __('Destinatário'); ?>: <?php echo $documento['destinatario']; ?></li>
        <?php   
        }
        
        //Editorial
        if (isset($documento['local']) && $documento['local']) {
        ?>
           <li><?php echo __('Local'); ?>: <?php echo $documento['local']; ?></li>
        <?php   
        }
        
        //Memorabilia
        if (isset($documento['num_livro_ata']) && $documento['num_livro_ata']) {
        ?>
           <li><?php echo __('Número do livro Ata'); ?>: <?php echo $documento['num_livro_ata']; ?></li>
        <?php   
        }
        if (isset($documento['evento']) && $documento['evento']) {
        ?>
           <li><?php echo __('Evento'); ?>: <?php echo $documento['evento']; ?></li>
        <?php   
        }
        if (isset($documento['promocao']) && $documento['promocao']) {
        ?>
           <li><?php echo __('Promoção'); ?>: <?php echo $documento['promocao']; ?></li>
        <?php   
        }
        if (isset($documento['tipo_publicacao']) && $documento['tipo_publicacao']) {
        ?>
           <li><?php echo __('Tipo de publicação'); ?>: <?php echo $documento['tipo_publicacao']; ?></li>
        <?php   
        }
        
        //Obra
        if (isset($documento['sigla']) && $documento['sigla']) {
        ?>
           <li><?php echo __('Sigla'); ?>: <?php echo $documento['sigla']; ?></li>
        <?php   
        }
        
        //Originais
        if (isset($documento['pseudonimo']) && $documento['pseudonimo']) {
        ?>
           <li><?php echo __('Pseudônimo'); ?>: <?php echo $documento['pseudonimo']; ?></li>
        <?php   
        }
        
        //Publicação na Imprensa
        if (isset($documento['data_inicial']) && $documento['data_inicial']) {
        ?>
           <li><?php echo __('Data inicial'); ?>: <?php echo $documento['data_inicial']; ?></li>
        <?php   
        }
        if (isset($documento['data_final']) && $documento['data_final']) {
        ?>
           <li><?php echo __('Data final'); ?>: <?php echo $documento['data_final']; ?></li>
        <?php   
        }
        if (isset($documento['num_fasciculo']) && $documento['num_fasciculo']) {
        ?>
           <li><?php echo __('Número do fascículo'); ?>: <?php echo $documento['num_fasciculo']; ?></li>
        <?php   
        }
        
        //Vida
        if (isset($documento['indice_assunto']) && $documento['indice_assunto']) {
        ?>
           <li><?php echo __('Índice do assunto'); ?>: <?php echo $documento['indice_assunto']; ?></li> 
        <?php   
        }
	}
	?>
	</ul>
	
	<?php 
	if ($fontes) {
	?>
	<h3><?php echo __('Fontes'); ?></h3>
	<ul>
	<?php
	foreach ($fontes as $fonte) {
	?>
		<li><?php echo $fonte['descricao']; ?></li>
	<?php
	}
	?>
	</ul>
	<?php
	}
	?>
	
    <?php
    if (sizeof($midias) > 0) {
    ?>
        <p><h4><a href="<?php echo DOCUMENTOS_URI . '?action=midias&id=' . $id; ?>" class="visualizar_obra">
        <?php echo __('Documento disponível para download'); ?></a></h4></p>
    <?php
    }
    else {
    ?>
    <br />
    <?php	
    }
    ?>
    
	<?php
    if ($criticas_obra) {
    ?>
		<hr />
		<h3><?php echo __('Críticas sobre a Obra'); ?></h3>
        <table id="criticas">
	        <thead>
	            <tr>
  	                <th><?php echo __('Título'); ?></th>
                    <th><?php echo __('Autor da crítica'); ?></th>
		        </tr>
		    </thead>
		    <tbody>
		    <?php
		    foreach ($criticas_obra as $critica_obra) {
		    ?>
		    	<tr id="<?php echo $critica_obra['id']; ?>">
		        	<td><?php echo $critica_obra['titulo']; ?></td>
		            <td><?php echo $critica_obra['autor_critica']; ?></td>
		        </tr>
		    <?php
		    }
		    ?>
       		</tbody>
    	</table>
    <?php
    }
    
    if ($fatos_historicos) {
    ?>
	    <hr />
	    <h3><?php echo __('Fatos históricos associados'); ?></h3>
		<table id="fatos_historicos">
			<thead>
		    	<tr>
		        	<th><?php echo __('Ano do início'); ?></th>
		        	<th><?php echo __('Ano do fim'); ?></th>
		            <th><?php echo __('Descrição'); ?></th>
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
     <h3><?php echo __('Comentários'); ?> (<span id="comment_count"><?php echo sizeof($comentarios); ?></span>)</h3>
     <?php 
     if (Auth::check()) {
     ?>
     <form id="comment_form" method="post" action="?action=add_comment&id=<?php echo $id; ?>">
     	<p><?php echo __('Insira seu comentário sobre este documento'); ?></p>
     	<input type="hidden" id="id" name="id" value="<?php echo $id; ?>" />
     	<label for="title"><?php echo __('Título'); ?></label>
     	<input type="text" id="title" name="title" />
     	<label for="comment"><?php echo __('Conteúdo'); ?></label>
     	<textarea id="comment" name="comment" maxlenght="4000"></textarea>
     	<input type="submit" value="<?php echo __('Enviar'); ?>" />
     	<span id="status"></span>
     </form>
     <?php
     }
     else {
     ?>
     <br />
     <h4><?php echo __('Faça login para poder comentar'); ?></h4>
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
			    <button class="reply"><?php echo __('Responder'); ?></button>
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
$(document).ready(function(){
	<?php if ($criticas_obra) { ?>
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
	            var uri = '<?php  echo CRITICAS_OBRA_URI; ?>?id=' + id;
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