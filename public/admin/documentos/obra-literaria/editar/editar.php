<?php
if (! Auth::check ( array (
		PAPEL_ADMINISTRADOR_ID,
		PAPEL_CADASTRADOR_ID 
) )) {
	exit ( __ ( 'Acesso negado' ) );
}

require_once (APPLICATION_PATH . '/controllers/ControleDocumentos.php');
require_once(APPLICATION_PATH . '/controllers/ControleEditoras.php');
$controle_editoras = ControleEditoras::getInstance();


$id = isset ( $_GET ['id'] ) ? $_GET ['id'] : '';
if (! $id) {
	exit ( __ ( 'ID não especificado' ) );
}
$controle_documentos = ControleDocumentos::getInstance ();
$documento = $controle_documentos->get ( $id );
if (! $documento) {
	exit ( __ ( 'Registro não encontrado. ID inválido' ) );
}
$documento = $documento [0];

$generosAtuais = $controle_documentos->get_generos_new ( $id, array (
		'id',
		'nome' 
) );
$generos = $controle_documentos->get_generos ( DOCUMENTOS_OBRA_LITERARIA_ID );

$idiomasAtuais = $controle_documentos->get_idiomas_new ( $id, array (
		'id',
		'descricao' 
) );
$idiomas = $controle_documentos->get_idiomas ();
$acervo = $controle_documentos->get_acervo ( $id, array (
		'id',
		'descricao' 
) );

$categorias = $controle_documentos->get_categorias ( DOCUMENTOS_OBRA_LITERARIA_ID );
$editoras = $controle_documentos->get_editoras($id);

$autores = $controle_documentos->get_autores ( $id, array (
		'id',
		'nome_completo' 
) );
$fontes = $controle_documentos->get_fontes ( $id );
$midias = $controle_documentos->get_midias ( $id, array (
		'titulo',
		'descricao',
		'nome_arquivo',
		'tamanho',
		'fonte' 
) );

$seculos = array (
		'I',
		'II',
		'III',
		'IV',
		'V',
		'VI',
		'VII',
		'VIII',
		'IX',
		'X',
		'XI',
		'XII',
		'XIII',
		'XIV',
		'XV',
		'XVI',
		'XVII',
		'XVIII',
		'XIX',
		'XX',
		'XXI' 
);
$seculos = array_reverse ( $seculos );
?>
<div id="content">
	<h2>
		<a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a>
		&raquo; <a href="<?php echo ADMIN_DOCUMENTOS_URI; ?>"><?php echo __('Documentos');?></a>
		&raquo; <a href="<?php echo ADMIN_DOCUMENTOS_OBRA_LITERARIA_URI; ?>"><?php echo __('Obra Literária');?></a> &raquo;
        <?php echo __('Editar');?>
     </h2>
	<br />
	<form name="removeDoc" id="removeDoc"
		action="<?php echo ADMIN_DOCUMENTOS_OBRA_LITERARIA_URI; ?>?action=removeDoc&ids[]=<?php echo $id; ?>"
		method="post" class="inline">
		<input type="hidden" name="ids[]" value="<?php echo $id; ?>">
	</form>
	<form name="formObra" id="update"
		action="<?php echo ADMIN_DOCUMENTOS_OBRA_LITERARIA_URI; ?>?action=update&id=<?php echo $id; ?>"
		method="post" class="inline">
		<fieldset>
			<legend><?php echo __('Títulos');?></legend>
			<p>
				<label for="titulo"><?php echo __('Título');?>*</label>
				<textarea id="titulo" name="titulo"
					style="width: 780px; height: 40px"><?php echo $documento['titulo']; ?></textarea>
			</p>
			<p>
				<label for="subtitulo"><?php echo __('Subtítulo');?></label> <input
					type="text" id="subtitulo" name="subtitulo" style="width: 780px"
					value="<?php echo $documento['subtitulo']; ?>" />
			</p>
			<p>
				<label for="titulo_alternativo"><?php echo __('Título alternativo');?></label>
				<input type="text" id="titulo_alternativo" name="titulo_alternativo"
					style="width: 780px"
					value="<?php echo $documento['titulo_alternativo']; ?>" />
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Autores');?></legend>
			<p>
				<label for="fonte"><?php echo __('Autores');?>*</label> <input
					type="text" id="autor" name="autor"
					title="<?php echo __('Digite o nome do autor, selecione um dos resultados encontrados e clique no botão ao lado para adicioná-lo à lista');?>"
					style="width: 780px" /> <input type="hidden" id="autor_id"
					name="autor_id" />
				<button id="add_autor" disabled="disabled" class="disabled"
					title="<?php echo __('Adicionar à lista');?>">+</button>
			
			
			<div class="clear"></div>
			<select id="autores" name="autores[]" multiple="multiple" size="5"
				style="width: 792px; margin-right: 4px" class="left">
            <?php
												if ($autores) {
													foreach ( $autores as $autor ) {
														?>    
                <option value="<?php echo $autor['id']; ?>"><?php echo $autor['nome_completo']; ?></option>
            <?php
													}
												}
												?>
            </select>
			<button id="rem_autor" disabled="disabled" class="disabled"
				title="<?php echo __('Remover da lista');?>">-</button>
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Classificação');?></legend>
			<p>
				<label for="genero"><?php echo __('Gênero literário');?>*</label> <select
					id="genero" name="genero" style="width: 385px">
					<option value=""><?php echo __("Selecione");?></option>
                    <?php
																				if ($generos) {
																					foreach ( $generos as $genero ) {
																						?>
                    <option value="<?php echo $genero['id']; ?>"><?php echo $genero['nome']; ?></option>
                    <?php
																					}
																				}
																				?>
                </select> <input type="hidden" id="genero_id"
					name="genero_id" />
				<button id="add_genero" disabled="disabled" class="disabled"
					title="<?php echo __('Adicionar à lista');?>">+</button>
			
			
			<div class="clear"></div>
			<select id="generos" name="generos[]" multiple="multiple" size="5"
				style="width: 792px; margin-right: 4px" class="left">
                <?php
																if ($generosAtuais) {
																	foreach ( $generosAtuais as $genero ) {
																		?>    
                <option value="<?php echo $genero['id']; ?>"><?php echo $genero['nome'];?></option>
                <?php
																	}
																}
																?>
                </select>
			<button id="rem_genero" disabled="disabled" class="disabled"
				title="<?php echo __('Remover da lista');?>">-</button>
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Datas');?></legend>
			<p>
				<label for="ano_producao"><?php echo __('Ano de produção (início)');?></label>
				<input type="text" id="ano_producao" name="ano_producao"
					style="width: 170px"
					value="<?php echo $documento['ano_producao']; ?>" />
			</p>
			<p>
				<label for="ano_producao_fim"><?php echo __('Ano de produção (fim)');?></label>
				<input type="text" id="ano_producao_fim" name="ano_producao_fim"
					style="width: 170px"
					value="<?php echo $documento['ano_producao_fim']; ?>" />
			</p>
			<p>
				<label for="ano_publicacao_inicio"><?php echo __('Ano de publicação (início)');?></label>
				<input type="text" id="ano_publicacao_inicio"
					name="ano_publicacao_inicio" style="width: 170px"
					value="<?php echo $documento['ano_publicacao_inicio']; ?>" />
			</p>
			<p>
				<label for="ano_publicacao_fim"><?php echo __('Ano de publicação (fim)');?></label>
				<input type="text" id="ano_publicacao_fim" name="ano_publicacao_fim"
					style="width: 170px"
					value="<?php echo $documento['ano_publicacao_fim']; ?>" />
			</p>
			<div class="clear"></div>
			<p>
				<label for="seculo_producao"><?php echo __('Século de Produção');?></label>
				<select id="seculo_producao" name="seculo_producao"
					style="width: 182px">
					<option value=""><?php echo __('Selecione');?></option>     
                <?php
																foreach ( $seculos as $seculo ) {
																	?>
                <option value="<?php echo $seculo; ?>"
						<?php if ($documento['seculo_producao'] == $seculo) echo 'selected="selected"'; ?>><?php echo $seculo; ?></option>
                <?php
																}
																?>
            </select>
			</p>
			<p>
				<label for="seculo_publicacao"><?php echo __('Século de Publicação');?></label>
				<select id="seculo_publicacao" name="seculo_publicacao"
					style="width: 182px">
					<option value=""><?php echo __('Selecione');?></option>     
                <?php
																foreach ( $seculos as $seculo ) {
																	?>
                <option value="<?php echo $seculo; ?>"
						<?php if ($documento['seculo_publicacao'] == $seculo) echo 'selected="selected"'; ?>><?php echo $seculo; ?></option>
                <?php
																}
																?>
            </select>
			</p>
			<p>
				<label for="seculo_encenacao"><?php echo __('Século de Encenação');?></label>
				<select id="seculo_encenacao" name="seculo_encenacao"
					style="width: 182px">
					<option value=""><?php echo __('Selecione');?></option>     
                <?php
																foreach ( $seculos as $seculo ) {
																	?>
                <option value="<?php echo $seculo; ?>"
						<?php if ($documento['seculo_encenacao'] == $seculo) echo 'selected="selected"'; ?>><?php echo $seculo; ?></option>
                <?php
																}
																?>
            </select>
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Encenação');?></legend>
			<p>
				<label for="ano_encenacao"><?php echo __('Ano');?></label> <input
					type="text" id="ano_encenacao" name="ano_encenacao"
					value="<?php echo $documento['ano_encenacao']; ?>" />
			</p>
			<p>
				<label for="local_encenacao"><?php echo __('Local');?></label> <input
					type="text" id="local_encenacao" name="local_encenacao"
					value="<?php echo $documento['local_encenacao']; ?>" />
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Outras informações');?></legend>
			<p>
				<label for="fonte"><?php echo __('Fontes');?>*</label> <input
					type="text" id="fonte" name="fonte"
					title="<?php echo __('Digite o nome da fonte, selecione um dos resultados encontrados e clique no botão ao lado para adicioná-la à lista');?>"
					style="width: 780px" /> <input type="hidden" id="fonte_id"
					name="fonte_id" />
				<button id="add_fonte" disabled="disabled" class="disabled"
					title="<?php echo __('Adicionar à lista');?>">+</button>
			
			
			<div class="clear"></div>
			<select id="fontes" name="fontes[]" multiple="multiple" size="5"
				style="width: 792px; margin-right: 4px" class="left">
            <?php
												if ($fontes) {
													foreach ( $fontes as $fonte ) {
														?>    
                <option value="<?php echo $fonte['id']; ?>"><?php echo $fonte['descricao']; ?></option>
            <?php
													}
												}
												?>
            </select>
			<button id="rem_fonte" disabled="disabled" class="disabled"
				title="<?php echo __('Remover da lista');?>">-</button>
			</p>
			<div class="clear"></div>
			            
            <p><label for="editora"><?php echo __("Editoras");?>*</label>
                <input type="text" id="editora" name="editora" title="<?php echo __("Digite o nome da editora, selecione um dos resultados encontrados e clique no botão ao lado para adicioná-la à lista");?>" style="width: 780px" />
                <input type="hidden" id="editora_id" name="editora_id" />
                <button id="add_editora" disabled="disabled" class="disabled" title="<?php echo __("Adicionar à lista");?>">+</button>
                <div class="clear"></div>
                <select id="editoras" name="editoras[]" multiple="multiple" size="5" style="width: 792px; margin-right: 4px" class="left">
                    <?php
                    if ($editoras) {
                        foreach ($editoras as $editora) {
                    ?>
                    <option value="<?php echo $editora['id']; ?>"><?php echo $editora['nome'].", ".$controle_editoras->getLocal($editora['id']); ?></option>
                    <?php
                        }
                    }
                    ?>
                </select>
                <button id="rem_editora" disabled="disabled" class="disabled" title="<?php echo __("Remover da lista");?>">-</button>
            </p>
            
			<p>
				<label for="idioma"><?php echo __('Idiomas');?>*</label> <select
					id="idioma" name="idioma" style="width: 385px">
					<option value=""><?php echo __("Selecione");?></option>
                    <?php
																				if ($idiomas) {
																					foreach ( $idiomas as $idioma ) {
																						?>
                    <option value="<?php echo $idioma['id']; ?>"><?php echo $idioma['descricao']; ?></option>
                    <?php
																					}
																				}
																				?>
                </select> <input type="hidden" id="idioma_id"
					name="idioma_id" />
				<button id="add_idioma" disabled="disabled" class="disabled"
					title="<?php echo __('Adicionar à lista');?>">+</button>
			
			
			<div class="clear"></div>
			<select id="idiomas" name="idiomas[]" multiple="multiple" size="5"
				style="width: 792px; margin-right: 4px" class="left">
                <?php
																if ($idiomas) {
																	foreach ( $idiomasAtuais as $idioma ) {
																		?>    
                <option value="<?php echo $idioma['id']; ?>"><?php echo $idioma['descricao'];?></option>
                <?php
																	}
																}
																?>
                </select>
			<button id="rem_idioma" disabled="disabled" class="disabled"
				title="<?php echo __('Remover da lista');?>">-</button>
			</p>
			<div class="clear"></div>
			<p>
				<label for="personagens"><?php echo __('Personagens');?></label> <input
					type="text" id="personagens" name="personagens"
					value="<?php echo $documento['personagens']; ?>" />
			</p>
			<p>
				<label for="palavra_chave"><?php echo __('Palavra-chave');?></label>
				<input type="text" id="palavra_chave" name="palavra_chave"
					value="<?php echo $documento['palavra_chave']; ?>" />
			</p>
			<p><label for="acervo"><?php echo __("Acervo");?></label>
	            <input type="text" id="acervo" name="acervo"  style="width: 780px"
	            	<?php  
	            		if ($acervo) 
	            			echo " value="."\"".$acervo['descricao']."\""; 
	            	?>  
	         	/>
	            <input type="hidden" id="acervo_id" name="acervo_id"   
	            	<?php  
	            		if ($acervo) 
	            			echo " value="."\"".$acervo['id']."\""; 
	            	?> 
	            />
            </p>
			<div class="clear"></div>
			<p>
				<label for="descricao"><?php echo __('Descrição');?></label>
				<textarea id="descricao" name="descricao" style="width: 785px"><?php echo $documento['descricao']; ?></textarea>
			</p>
		</fieldset>
		<fieldset>
			<legend><?php echo __('Mídias');?></legend>
			<div id="arquivo"></div>
			<div class="clear"></div>
			<ul class="qq-upload-list">
                <?php
																if ($midias) {
																	foreach ( $midias as $midia ) {
																		?>
                <li><span class="qq-upload-title"><?php echo __('Arquivo');?></span>
					<a href="#" onclick="$(this).parent().remove(); return false"
					class="qq-upload-remove"><?php echo __('Remover');?></a> <span
					class="qq-upload-file"><a
						href="<?php echo DOCUMENTS_URI . $midia['nome_arquivo']; ?>"><?php echo __('Clique aqui para visualizar o documento');?></a></span>
					<span class="qq-upload-size"><?php echo format_bytes($midia['tamanho']); ?></span>
					<a href="#" class="qq-upload-replace"><?php echo __('Substituir arquivo');?></a>
					<input type="file" name="qqfile" class="qq-upload-replace-file" />
					<div class="clear"></div>
					<p>
						<label><?php echo __('Título');?></label> <input type="text"
							name="titulos_arquivos[]" style="width: 367px"
							value="<?php echo $midia['titulo']; ?>" />
					</p>
					<p>
						<label><?php echo __('Fonte');?></label> <input type="text"
							name="fontes_arquivos[]" style="width: 367px"
							value="<?php echo $midia['fonte']; ?>" />
					</p>
					<div class="clear"></div>
					<p>
						<label><?php echo __('Descrição');?></label> <input type="text"
							name="descricoes_arquivos[]" style="width: 767px"
							value="<?php echo $midia['descricao']; ?>" />
					</p> <input type="hidden" name="arquivos[]"
					value="<?php echo $midia['nome_arquivo']; ?>" /> <input
					type="hidden" name="arquivos_substituidos[]"
					class="qq-upload-replaced" value="" />
					<div class="clear"></div></li>
                <?php
																	}
																}
																?>
            </ul>
		</fieldset>
		<p>
			<input type="submit" value="<?php echo __('Atualizar');?>"
				disabled="disabled" class="disabled" /> <input type="button"
				onclick="removedoc(<?php echo $id; ?>)"
				value="<?php echo __('Excluir');?>" class="remove" />
		</p>
		<div id="accessibility_form">
			<a href="javascript:scrollUp()"><?php echo __('Ir ao topo');?></a> |
			<a href="javascript:history.go(-1)"><?php echo __('Voltar à página anterior');?></a>
		</div>
		<div id="status"></div>
		<div class="clear"></div>
		<em>* <?php echo __('Campos obrigatórios');?></em>
	</form>
</div>

