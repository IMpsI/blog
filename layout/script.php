<?php
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($basePath === '' || $basePath === '.') {
	$basePath = '';
}
?>
<script src="<?= $basePath ?>/src/js/funcoes_globais.js"></script>
<script src="<?= $basePath ?>/src/js/index.js"></script>