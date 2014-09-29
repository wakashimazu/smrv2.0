<!DOCTYPE html>
<html>
	<head><?php
		$this->assign('FontSize', $FontSize-20);
		$this->includeTemplate('includes/Head.inc'); ?>	
	</head>
	<body>
		<table class="m" align="center">
			<tr>
				<td class="l0">
					<div class="l1">
						<?php $this->includeTemplate('includes/LeftPanel.inc'); ?>
					</div>
				</td>
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
				<td class="r0">
					<div id="right_panel">
						<?php $this->includeTemplate('includes/RightPanel.inc'); ?>
					</div>
				</td>
			</tr>
		</table>
		<?php $this->includeTemplate('includes/EndingJavascript.inc'); ?>
	</body>
</html>