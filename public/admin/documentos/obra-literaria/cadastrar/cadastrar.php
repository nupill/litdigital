<?php 
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}

require_once(APPLICATION_PATH . '/controllers/ControleDocumentos.php');
$controle_documentos = ControleDocumentos::getInstance();

$generos = $controle_documentos->get_generos(DOCUMENTOS_OBRA_LITERARIA_ID);
$categorias = $controle_documentos->get_categorias(DOCUMENTOS_OBRA_LITERARIA_ID);
//$fontes = $controle_documentos->get_fontes(DOCUMENTOS_OBRA_LITERARIA_ID);
$idiomas = $controle_documentos->get_idiomas();

$seculos = array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX', 'XXI');
$seculos = array_reverse($seculos);
?>
<div id="content">
    <h2>
        <a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
        <a href="<?php echo ADMIN_DOCUMENTOS_URI; ?>"><?php echo __('Documentos');?></a> &raquo;
        <a href="<?php echo ADMIN_DOCUMENTOS_OBRA_LITERARIA_URI; ?>"><?php echo __('Obra Literária');?></a> &raquo;
        <?php echo __('Cadastrar');?>
     </h2>
    <br />
    <form id="add" action="<?php echo ADMIN_DOCUMENTOS_OBRA_LITERARIA_URI; ?>?action=add" method="post" class="inline">
        <fieldset>
            <legend><?php echo __('Títulos');?></legend>
            <p><label for="titulo"><?php echo __('Título');?>*</label>
            <textarea id="titulo" name="titulo" style="width: 780px; height: 40px"></textarea></p>
            <p><label for="subtitulo"><?php echo __('Subtítulo');?></label>
            <input type="text" id="subtitulo" name="subtitulo" style="width: 780px" /></p>
            <p><label for="titulo_alternativo"><?php echo __('Título alternativo');?></label>
            <input type="text" id="titulo_alternativo" name="titulo_alternativo" style="width: 780px" /></p>
        </fieldset>
        <fieldset>
            <legend><?php echo __('Autores');?></legend>
            <p><label for="fonte"><?php echo __('Autores');?>*</label>
	            <input type="text" id="autor" name="autor" title="<?php echo __('Digite o nome do autor, selecione um dos resultados encontrados e clique no botão ao lado para adicioná-lo à lista');?>" style="width: 780px" />
	            <input type="hidden" id="autor_id" name="autor_id" />
	            <button id="add_autor" disabled="disabled" class="disabled" title="<?php echo __('Adicionar à lista');?>">+</button>
	            <div class="clear"></div>
	            <select id="autores" name="autores[]" multiple="multiple" size="5" style="width: 792px; margin-right: 4px" class="left"></select>
	            <button id="rem_autor" disabled="disabled" class="disabled" title="<?php echo __('Remover da lista');?>">-</button>
            </p>
        </fieldset>        
        <fieldset>
            <legend><?php echo __('Classificação');?></legend>
            <p><label for="genero"><?php echo __('Gênero literário');?>*</label>
             <select id="genero" name="genero" style="width: 385px">
             		<option value=""><?php echo __("Selecione");?></option>
                    <?php
                    if ($generos) {
                        foreach ($generos as $genero) {
                    ?>
                    <option value="<?php echo $genero['id']; ?>"><?php echo $genero['nome']; ?></option>
                    <?php
                        }
                    }
                    ?>
                </select>                <input type="hidden" id="genero_id" name="genero_id" />
                <button id="add_genero" disabled="disabled" class="disabled" title="<?php echo __('Adicionar à lista');?>">+</button>
                <div class="clear"></div>
                <select id="generos" name="generos[]" multiple="multiple" size="5" style="width: 792px; margin-right: 4px" class="left"></select>
                <button id="rem_genero" disabled="disabled" class="disabled" title="<?php echo __('Remover da lista');?>">-</button>
            </p>
        </fieldset>
        <fieldset>
            <legend><?php echo __('Datas');?></legend>
            <p><label for="ano_producao"><?php echo __('Ano de produção (início)');?></label>
            <input type="text" id="ano_producao" name="ano_producao" style="width: 170px" /></p>
            <p><label for="ano_producao_fim"><?php echo __('Ano de produção (fim)');?></label>
            <input type="text" id="ano_producao_fim" name="ano_producao_fim" style="width: 170px" /></p>
            <p><label for="ano_publicacao_inicio"><?php echo __('Ano de publicação (início)');?></label>
            <input type="text" id="ano_publicacao_inicio" name="ano_publicacao_inicio" style="width: 170px" /></p>
            <p><label for="ano_publicacao_fim"><?php echo __('Ano de publicação (fim)');?></label>
            <input type="text" id="ano_publicacao_fim" name="ano_publicacao_fim" style="width: 170px" /></p>
            <div class="clear"></div>
            <p><label for="seculo_producao"><?php echo __('Século de Produção');?></label>
            <select id="seculo_producao" name="seculo_producao" style="width: 182px">
                <option value=""><?php echo __('Selecione');?></option>     
                <?php 
                foreach ($seculos as $seculo) {
                ?>
                <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
                <?php
                }
                ?>
            </select></p>
            <p><label for="seculo_publicacao"><?php echo __('Século de Publicação');?></label>
            <select id="seculo_publicacao" name="seculo_publicacao" style="width: 182px">
                <option value=""><?php echo __('Selecione');?></option>     
                <?php 
                foreach ($seculos as $seculo) {
                ?>
                <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
                <?php
                }
                ?>
            </select></p>
            <p><label for="seculo_encenacao"><?php echo __('Século de Encenação');?></label>
            <select id="seculo_publicacao" name="seculo_encenacao" style="width: 182px">
                <option value=""><?php echo __('Selecione');?></option>     
                <?php 
                foreach ($seculos as $seculo) {
                ?>
                <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
                <?php
                }
                ?>
            </select></p>
        </fieldset>
        <fieldset>
            <legend><?php echo __('Encenação');?></legend>
            <p><label for="ano_encenacao"><?php echo __('Ano');?></label>
            <input type="text" id="ano_encenacao" name="ano_encenacao" /></p>
            <p><label for="local_encenacao"><?php echo __('Local');?></label>
            <input type="text" id="local_encenacao" name="local_encenacao" /></p>
        </fieldset>
        <fieldset>
            <legend><?php echo __('Outras informações');?></legend>
            <p><label for="fonte"><?php echo __('Fontes');?>*</label>
	            <input type="text" id="fonte" name="fonte" title="<?php echo __('Digite o nome da fonte, selecione um dos resultados encontrados e clique no botão ao lado para adicioná-la à lista');?>" style="width: 780px" />
	            <input type="hidden" id="fonte_id" name="fonte_id" />
	            <button id="add_fonte" disabled="disabled" class="disabled" title="<?php echo __('Adicionar à lista');?>">+</button>
	            <div class="clear"></div>
	            <select id="fontes" name="fontes[]" multiple="multiple" size="5" style="width: 792px; margin-right: 4px" class="left"></select>
	            <button id="rem_fonte" disabled="disabled" class="disabled" title="<?php echo __('Remover da lista');?>">-</button>
            </p>            
            <div class="clear"></div>
                     
            <p><label for="editora"><?php echo __("Editoras");?>*</label>
	            <input type="text" id="editora" name="editora" title="<?php echo __("Digite o nome da editora, selecione um dos resultados encontrados e clique no botão ao lado para adicioná-la à lista");?>" style="width: 780px" />
	            <input type="hidden" id="editora_id" name="editora_id" />
	            <button id="add_editora" disabled="disabled" class="disabled" title="<?php echo __("Adicionar à lista");?>">+</button>
	            <div class="clear"></div>
	            <select id="editoras" name="editoras[]" multiple="multiple" size="5" style="width: 792px; margin-right: 4px" class="left"></select>
	            <button id="rem_editora" disabled="disabled" class="disabled" title="<?php echo __("Remover da lista");?>">-</button>
            </p>    
                     
            <p><label for="idioma"><?php echo __('Idiomas');?>*</label>
				<select id="idioma" name="idioma" style="width: 385px">
             		<option value=""><?php echo __("Selecione");?></option>
                    <?php
                    if ($idiomas) {
                        foreach ($idiomas as $idioma) {
                    ?>
                    <option value="<?php echo $idioma['id']; ?>"><?php echo $idioma['descricao']; ?></option>
                    <?php
                    
                        }
                    }
                    ?>
                </select>                
                                <input type="hidden" id="idioma_id" name="idioma_id" />
                <button id="add_idioma" disabled="disabled" class="disabled" title="<?php echo __('Adicionar à lista');?>">+</button>
                <div class="clear"></div>
                <select id="idiomas" name="idiomas[]" multiple="multiple" size="5" style="width: 792px; margin-right: 4px" class="left"></select>
                <button id="rem_idioma" disabled="disabled" class="disabled" title="<?php echo __('Remover da lista');?>">-</button>
            </p>
            <div class="clear"></div>
            <p><label for="personagens"><?php echo __('Personagens');?></label>
            <input type="text" id="personagens" name="personagens" /></p>
            <p><label for="palavra_chave"><?php echo __('Palavra-chave');?></label>
            <input type="text" id="palavra_chave" name="palavra_chave" /></p>
            <p><label for="acervo"><?php echo __("Acervo");?></label>
	            <input type="text" id="acervo" name="acervo" title="<?php echo __("Digite o nome do acervo, selecione um dos resultados encontrados e clique no botão ao lado para adicioná-la à lista");?>" style="width: 780px" />
	            <input type="hidden" id="acervo_id" name="acervo_id" />
            </p>            
            <div class="clear"></div>
            <p><label for="descricao"><?php echo __('Descrição');?></label>
            <textarea id="descricao" name="descricao" style="width: 785px"></textarea></p>
        </fieldset>
        <fieldset>
            <legend><?php echo __('Mídias');?></legend>
            <div id="arquivo"></div>
            <div class="clear"></div>
        </fieldset>
        <p><input type="submit" value="<?php echo __('Cadastrar');?>" disabled="disabled" class="disabled" /></p>
        <div id="accessibility_form">
            <a href="javascript:scrollUp()"><?php echo __('Ir ao topo');?></a> | 
            <a href="javascript:history.go(-1)"><?php echo __('Voltar à página anterior');?></a>
        </div>
        <div id="status"></div>
        <div class="clear"></div>
        <em>* <?php echo __('Campos obrigatórios');?>
        </em>
    </form>
</div>