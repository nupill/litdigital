<?php
require_once(dirname(__FILE__) . '/../../../application/controllers/ControleDocumentos.php');
		$indexPath = '../zf/indexes';
		$diretorio = "../../public/_documents/";
		$controllerdoc = ControleDocumentos::getInstance();
		$controllerdoc->index_alldocuments();
?>
