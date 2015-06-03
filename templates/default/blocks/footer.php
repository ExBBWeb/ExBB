
			<div id="footer">
				<p><?php echo $app->config->getOption('site_title'); ?> &copy; <?php echo date('Y'); ?></p>
				<p class="runtime-info">Время выполнения: <?php echo round(microtime(true)-MT, 3); ?>сек. Макс. ОЗУ: <?php echo round(memory_get_peak_usage()/1024, 2) ?>кб.</p>
			</div>
		</div>
	</body>
</html>