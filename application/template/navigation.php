<ul id="navigation_menu">
	<li <?php if ($this->get_active_nav_item() == HOME_ID) { echo 'class="active"'; } ?>><a href="<?php echo HOME_URI; ?>"><?php echo __('Início') ?></a></li>
	<li <?php if ($this->get_active_nav_item() == BUSCA_ID) { echo 'class="active"'; } ?>><a href="<?php echo BUSCA_URI; ?>"><?php echo __('Busca') ?></a></li>
	<li <?php if ($this->get_active_nav_item() == NAVEGACAO_ID) { echo 'class="active"'; } ?>><a href="<?php echo NAVEGACAO_URI; ?>"><?php echo __('Navegação') ?></a></li>
<!--<li <?php if ($this->get_active_nav_item() == ADAPTABILIDADE_ID) { echo 'class="active"'; } ?>><a href="<?php echo ADAPTABILIDADE_URI; ?>">Adaptabilidade</a></li>-->
	<li <?php if ($this->get_active_nav_item() == SOBRE_ID) { echo 'class="active"'; } ?>><a href="<?php echo SOBRE_URI; ?>"><?php echo __('Sobre') ?></a></li>
</ul>