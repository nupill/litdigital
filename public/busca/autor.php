<?php 
$seculos = array('I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV', 'XVI', 'XVII', 'XVIII', 'XIX', 'XX', 'XXI');
$seculos = array_reverse($seculos);
?>
<form class="inline">
	<fieldset>
		<legend><?php echo __('Nome');?></legend>
		<p><label for="nome_completo"><?php echo __('Nome');?></label>
		<input type="text" id="nome_completo" name="nome_completo" style="width: 500px" /></p>
		<p><label for="pseudonimo"><?php echo __('Pseudônimo');?></label>
		<input type="text" id="pseudonimo" name="pseudonimo" style="width: 220px" /></p>
	</fieldset>
	<fieldset>
		<legend><?php echo __('Nascimento');?></legend>
		<p><label for="ano_nascimento"><?php echo __('Ano de Nascimento');?></label>
		<input type="text" id="ano_nascimento" name="ano_nascimento" style="width: 180px" /></p>
		<p><label for="seculo_nascimento"><?php echo __('Século de Nascimento');?></label>
		<select id="seculo_nascimento" name="seculo_nascimento" style="width: 180px">
			<option value=""><?php echo __('Selecione');?></option>		
		    <?php 
            foreach ($seculos as $seculo) {
            ?>
            <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
            <?php
            }
            ?>
		</select></p>
		<p><label for="local_nascimento"><?php echo __('Local de Nascimento');?></label>
		<input type="text" id="local_nascimento" name="local_nascimento" style="width: 340px" /></p>
		<p><label for="catarinense"><br /><input type="checkbox" id="catarinense" name="catarinense" /> Catarinense</label></p>
	</fieldset>
	<fieldset>
		<legend><?php echo __('Morte');?></legend>
		<p><label for="ano_morte"><?php echo __('Ano da Morte');?></label>
		<input type="text" id="ano_morte" name="ano_morte" style="width: 180px" /></p>
		<p><label for="seculo_morte"><?php echo __('Século da Morte');?></label>
		<select id="seculo_morte" name="seculo_morte" style="width: 180px">
			<option value=""><?php echo __('Selecione');?></option>		
            <?php 
            foreach ($seculos as $seculo) {
            ?>
            <option value="<?php echo $seculo; ?>"><?php echo $seculo; ?></option>
            <?php
            }
            ?>
		</select></p>
		<p><label for="local_morte"><?php echo __('Local da Morte');?></label>
		<input type="text" id="local_morte" name="local_morte" style="width: 340px" /></p>
	</fieldset>
	<p><input type="submit" value="Pesquisar" /></p>
    <div id="status"></div>
    <div class="clear"></div>
</form>