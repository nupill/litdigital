<?php

echo "Loading, please wait... \n";

session_start();

require(dirname( '..\..\application\controllers\ControleInstall.php\ControleInstall.php'));

$url = @$_SESSION ['url'];

// should be the $url instead of the url in the code below

$dump_url = "CREATE ALGORITHM=UNDEFINED DEFINER=`litdigital`@`localhost` SQL SECURITY DEFINER VIEW `oai_records` AS select `m`.`id` AS `serial`,concat('http://www.literaturabrasileira.ufsc.br/documentos/?action=download&id=',`m`.`id`) AS `url`,`m`.`id` AS `oai_idenfifier`,'literaturadigital' AS `oai_set`,concat_ws(' ',`d`.`titulo_normalizado`,concat('- ',`pt_normalize`(`m`.`titulo`))) AS `dc_title`,`d`.`autores_nome_completo_normalizado` AS `dc_creator`,`pt_normalize`(`d`.`descricao`) AS `dc_description`,`do`.`data_inclusao` AS `dc_date`,`do`.`data_inclusao` AS `datestamp`,`d`.`nome_tipodocumento` AS `dc_type`,`m`.`mime` AS `dc_format`,`m`.`id` AS `identifier`,`m`.`fonte` AS `dc_source`,`d`.`nome_idioma` AS `dc_language`,`do`.`abrangencia` AS `dc_coverage`,`do`.`direitos` AS `dc_rights` from (((`Midia` `m` join `DocumentoConsulta` `d`) join `Documento` `do`) join `Idioma` `i`) where ((`m`.`Documento_id` = `d`.`id`) and (`d`.`id` = `do`.`id`)) ;";
$dump_admin_creation1 = "insert  into `papel`(`id`,`nome`) values (1,'Administrador'),(2,'Cadastrador'),(3,'Leitor');";
$dump_admin_creation2 = "insert  into `usuario`(`id`,`nome`,`login`,`senha`,`email`,`url`,`profissao`,`anotacao`,`personalizacao`,`confirmado`,`codigo_confirmacao`,`codigo_redefinicao`,`data_codigo_redefinicao`,`data_inclusao`,`Papel_id`,`AdTipoCores_id`,`AdTipoOrdenacao_id`) values (1,'admin','admin','21232f297a57a5a743894a0e4a801fc3','',NULL,'',0,0,2,NULL,NULL,NULL,'0000-00-00 00:00:00',1,NULL,NULL);";
if (isset($url)) {
	$controller = ControleInstall::getInstance();
	try {
		$controller->set_db("EstruturaBanco.sql");
		if (!$controller->query($dump_url)) {
			throw new Exception("Invalid Url passed by param");
		}
		$controller->query($dump_admin_creation1);
		$controller->query($dump_admin_creation2);
		$controller->set_db("Config.sql");
		echo "Database loaded successfully";	
	} catch (Exception $e) {
		echo $e->getMessage();
	}
}
?>