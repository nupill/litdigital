<img style="height: 70px; width: 307px;margin-top: 8.5px;" src="<?php echo IMAGES_URI; ?>/logo_small.png">
<?php 
$login_visibility = '';
$logout_visibility = 'style="display: none"';
if (Auth::check()) {
    $login_visibility = 'style="display: none"';
    $logout_visibility = '';
}
?>

<form id="login" action="<?php echo CONTAS_URI; ?>?action=login" method="post" <?php echo $login_visibility; ?>>
	<p><input type="text" id="usuario" name="usuario" title="Usuário" />
	<input type="password" id="senha_fake" name="senha_fake" title="Senha" />
	<input type="hidden" id="senha" name="senha" />
	<input type="submit" value="<?php echo __('Entrar') ?>" id="login_entrar" disabled="disabled" class="disabled" /></p>
	<p class="links"><a href="<?php echo HOME_URI; ?>contas/redefinir/"><?php echo __('Esqueci minha senha') ?></a> | <a href="<?php echo HOME_URI; ?>contas/cadastrar/" title="<?php echo __('Cadastre-se apenas para usar os serviços de personalização e anotações') ?>"><?php echo __('Criar uma conta') ?></a></p>
	<div id="status_login"></div>
</form>
<form id="logout" action="<?php echo CONTAS_URI; ?>?action=logout" method="post" <?php echo $logout_visibility; ?>>
	<p>Olá <span id="nome_usuario"><?php if (isset($_SESSION['primeiro_nome'])) echo $_SESSION['primeiro_nome']; ?></span>.</p>
	<a href="<?php echo CONTAS_URI; ?>?action=logout" class="logout"><?php echo __('Logout') ?></a> |
	<a href="<?php echo HOME_URI; ?>contas/editar/" class="settings"><?php echo __('Meu Perfil') ?></a>
	<span id="link_admin"> | <a href="<?php echo ADMIN_URI; ?>" class="home"><?php echo __('Área administrativa') ?></a></span>
	<div id="status_login"></div>
</form>

<dl style="position: absolute; top: 0; right: 0; border: 0; background: url('../_images/background_bar.png') repeat-x scroll left top rgba(0, 0, 0, 0);" class="dropdown">
    <dt><a class="sublang" href="javascript:void(0);"><span><?php echo __('Idioma') ?></span></a></dt>
    <dd>
        <ul>
            <li><a class="sublang" href="javascript:refpt();">Português<span class="value">BR</span></a></li>
            <li><a class="sublang" href="javascript:reffr();">Français<span class="value">FR</span></a></li>
            <li><a class="sublang" href="javascript:refen();">English<span class="value">US</span></a></li>
             <li><a class="sublang" href="javascript:refes();">Español<span class="value">ES</span></a></li>
        </ul>
    </dd>
</dl>
<script type="text/javascript">
    $('#logo').click(function() {
    	window.location = '<?php echo HOME_URI; ?>';
    });
    $(".dropdown dt a").click(function() {
        $(".dropdown dd ul").toggle();
    });

    $(document).bind('click', function(e) {
        var $clicked = $(e.target);
        if (! $clicked.parents().hasClass("dropdown"))
            $(".dropdown dd ul").hide();
    });
    var phplocation = '<?php echo HOME_URI.$_SERVER["REQUEST_URI"];?>';
     ;

     function stripos(f_haystack, f_needle, f_offset) {
    	  //  discuss at: http://phpjs.org/functions/stripos/
    	  // original by: Martijn Wieringa
    	  //  revised by: Onno Marsman
    	  //   example 1: stripos('ABC', 'a');
    	  //   returns 1: 0

    	  var haystack = (f_haystack + '')
    	    .toLowerCase();
    	  var needle = (f_needle + '')
    	    .toLowerCase();
    	  var index = 0;

    	  if ((index = haystack.indexOf(needle, f_offset)) !== -1) {
    	    return index;
    	  }
    	  return -1;
    	}
     
     function refpt() {
    	 var parser = document.createElement('a');
    	 parser.href = location.href;
    	 var hreforiginal = parser.host + parser.port+parser.pathname;
    	 var search = parser.search;
    	 
    	 
         var language_set = stripos(search, 'locale', 0);
         if(language_set!=-1){
             var equal_sign = stripos(search, '=', language_set) +1 ;
             var new_search = search.substr(0, equal_sign);
             new_search = new_search+'pt_BR';

             var other_info = stripos(search, '&', equal_sign) ;
             if (other_info!=-1)
            	 new_search = new_search+search.substr(other_info);
             search = new_search;
         }else{
             var existePar = stripos(search, '?', 0);
             if (existePar == -1)
             	search = search +'?locale=pt_BR';
             else
            	 search = search+'&locale=pt_BR';
         }
         location.href = search+parser.hash;
     }

     function reffr() {
    	 var parser = document.createElement('a');
    	 parser.href = location.href;
    	 var hreforiginal = parser.host + parser.port+parser.pathname;
    	 var search = parser.search;
    	 
    	 
         var language_set = stripos(search, 'locale', 0);
         if(language_set!=-1){
             var equal_sign = stripos(search, '=', language_set) +1 ;
             var new_search = search.substr(0, equal_sign);
             new_search = new_search+'fr';

             var other_info = stripos(search, '&', equal_sign) ;
             if (other_info!=-1)
            	 new_search = new_search+search.substr(other_info);
             search = new_search;
         }else{
             var existePar = stripos(search, '?', 0);
             if (existePar == -1)
             	search = search +'?locale=fr';
             else
            	 search = search+'&locale=fr';
         }
         location.href = search+parser.hash;
     }
      
     function refen() {
    	 var parser = document.createElement('a');
    	 parser.href = location.href;
    	 var hreforiginal = parser.host + parser.port+parser.pathname;
    	 var search = parser.search;
    	 
    	 
         var language_set = stripos(search, 'locale', 0);
         if(language_set!=-1){
             var equal_sign = stripos(search, '=', language_set) +1 ;
             var new_search = search.substr(0, equal_sign);
             new_search = new_search+'en';

             var other_info = stripos(search, '&', equal_sign) ;
             if (other_info!=-1)
            	 new_search = new_search+search.substr(other_info);
             search = new_search;
         }else{
             var existePar = stripos(search, '?', 0);
             if (existePar == -1)
             	search = search +'?locale=en';
             else
            	 search = search+'&locale=en';
         }
         location.href = search+parser.hash;
     }

     function refes() {
    	 var parser = document.createElement('a');
    	 parser.href = location.href;
    	 var hreforiginal = parser.host + parser.port+parser.pathname;
    	 var search = parser.search;
    	 
    	 
         var language_set = stripos(search, 'locale', 0);
         if(language_set!=-1){
             var equal_sign = stripos(search, '=', language_set) +1 ;
             var new_search = search.substr(0, equal_sign);
             new_search = new_search+'es';

             var other_info = stripos(search, '&', equal_sign) ;
             if (other_info!=-1)
            	 new_search = new_search+search.substr(other_info);
             search = new_search;
         }else{
             var existePar = stripos(search, '?', 0);
             if (existePar == -1)
             	search = search +'?locale=es';
             else
            	 search = search+'&locale=es';
         }
         location.href = search+parser.hash;
     }


function authenticated() {
	$('#login').fadeOut('slow', function() {
		$('#logout').fadeIn();
	});
	window.location.reload();
	// Reload this pages because of comments
	if (window.location.href.indexOf('<?php echo AUTORES_URI; ?>') !== -1 ||
		window.location.href.indexOf('<?php echo DOCUMENTOS_URI; ?>') !== -1) {
		window.location.reload();
	}
	
}

var label = '';

function enableLoading(button) {
	label = button.val();
	button.addClass('loading_dark');
	button.css('width', button.outerWidth());
	button.val(' ');
	button.blur();
	button.attr('disabled', true);
}

function disableLoading(button) {
	button.removeClass('loading_dark');
	button.val(label);
	button.attr('disabled', false);
}

$(function() {
	
    /* Form submission (AJAX + JSON)
    ********************************************************************************************/

    //Define the options (functions) to handle the submit and response
    var options = {
        beforeSubmit: function() {
    		$('#status_login').hide().removeClass('error_box'); //Remove error messages (if exists)
    		//$('#status_login').html('<span class="loading_dark"></span>'); //AJAX loading gif
    		enableLoading($('#login_entrar'));
    	},
    	beforeSerialize: function() {
    		$('#senha').val($.md5($('#senha_fake').val()));
    		$('#senha_fake').attr('disabled', true);
    	},
        success: function(response, status) {
    		disableLoading($('#login_entrar'));
            //If no errors ocurred, print the success message
    		if (response && response.error == null) {
    			$('#status_login').addClass('success_box');
    			$('#status_login').hide().html('<span class="success"><?php echo __("Olá") ?> ' + response.name + '!</span>').fadeIn(300);
    			var first_name = response.name.split(' ');
    			first_name = first_name[0];
    			$('#nome_usuario').text(first_name);
    			setTimeout("authenticated()", 1000);
    			if (response.papel == <?php echo PAPEL_LEITOR_ID; ?>) {
    				$('#link_admin').hide();
    			}
    			else {
    				$('#link_admin').show();
    			}
    		}
    		else {
    			$('#status_login').addClass('error_box');
    			if (!response) {
    				response = {};
    				response.error = '<?php echo __("Ocorreu um erro inesperado") ?>';
    			}
    			$('#status_login').hide().html('<span class="error">'+response.error+'</span>').fadeIn(300);
    			$('#senha_fake').attr('disabled', false);
    		}
    	},
    	error: function(XMLHttpRequest, textStatus, errorThrown) {
    		disableLoading($('#login_entrar'));
        	//Print the error message if the request fails (possible reasons: "timeout", "error", "notmodified" and "parsererror")
    		$('#status_login').html('<span class="error">'+textStatus+':<br />'+XMLHttpRequest.responseText+'</span>');
    		$('#senha_fake').attr('disabled', false);
    	},
        dataType: 'json'
    };
    
    $('#login').ajaxForm(options); //Bind the form to the AJAX Form plugin

	// Tooltip
    $('a[title]').qtip({
        position: {
            my: 'right top', // Use the corner...
            at: 'left bottom', // ...and opposite corner
            adjust: {
                y: 5
             }
        },
        style: {
            classes: 'ui-tooltip-light ui-tooltip-rounded'
        }
    });

	<?php 
	if (isset($_SESSION['papel']) && $_SESSION['papel'] == PAPEL_LEITOR_ID) {
	?>
	$('#link_admin').hide();
	<?php 
	}
	?>
});
</script>
