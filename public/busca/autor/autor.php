<?php
/**
 * Formulário para busca por Autor
 */
require_once (dirname ( __FILE__ ) . '/../../../application/controllers/ControleAutores.php');
require_once (dirname ( __FILE__ ) . '/../../../application/controllers/ControleFontes.php');

$controle_autores = ControleAutores::getInstance ();
$controle_fontes = ControleFontes::getInstance ();

$regiao_nasc = $controle_autores->getDistinctRegNasc ();
$paises_nasc = $controle_autores->getDistinctPaisNasc ();
$regiao_morte = $controle_autores->getDistinctRegMorte ();
$paises_morte = $controle_autores->getDistinctPaisMorte ();

$fontes = $controle_fontes->get ();

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
<form action="<?php echo BUSCA_URI; ?>autor/" method="post"
	class="inline">	
	<fieldset>
		<legend><?php echo __('Nome do autor');?></legend>
		<p>
			<label for="nome"><?php echo __('Nome');?></label> <input type="text"
				id="nome" name="nome" style="width: 570px"
				title="<?php echo __('Exemplo: Machado de Assis');?>" /> <select
				id="forma_busca" name="forma_busca" style="width: 180px"
				disabled="disabled">
				<option value="1"><?php echo __('Qualquer palavra');?></option>
				<option value="2" selected="selected"><?php echo __('Frase exata');?></option>
			</select>
		</p>
	</fieldset>
	
	<fieldset>
		<legend><?php echo __('Nascimento');?></legend>
		<p id="ano_nascimento">
			<label for="periodo_nascimento_tipo"><?php echo __('Período de nascimento');?></label>
			<select id="periodo_nascimento_tipo" name="periodo_nascimento_tipo"
				style="width: 100px">
				<option value="ano" selected="selected"><?php echo __('Ano');?></option>
				<option value="seculo"><?php echo __('Século');?></option>
			</select>
		<?php echo __('de');?>
		<input type="text" id="ano_nascimento_inicio"
				name="ano_nascimento_inicio" style="width: 90px" /> <select
				id="seculo_nascimento_inicio" name="seculo_nascimento_inicio"
				style="width: 102px; display: none">
				<option value=""><?php echo __('Selecione');?></option>     
            <?php
												foreach ( $seculos as $seculo ) {
													?>
            <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
            <?php
												}
												?>
        </select>
		<?php echo __('até');?>
		<input type="text" id="ano_nascimento_fim" name="ano_nascimento_fim"
				style="width: 90px" /> <select id="seculo_nascimento_fim"
				name="seculo_nascimento_fim" style="width: 102px; display: none">
				<option value=""><?php echo __('Selecione');?></option>     
            <?php
												foreach ( $seculos as $seculo ) {
													?>
            <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
            <?php
												}
												?>
        </select>
		</p>
		<div class='clear'></div>
		<p>
			<label for="cidade_nasc"><?php echo __('Cidade de nascimento');?></label>
			<input type="text" id="cidade_nasc" name="cidade_nasc" style="width: 225px" />
		</p>

		<p>
			<label for="regiao_nascimento"><?php echo __('Estado/Região de Nascimento');?></label>
			<select id="regiao_nasc" name="regiao_nasc">
				<option value=""></option>
			 <?php
				foreach ( $regiao_nasc as $regiao ) {
					if ($regiao['regiao_id']) {
					?>
                <option value="<?php echo $regiao['regiao_id']; ?>"><?php echo $regiao['regiao_nasc']; ?></option>
                <?php
				} }
				?>
    		</select>
		
		
		<p>
			<label for="pais_nascimento"><?php echo __('País de Nascimento');?></label>
			<select id="pais_nasc" name="pais_nasc">
				<option value=""></option>
 			<?php
				foreach ( $paises_nasc as $pais ) {
					?>
                <option value="<?php echo $pais['pais_id']; ?>"><?php echo $pais['pais_nasc']; ?></option>
                <?php
				}
				?>
			</select>
		</p>
		<p>
			<label for="catarinense"><br />
			<input type="checkbox" id="catarinense" name="catarinense" />
				Catarinense</label>
		</p>
		
	</fieldset>
	<fieldset>
		<legend><?php echo __('Falecimento');?></legend>
		<p>
			<label for="periodo_morte_tipo"><?php echo __('Período da morte');?></label>
			<select id="periodo_morte_tipo" name="periodo_morte_tipo"
				style="width: 100px">
				<option value="ano" selected="selected"><?php echo __('Ano');?></option>
				<option value="seculo"><?php echo __('Século');?></option>
			</select>
		<?php echo __('de');?>
		<input type="text" id="ano_morte_inicio" name="ano_morte_inicio"
				style="width: 90px" /> <select id="seculo_morte_inicio"
				name="seculo_morte_inicio" style="width: 102px; display: none">
				<option value=""><?php echo __('Selecione');?></option>     
            <?php
												foreach ( $seculos as $seculo ) {
													?>
            <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
            <?php
												}
												?>
        </select>
		<?php echo __('até');?>
		<input type="text" id="ano_morte_fim" name="ano_morte_fim"
				style="width: 90px" /> <select id="seculo_morte_fim"
				name="seculo_morte_fim" style="width: 102px; display: none">
				<option value=""><?php echo __('Selecione');?></option>     
            <?php
												foreach ( $seculos as $seculo ) {
													?>
            <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
            <?php
												}
												?>
        </select>
		</p>
		<div class='clear'></div>
		<p>
			<label for="loc_morte"><?php echo __('Cidade do falecimento');?></label>
			<input type="text" id="cidade_morte" name="cidade_morte"
				style="width: 225px" />
		</p>

		<p>
			<label for="regiao_morte"><?php echo __('Estado/Região de Falecimento');?></label>
			<select id="regiao_morte" name="regiao_morte">
				<option value=""></option>
			 <?php
				foreach ( $regiao_morte as $regiao ) {
					if ($regiao['regiao_id']) {
					?>
                <option value="<?php echo $regiao['regiao_id']; ?>"><?php echo $regiao['regiao_morte']; ?></option>
                <?php
				} }
				?>
    		</select>
		<p>
			<label for="pais_falecimento"><?php echo __('País de Falecimento');?></label>
			<select id="pais_morte" name="pais_morte">
				<option value="" selected='selected'></option>
 			<?php
				foreach ( $paises_morte as $pais ) {
					?>
                <option value="<?php echo $pais['pais_id']; ?>"><?php echo $pais['pais_morte']; ?></option>
                <?php
				}
				?>
			</select>
		</p>

	</fieldset>
	<fieldset>
		<legend>Outros</legend>
		<p>
			<label for="descricao"><?php echo __('Descrição');?><span><?php echo __('Exemplo: Poeta, Romancista, Tradutor, Biógrafo, Trovador, Músico, Jornalista');?></span></label>
			<input type="text" id="descricao" name="descricao"
				style="width: 753px" />
		</p>
		<p>
			<label for="fonte"><?php echo __('Fonte');?></label> <select
				id="fonte" name="fonte" style="width: 765px">
				<option value=""><?php echo __('Todas');?></option>      
            <?php
												foreach ( $fontes as $fonte ) {
													if ($fonte ['id'] == 1 || $fonte ['id'] == 3 || $fonte ['id'] == 36) {
														$fonte ['descricao'] = strlen ( $fonte ['descricao'] ) > 130 ? substr ( $fonte ['descricao'], 0, 130 ) . '...' : $fonte ['descricao'];
														?>
            <option value="<?php echo $fonte['id']; ?>"><?php echo $fonte['descricao']; ?></option>
            <?php
													}
												}
												?>
        </select>
		</p>
	</fieldset>
		<p>
		<input id="autPesquisar" name=""autPesquisar"" type="submit"
			value="<?php echo __('Pesquisar');?>" />
	</p>
	<div id="status"></div>
	<div class="clear"></div>
</form>
<script type="text/javascript">
$(function() {

	$('#autPesquisar').click(function() {
		if ($('#forma_busca').val() == '2' && $('#nome').val() && $('#nome').val().slice(0,1) != '"' && $('#nome').val().slice(-1) != '"') {
			$('#nome').val('"' + $('#nome').val() + '"');
		}
	});

	function check_search_type() {
		if ($("#nome").val()) {
			$('#forma_busca').attr('disabled', false);
		}
		else {
			$('#forma_busca').attr('disabled', true);
		}
		
	}
	
	var inp = $("#nome")[0];
	if ("onpropertychange" in inp) {
	    inp.attachEvent('onpropertychange',$.proxy(function () {
	        if (event.propertyName == "value")
	            check_search_type();
	    }, inp));
	}
	else {
	    inp.addEventListener("input", function () {
	    	check_search_type();
	    }, false);
	}

	var inp = $("#nome")[0];
	if ("onpropertychange" in inp) {
	    inp.attachEvent('onpropertychange',$.proxy(function () {
	        if (event.propertyName == "value")
	            check_search_type();
	    }, inp));
	}
	else {
	    inp.addEventListener("input", function () {
	    	check_search_type();
	    }, false);
	}
	
    $('#periodo_nascimento_tipo').change(function() {
        if ($(this).val() == 'ano') {
            $('#seculo_nascimento_inicio').hide();
            $('#seculo_nascimento_fim').hide();
            $('#ano_nascimento_inicio').show();
            $('#ano_nascimento_fim').show();
        }
        else {
            $('#ano_nascimento_inicio').hide();
            $('#ano_nascimento_fim').hide();
            $('#seculo_nascimento_inicio').show();
            $('#seculo_nascimento_fim').show();
        }
    })

    $('#periodo_morte_tipo').change(function() {
        if ($(this).val() == 'ano') {
            $('#seculo_morte_inicio').hide();
            $('#seculo_morte_fim').hide();
            $('#ano_morte_inicio').show();
            $('#ano_morte_fim').show();
        }
        else {
            $('#ano_morte_inicio').hide();
            $('#ano_morte_fim').hide();
            $('#seculo_morte_inicio').show();
            $('#seculo_morte_fim').show();
        }
    })
});
</script>
