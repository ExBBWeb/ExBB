<?php if ($pages >= 1) : ?>
<div class="pagination">
		<?php
		if ($current > 1)
		viewPage(1, $url, false, '<i class="fa fa-angle-double-left"></i>');
		
		if ($current-1 >= 1) {
			viewPage($current-1, $url, false, '<i class="fa fa-angle-left"></i>');
		}
		?>
		<div class="numbers">
		<?php
		if ($current == 1) {
			viewPage($current, $url, $current);
			if ($current+1 <= $pages) viewPage($current+1, $url, $current);
			if ($current+2 <= $pages) viewPage($current+2, $url, $current);
			if ($current+3 <= $pages) viewPage($current+3, $url, $current);
			if ($current+4 <= $pages) viewPage($current+4, $url, $current);
		}
		elseif ($current > 1 && $current < $pages) {
			if ($current-2 >= 1) viewPage($current-2, $url, $current);
			if ($current-1 >= 1) viewPage($current-1, $url, $current);
			viewPage($current, $url, $current);
			if ($current+1 <= $pages) viewPage($current+1, $url, $current);
			if ($current+2 <= $pages) viewPage($current+2, $url, $current);
		}
		elseif ($current == $pages) {
			if ($current-4 >= 1) viewPage($current-4, $url, $current);
			if ($current-3 >= 1) viewPage($current-3, $url, $current);
			if ($current-2 >= 1) viewPage($current-2, $url, $current);
			if ($current-1 >= 1) viewPage($current-1, $url, $current);
			viewPage($current, $url, $current);
		}
		?>
		</div> 
		<?php
		if ($current+1 <= $pages) {
			viewPage($current+1, $url, false, '<i class="fa fa-angle-right"></i>');
		}
		
		if ($current < $pages) viewPage($pages, $url, false, '<i class="fa fa-angle-double-right"></i>');
		?>
		
	
</div>
<?php endif; ?>

<?php function viewPage($i, $url, $current, $label=false) { ?>

	<?php
	if ($label) {
		echo '<a href="'.str_replace(urlencode("[page]"), $i, $url).'" class="button"><span>'.$label.'</span></a> ';
		return true;
	}
	?>

	<?php if ($current == $i) :  ?>
	<span class="current"><?php echo ($label) ? $label : $i; ?></span> 
	<span>|</span> 
	<?php else : ?>
	<a href="<?php echo str_replace(urlencode("[page]"), $i, $url); ?>"><?php echo ($label) ? $label : $i; ?></a> 
	<span>|</span> 
	<?php endif; ?>
<?php } ?>
