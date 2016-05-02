<div id="left_side">
    <ul>
        <li class="selected"><?php echo __('Erro') ?></li>
    </ul>
  </div>
    <div id="right_side">
    <div id="right_side_top">
    
    </div>
    <div id="right_side_content">
        <div style="width: 350px; margin: 50px auto; padding: 0 15px" class="dashed_box">
            <h4 style='text-decoration: blink; color: #FF0000; text-align: center; margin: 5px; padding: 5px'>
                <?php echo __('Erro') ?>!
            </h4>
            <p><?php echo __('Ocorreu um erro ao executar uma operação no servidor, por favor tente novamente mais tarde.') ?></p>
            <p><?php echo __('Caso este erro persista, contate o administrador.') ?></p>
        </div>
        <p align="center"><input type="button" onclick="history.go(-1)" value="Voltar"></p>
    </div>
    <div id="loading" style="display: none">
    </div>
    <div id="right_side_bottom">
        
    </div>
</div>