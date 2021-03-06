<!DOCTYPE html>
<html>
	<head><?php
		$this->assign('FontSize', $FontSize-20);
		$this->includeTemplate('includes/Head.inc'); ?>	
	</head>
	<body>
		<div id="menu_container">
			<div id="menu">
					<?php $this->includeTemplate('includes/LeftPanel.inc'); ?>
			</div>
		</div>
		<table class="m" style="width: 80%;" align="center">
			<tr>
				<td class="m0">
					<div id="middle_panel"><?php
						if(isset($PageTopic)) {
							?><h1><?php echo $PageTopic; ?></h1><br><?php
						}
						if(isset($MenuItems)||isset($MenuBar)) { ?>
							<div class="bar1">
								<div><?php
									if(isset($MenuItems)) {
										$this->includeTemplate('includes/menu.inc');
									}
									else if(isset($MenuBar)) {
										echo $MenuBar;
									} ?>
								</div>
							</div><br><?php
						}
						else if(isset($SubMenuBar)) {
							echo $SubMenuBar;
						}
						$this->includeTemplate($TemplateBody); ?>
					</div>
				</td>
			</tr>
		</table>
		<?php $this->includeTemplate('includes/EndingJavascript.inc'); ?>
	</body>
	<img src="spinner.gif" id="spinner" style="width:30px; height:30px;position: absolute;top: 50%;left: 50%;"/>

	<script type="text/javascript">
		window.onload=hideSpinner;
 
		function hideSpinner() {
			document.getElementById('spinner').style.display = "none";
		}
	</script>
</html>