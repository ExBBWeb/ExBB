<script type="text/javascript" >
$(document).ready(function() {
	$("#<?php echo $id; ?>").markItUp(mySettings);
});
</script>
<textarea<?php echo $attr_name.$attr_id.$attr_class,$attr_placeholder; ?>><?php echo $content; ?></textarea>