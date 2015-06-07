<?php if (count($breadcrumbs) >= 1) : ?>
<div class="breadcrumbs">
	<ul class="breadcrumbs-list">
		<li><i class="fa fa-home"></i></li>
		<?php foreach ($breadcrumbs as $breadcrumb) : ?>
		<li><a href="<?php if ($breadcrumb['url']) echo $breadcrumb['url']; else echo '#'; ?>"<?php if ($breadcrumb['current']) echo ' class="current"'; ?>><?php echo $breadcrumb['title']; ?></a><?php if (!$breadcrumb['current']) : ?><i class="fa fa-arrow-circle-right"></i><?php endif; ?></li>
		<?php endforeach; ?>
	 </ul>
</div>
<?php endif; ?>