<!-- BEGIN BIBLE CONTAINNER -->
<div class='bovp_container'>

	<!-- BEGIN HEADER CONTAINNER -->
	<div class="bovp_header bovp_clear">

		<!-- SEARCH FORM CONTAINER -->
		<div class="bovp_search">
			<?php $bovp->showFormSearch(); ?>
		</div>
		<!-- SHOW SHARE BUTTONS -->
		<?php $bovp->showShareButtons(bovpSharer(bovpWriteUrl())); ?>
		<!-- SHOW INCREASE/DECREASE FONT BUTTONS -->
		<?php $bovp->showFontSize(array('s1'=>'16','s2'=>'18','s3'=>'20')); ?>

			
	</div>


	<!-- TITLE -->
	<?php $bovp->showTitle(strtolower($bovp_title), true);  ?>

	<!-- BIBLE TEXT CONTAINNER -->
	<?php $bovp->showResults($bovp_content, true); ?>

	<!-- PAGINATION CONTAINNER -->
	<nav class="bovp_pagination bovp_clear">

		<!-- SHOW PAGINATION -->
		<?php $bovp->showPagination(); ?>
		
	</nav>

	<!-- FOOTER CONTAINNER -->
	<div class="bovp_footer bovp_clear">

		<div class="bovp_version bovp_clear">

			<?php $bovp->showVersion(); ?>

		</div>

		<div class="bovp_logo bovp_clear">

			<?php $bovp->showLogo(); ?>

		</div>

	</div>

	

<!-- END BIBLE CONTAINNER -->
</div>