<?php
if (!Auth::check(array(PAPEL_ADMINISTRADOR_ID, PAPEL_CADASTRADOR_ID))) {
    exit(__('Acesso negado'));
}

require_once(APPLICATION_PATH . '/controllers/ControleDocumentos.php');
$controle_documentos = ControleDocumentos::getInstance();

$generos = $controle_documentos->get_generos(DOCUMENTOS_HISTORIA_EDITORIAL_ID);
$categorias = $controle_documentos->get_categorias(DOCUMENTOS_HISTORIA_EDITORIAL_ID);
//$fontes = $controle_documentos->get_fontes(DOCUMENTOS_HISTORIA_EDITORIAL_ID);
$idiomas = $controle_documentos->get_idiomas();
?>
<div id="content">
    <h2>
        <a href="<?php echo ADMIN_URI; ?>"><?php echo __('Área administrativa');?></a> &raquo;
        <a href="<?php echo ADMIN_DOCUMENTOS_URI; ?>"><?php echo __('Documentos');?></a> &raquo;
        <a href="<?php echo ADMIN_DOCUMENTOS_HISTORIA_EDITORIAL_URI; ?>"><?php echo __('História Editorial');?></a> &raquo;
        <?php echo __('Cadastrar');?>
    </h2>
    <br />
    <form id="add" action="<?php echo ADMIN_DOCUMENTOS_HISTORIA_EDITORIAL_URI; ?>?action=add" method="post" class="inline">
        <fieldset>
            <legend><?php echo __('Títulos');?></legend>
            <p><label for="titulo"><?php echo __('Título');?>*</label>
                <textarea id="titulo" name="titulo" style="width: 780px; height: 40px"></textarea></p>
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
            <p><label for="categoria"><?php echo __('Categoria');?></label>
                <select id="categoria" name="categoria" style="width: 385px">
                    <option value=""><?php echo __('Selecione');?></option>
                    <?php
                    if ($categorias) {
                        foreach ($categorias as $categoria) {
                    ?>
                    <option value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nome']; ?></option>
                    <?php
                        }
                    }
                    ?>
                </select></p>
                                                            <div class="clear"></div> 
                
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
            <p><label for="ano_producao"><?php echo __('Ano de produção');?></label>
                <input type="text" id="ano_producao" name="ano_producao" style="width: 170px" /></p>
        </fieldset>
        <fieldset>
            <legend><?php echo __('Localização');?></legend>
            <p><label for="acervo"><?php echo __("Acervo");?></label>
	            <input type="text" id="acervo" name="acervo" title="<?php echo __("Digite o nome do acervo, selecione um dos resultados encontrados e clique no botão ao lado para adicioná-la à lista");?>" style="width: 780px" />
	            <input type="hidden" id="acervo_id" name="acervo_id" />
            </p>            
            <p><label for="localizacao"><?php echo __('Localização');?></label>
                <input type="text" id="localizacao" name="localizacao" /></p>
            <p><label for="estado"><?php echo __('Estado de conservação');?></label>
                <select id="estado" name="estado">
                    <option value=""><?php echo __('Selecione');?></option>
                    <option><?php echo __('Muito bom');?></option>
                    <option><?php echo __('Bom');?></option>
                    <option><?php echo __('Regular');?></option>
                    <option><?php echo __('Péssimo');?></option>
                </select></p>
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
            <p><label for="id_material"><?php echo __('Código do material');?></label>
                <input type="text" id="id_material" name="id_material" /></p>
                        
            <p><label for="editora"><?php echo __("Editoras");?>*</label>
	            <input type="text" id="editora" name="editora" title="<?php echo __("Digite o nome da editora, selecione um dos resultados encontrados e clique no botão ao lado para adicioná-la à lista");?>" style="width: 780px" />
	            <input type="hidden" id="editora_id" name="editora_id" />
	            <button id="add_editora" disabled="disabled" class="disabled" title="<?php echo __("Adicionar à lista");?>">+</button>
	            <div class="clear"></div>
	            <select id="editoras" name="editoras[]" multiple="multiple" size="5" style="width: 792px; margin-right: 4px" class="left"></select>
	            <button id="rem_editora" disabled="disabled" class="disabled" title="<?php echo __("Remover da lista");?>">-</button>
            </p>    
            
            
            <p><label for="abrangencia"><?php echo __('Abrangência');?></label>
                <input type="text" id="abrangencia" name="abrangencia" /></p>
            <p><label for="direitos"><?php echo __('Direitos autorais');?></label>
                <input type="text" id="direitos" name="direitos" /></p>
            <p><label for="dimensao"><?php echo __('Dimensão');?></label>
                <input type="text" id="dimensao" name="dimensao" /></p>
            <div class="clear"></div>
            <p><label for="num_paginas"><?php echo __('Número de páginas');?></label>
                <input type="text" id="num_paginas" name="num_paginas" /></p>
            <p><label for="local"><?php echo __('Local');?></label>
                <input type="text" id="local" name="local" /></p>
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
        <em>* <?php echo __('Campos obrigatórios');?></em>
    </form>
</div>