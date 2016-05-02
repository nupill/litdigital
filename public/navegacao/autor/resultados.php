<div id="breadcrumbs">
	<a href="<?php echo HOME_URI; ?>">Início</a> &rarr;
	<a href="<?php echo NAVEGACAO_AUTOR_URI; ?>">Navegação</a> &rarr;
	Autor
</div>
<div id="content">
<?php
$alfabeto = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X','Y','Z');
$target = NAVEGACAO_AUTOR_URI;

echo '<div align="center" class="naveg_div">';
for ($i=0; $i<sizeof($alfabeto); $i++) {
	if (isset($_GET['letra']) && $_GET['letra'] == $alfabeto[$i]) {
		echo "<a href=$target?letra={$alfabeto[$i]} class='active'>{$alfabeto[$i]}</a>";
	}
	else {
		echo "<a href=$target?letra={$alfabeto[$i]}>{$alfabeto[$i]}</a>";
	}
}
echo '</div>';
?>
    <div id="search_results">
    	<table id="results">
    		<thead>
    			<tr>
    				<th>Nome</th>
    				<th>Nascimento</th>
    			</tr>
    		</thead>
    	</table>
    </div>
    <script type="text/javascript">
    $(function() {

        $('#results').loadTable({
            sAjaxSource: "?action=getTableData&<?php echo make_request_url($_GET); ?>",
            aoColumns: [
                { "sWidth": "450px", "sName": "nome_usual" },
                { "sName": "loc_nasc" }
            ],
            allowDelete: false,
            allowCreate: false,
            allowUpdate: false,
            aaSorting: [[0,'asc']],
            sPaginationType: "two_button",
            iDisplayLength: 30,
            sDom: '<"top"ifr>t<"bottom"p><"clear">'
        });
        
    });
    </script>
</div>