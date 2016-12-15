<div class="box">
	<div class="tbl-ctrls">
<?php if (isset($form_url)):?>
	<?=form_open($form_url)?>
<?php elseif (isset($footer) AND $footer['type'] == 'bulk_action_form'):?>
	<form><!-- currently EE's bulk action setup requires a form wrapper no matter what -->
<?php endif;?>
	<?php if ( ! empty($form_right_links)):?>
		<fieldset class="tbl-search right">
		<?php foreach ($form_right_links as $link_data):?>
		<a class="btn tn action" href="<?=$link_data['link']?>"><?=$link_data['title']?></a>
		<?php endforeach;?>
		</fieldset>
	<?php endif;?>
	<?php if (isset($cp_page_title)):?>
		<h1><?=$cp_page_title?></h1>
	<?php elseif (isset($wrapper_header)):?>
		<h1><?=$wrapper_header?></h1>
	<?php endif;?>
		<?=ee('CP/Alert')->getAllInlines()?>
		<?=$child_view?>
	<?php if (isset($pagination)):?>
		<div class="ss_clearfix"><?=$pagination?></div>
	<?php endif;?>
<?php if (isset($footer)):?>
	<?php if ($footer['type'] == 'form'):?>
		<fieldset class="form-ctrls">
		<?php if (isset($footer['submit_lang'])):?>
			<input class="btn submit" type="submit" value="<?=$footer['submit_lang']?>" />
		<?php endif;?>
		</fieldset>
	<?php elseif ($footer['type'] == 'bulk_action_form'): ?>
		<fieldset class="tbl-bulk-act hidden">
			<select name="bulk_action">
		<?php if (isset($footer['bulk_actions'])):?>
			<?php foreach($footer['bulk_actions'] as $value => $label):?>
				<option value="<?=$value?>" data-confirm-trigger="selected" rel="modal-confirm-<?=$value?>">
					<?=$label?>
				</option>
			<?php endforeach;?>
		<?php else: ?>
				<option value="remove" data-confirm-trigger="selected" rel="modal-confirm-remove">
					<?=lang('remove')?>
				</option>
		<?php endif;?>
			</select>
			<button class="btn submit" data-conditional-modal="confirm-trigger">
				<?=$footer['submit_lang']?>
			</button>
		</fieldset>
	<?php else:?>
	<?php endif;?>
<?php endif;?>
<?php if (isset($form_url) || (isset($footer) AND $footer['type'] == 'bulk_action_form')):?>
		</form>
<?php endif;?>
	</div>
</div>