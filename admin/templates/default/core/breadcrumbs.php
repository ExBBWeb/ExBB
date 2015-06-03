<?php if (count($breadcrumbs) >= 1) : ?>
<ol class="breadcrumb">
<?php foreach ($breadcrumbs as $breadcrumb) : ?>
  <li<?php if ($breadcrumb['active']) echo ' class="active"'; ?>>
	<?php if ($breadcrumb['url']) : ?>
		<a href="<?php echo $breadcrumb['url']; ?>">
	<?php endif; ?>
	
	<?php echo $breadcrumb['title']; ?>
	
	<?php if ($breadcrumb['url']) : ?>
	</a>
	<?php endif; ?>

	</li>
 <?php endforeach; ?>
</ol>
<?php endif; ?>